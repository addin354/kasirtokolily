<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\Product;
use App\Models\User;
use App\Services\WhatsAppGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CheckStock extends Command
{
    protected $signature = 'check:stock {--min= : Batas stok (override config pos.stock_notification.min_stok)}';

    protected $description = 'Periksa produk berstok di bawah minimum, kirim email & simulasi WhatsApp.';

    public function handle(): int
    {
        $min = (int) ($this->option('min') ?: config('pos.stock_notification.min_stok', 10));
        $min = max(0, $min);

        $produk = Product::query()
            ->where('is_active', true)
            ->where('stok', '<', $min)
            ->orderBy('stok')
            ->orderBy('nama')
            ->get();

        if ($produk->isEmpty()) {
            $this->info('Tidak ada produk berstok di bawah '.$min.'; tidak mengirim notifikasi.');

            return self::SUCCESS;
        }

        $this->info('Ditemukan '.$produk->count().' produk (stok < '.$min.').');

        if (config('pos.stock_notification.email_enabled', true)) {
            $this->kirimEmail($produk, $min);
        } else {
            $this->comment('Email notifikasi dinonaktifkan (STOK_ALERT_EMAIL_ENABLED).');
        }

        if (config('pos.stock_notification.wa_enabled', true)) {
            $this->kirimWhatsAppOwner($produk, $min, app(WhatsAppGateway::class));
        } else {
            $this->comment('Notifikasi WhatsApp dinonaktifkan (STOK_ALERT_WA_ENABLED).');
        }

        return self::SUCCESS;
    }

    private function kirimEmail($produk, int $min): void
    {
        $mailable = new LowStockAlert($produk, $min);
        $to = config('pos.stock_notification.email_to', []);

        if (count($to) > 0) {
            $recipients = $to;
        } else {
            $recipients = User::query()
                ->where('role', User::ROLE_OWNER)
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if ($recipients === []) {
            $this->warn('Tidak ada penerima email (set STOK_ALERT_EMAIL_TO atau buat user owner).');

            return;
        }

        foreach ($recipients as $addr) {
            Mail::to($addr)->send($mailable);
            $this->line('Email terkirim (log/mailer): '.$addr);
        }
    }

    private function kirimWhatsAppOwner($produk, int $min, WhatsAppGateway $wa): void
    {
        $ownerNumbers = User::query()
            ->where('role', User::ROLE_OWNER)
            ->pluck('no_hp')
            ->filter()
            ->map(fn ($n) => $this->normalizePhone((string) $n))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $manualOwnerNumbers = array_map(
            fn ($n) => $this->normalizePhone((string) $n),
            (array) config('pos.stock_notification.wa_owner_to', [])
        );
        $fallback = (string) config('pos.stock_notification.wa_to', '62812xxxxxxxxxx');
        $recipients = $ownerNumbers !== []
            ? $ownerNumbers
            : array_values(array_unique(array_filter($manualOwnerNumbers)));

        if ($recipients === []) {
            $fallbackNormalized = $this->normalizePhone($fallback);
            if ($fallbackNormalized !== null) {
                $recipients = [$fallbackNormalized];
            }
        }

        if ($recipients === []) {
            $this->warn('Tidak ada nomor WA owner valid. Isi no_hp owner atau STOK_ALERT_WA_OWNER_TO.');

            return;
        }

        $teks = $this->buildWhatsAppMessage($produk, $min);

        foreach ($recipients as $to) {
            try {
                $wa->send((string) $to, $teks);
                $this->line('WA terkirim ke '.$to.'.');
            } catch (Throwable $e) {
                $this->error('Gagal kirim WA ke '.$to.': '.$e->getMessage());
            }
        }
    }

    private function normalizePhone(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }
        if (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }
        if (strlen($digits) < 10 || strlen($digits) > 16) {
            return null;
        }

        return $digits;
    }

    private function buildWhatsAppMessage($produk, int $min): string
    {
        $template = (string) config('pos.stock_notification.wa_template', '');
        if ($template === '') {
            $template = "*{app_name}*\nPeringatan stok menipis\nWaktu: {datetime}\nBatas minimum: {min_stok}\nJumlah produk: {count_produk}\n\n{produk_lines}";
        }

        // Dukung format dari env yang memakai encoded newline (%0A).
        $template = str_replace('%0A', "\n", $template);

        $produkLines = $produk->take(20)->map(function (Product $p) {
            return '- '.($p->kode ? '['.$p->kode.'] ' : '').$p->nama.' (stok: '.(int) $p->stok.')';
        })->implode("\n");

        if ($produk->count() > 20) {
            $produkLines .= "\n- ... +".($produk->count() - 20).' produk lainnya';
        }

        $dashboardUrl = rtrim((string) config('app.url'), '/').'/owner/stok';
        $replacements = [
            '{app_name}' => (string) config('app.name'),
            '{datetime}' => now()->format('d-m-Y H:i'),
            '{min_stok}' => (string) $min,
            '{count_produk}' => (string) $produk->count(),
            '{produk_lines}' => $produkLines,
            '{dashboard_url}' => $dashboardUrl,
        ];

        return strtr($template, $replacements);
    }
}
