<?php

namespace App\Http\Controllers;

use App\Mail\LaporanKasirMasuk;
use App\Models\Report;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ReportController extends Controller
{
    /**
     * Daftar laporan: kasir hanya milik sendiri; owner & admin semua.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (! in_array($user->role, [User::ROLE_KASIR, User::ROLE_OWNER, User::ROLE_ADMIN], true)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        $query = Report::query()->with('user')->latest();

        if ($user->isKasir()) {
            $query->where('user_id', $user->id);
        }

        $reports = $query->paginate(15)->withQueryString();

        return view('reports.index', compact('reports'));
    }

    public function create(): View
    {
        $this->ensureKasir();

        return view('reports.create', [
            'typeList' => Report::typeList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureKasir();

        $request->validate([
            'type' => 'required|in:harian,transaksi',
            'report_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data = match ($request->string('type')->toString()) {
            'harian' => $this->generateDailyReport($request->input('report_date')),
            'transaksi' => $this->generateTransactionReport($request->input('report_date')),
            default => [],
        };

        $report = Report::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'report_date' => $request->report_date,
            'data' => $data,
            'notes' => $request->notes,
            'status' => Report::STATUS_TERKIRIM,
        ]);

        $this->sendEmailToOwner($report);

        return redirect()
            ->route('reports.index')
            ->with('success', 'Laporan tersimpan dan terkirim ke owner.');
    }

    private function sendEmailToOwner(Report $report): void
    {
        $report->loadMissing('user');

        $recipients = User::query()
            ->where('role', User::ROLE_OWNER)
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $configuredTo = (array) config('pos.stock_notification.email_to', []);
        $envEmail = env('STOK_ALERT_EMAIL_TO', 'addinhusnannadhari354@gmail.com');
        if (! empty($envEmail)) {
            $configuredTo[] = $envEmail;
        }

        $recipients = array_values(array_unique(array_filter(array_merge($recipients, $configuredTo))));

        // Filter email palsu/dummy (contoh: pos.test, example.com)
        $recipients = array_values(array_filter($recipients, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL)
                && ! str_ends_with(strtolower($email), '.test')
                && ! str_contains(strtolower($email), 'example.com')
                && ! str_contains(strtolower($email), 'example.org')
                && ! str_contains(strtolower($email), 'owner@gmail.com')
                && ! str_contains(strtolower($email), 'owner@pos.test');
        }));

        if (empty($recipients)) {
            return;
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new LaporanKasirMasuk($report));
            } catch (Throwable $e) {
                Log::error("Gagal mengirim email laporan kasir ke {$recipient}: ".$e->getMessage());
            }
        }
    }

    public function show(Request $request, Report $report): View
    {
        $this->ensureCanView($request->user(), $report);

        if ($this->shouldMarkAsRead($request->user(), $report)) {
            $report->update([
                'status' => Report::STATUS_DIBACA,
                'read_at' => now(),
            ]);
            $report->refresh();
        }

        return view('reports.show', [
            'report' => $report,
            'typeList' => Report::typeList(),
        ]);
    }

    private function ensureKasir(): void
    {
        if (! Auth::user()?->isKasir()) {
            abort(403, 'Hanya kasir yang dapat membuat laporan ke owner.');
        }
    }

    private function ensureCanView(?User $user, Report $report): void
    {
        if (! $user) {
            abort(403);
        }

        if ($user->isKasir() && $report->user_id !== $user->id) {
            abort(403);
        }

        if (! $user->isKasir() && ! $user->isOwner() && ! $user->isAdmin()) {
            abort(403);
        }
    }

    private function shouldMarkAsRead(User $user, Report $report): bool
    {
        if (! $user->isOwner() && ! $user->isAdmin()) {
            return false;
        }

        return $report->status === Report::STATUS_TERKIRIM;
    }

    /**
     * Ringkatan penjualan harian: total, jumlah transaksi, qty per produk.
     */
    private function generateDailyReport(string $date): array
    {
        $transactions = Transaksi::query()
            ->with('detailTransaksis.product')
            ->whereDate('tanggal', $date)
            ->get();

        $totalRevenue = (float) $transactions->sum('total');
        $totalTransactions = $transactions->count();
        $productsSold = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->detailTransaksis as $detail) {
                if (! $detail->product) {
                    continue;
                }
                $name = $detail->product->nama;
                $productsSold[$name] = ($productsSold[$name] ?? 0) + (int) $detail->qty;
            }
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'products_sold' => $productsSold,
        ];
    }

    /**
     * Daftar transaksi pada tanggal (nilai disimpan = snapshot dari database).
     */
    private function generateTransactionReport(string $date): array
    {
        $transactions = Transaksi::query()
            ->with('detailTransaksis.product')
            ->whereDate('tanggal', $date)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        return $transactions->map(function (Transaksi $transaction) {
            return [
                'id' => $transaction->id,
                'total' => (float) $transaction->total,
                'bayar' => (float) $transaction->bayar,
                'kembalian' => (float) $transaction->kembalian,
                'tanggal' => $transaction->tanggal?->toIso8601String(),
                'items' => $transaction->detailTransaksis->map(function ($detail) {
                    return [
                        'produk' => $detail->product?->nama ?? '—',
                        'qty' => (int) $detail->qty,
                        'harga' => (float) $detail->harga,
                        'subtotal' => (float) $detail->subtotal,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }
}
