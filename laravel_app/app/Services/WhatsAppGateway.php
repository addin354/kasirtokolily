<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppGateway
{
    /**
     * Driver supported:
     * - simulated: write to storage/logs/whatsapp_simulation.log
     * - fonnte: send real WA via https://api.fonnte.com/send
     */
    public function send(string $to, string $message): void
    {
        $driver = (string) config('pos.stock_notification.wa_driver', 'simulated');

        if ($driver === 'fonnte') {
            $this->sendViaFonnte($to, $message);

            return;
        }

        $this->sendSimulated($to, $message);
    }

    private function sendViaFonnte(string $to, string $message): void
    {
        $token = (string) config('pos.stock_notification.wa_fonnte_token', '');
        $url = (string) config('pos.stock_notification.wa_fonnte_url', 'https://api.fonnte.com/send');

        if ($token === '') {
            throw new RuntimeException('Token Fonnte belum diisi (STOK_ALERT_WA_FONNTE_TOKEN).');
        }

        $response = Http::asForm()
            ->timeout(20)
            ->withHeaders(['Authorization' => $token])
            ->post($url, [
                'target' => $to,
                'message' => $message,
                'countryCode' => '62',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal kirim WA (HTTP '.$response->status().'): '.$response->body());
        }
    }

    private function sendSimulated(string $to, string $message): void
    {
        $logPath = storage_path('logs/whatsapp_simulation.log');
        $line = sprintf("[%s] to=%s\n%s\n---\n", now()->toDateTimeString(), $to, $message);
        $dir = dirname($logPath);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        File::append($logPath, $line);
    }
}

