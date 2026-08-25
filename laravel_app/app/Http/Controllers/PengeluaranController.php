<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Http\Requests\StorePengeluaranRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Pengeluaran::class);

        if (Pengeluaran::query()->count() < 10) {
            (new \Database\Seeders\PengeluaranSeeder())->run();
        }

        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');
        $tanggalDari = $request->query('tanggal_dari');
        $tanggalSampai = $request->query('tanggal_sampai');

        // Dashboard Kecil calculations (overall, unaffected by filters)
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $startOfYear = now()->startOfYear()->toDateString();
        $endOfYear = now()->endOfYear()->toDateString();

        $pengeluaranHariIni = (float) Pengeluaran::whereDate('tanggal', $today)->sum('nominal');
        $pengeluaranMingguIni = (float) Pengeluaran::whereDate('tanggal', '>=', $startOfWeek)->whereDate('tanggal', '<=', $endOfWeek)->sum('nominal');
        $pengeluaranBulanIni = (float) Pengeluaran::whereDate('tanggal', '>=', $startOfMonth)->whereDate('tanggal', '<=', $endOfMonth)->sum('nominal');
        $pengeluaranTahunIni = (float) Pengeluaran::whereDate('tanggal', '>=', $startOfYear)->whereDate('tanggal', '<=', $endOfYear)->sum('nominal');

        // Query listing with filters
        $query = Pengeluaran::query()->with('user');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_pengeluaran', 'LIKE', "%{$q}%")
                    ->orWhere('keterangan', 'LIKE', "%{$q}%");
            });
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($tanggalDari) {
            $query->whereDate('tanggal', '>=', $tanggalDari);
        }

        if ($tanggalSampai) {
            $query->whereDate('tanggal', '<=', $tanggalSampai);
        }

        $pengeluarans = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $categories = Pengeluaran::categories();

        return view('pengeluaran.index', compact(
            'pengeluarans',
            'categories',
            'pengeluaranHariIni',
            'pengeluaranMingguIni',
            'pengeluaranBulanIni',
            'pengeluaranTahunIni'
        ));
    }

    public function create()
    {
        $this->authorize('create', Pengeluaran::class);

        $categories = Pengeluaran::categories();

        // Prediksi nomor pengeluaran otomatis
        $today = now()->format('Ymd');
        $prefix = 'OUT-' . $today . '-';
        $latest = Pengeluaran::where('nomor_pengeluaran', 'LIKE', $prefix . '%')
            ->orderByDesc('nomor_pengeluaran')
            ->first();

        if ($latest) {
            $parts = explode('-', $latest->nomor_pengeluaran);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        $predictedNomor = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

        return view('pengeluaran.create', compact('categories', 'predictedNomor'));
    }

    public function store(StorePengeluaranRequest $request)
    {
        $this->authorize('create', Pengeluaran::class);

        $validated = $request->validated();

        try {
            $pengeluaran = DB::transaction(function () use ($validated) {
                $today = now()->format('Ymd');
                $prefix = 'OUT-' . $today . '-';
                $latest = Pengeluaran::where('nomor_pengeluaran', 'LIKE', $prefix . '%')
                    ->lockForUpdate()
                    ->orderByDesc('nomor_pengeluaran')
                    ->first();

                if ($latest) {
                    $parts = explode('-', $latest->nomor_pengeluaran);
                    $num = intval(end($parts)) + 1;
                } else {
                    $num = 1;
                }
                $nomor = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

                return Pengeluaran::create([
                    'nomor_pengeluaran' => $nomor,
                    'tanggal' => $validated['tanggal'],
                    'kategori' => $validated['kategori'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'nominal' => $validated['nominal'],
                    'metode_pembayaran' => $validated['metode_pembayaran'] ?? 'Cash',
                    'user_id' => auth()->id(),
                ]);
            });

            return redirect()
                ->route('pengeluaran.index')
                ->with('success', 'Pengeluaran ' . $pengeluaran->nomor_pengeluaran . ' berhasil dicatat.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mencatat pengeluaran: ' . $e->getMessage());
        }
    }

    public function show(Pengeluaran $pengeluaran)
    {
        $this->authorize('view', $pengeluaran);

        $pengeluaran->load('user');

        return view('pengeluaran.show', compact('pengeluaran'));
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        $this->authorize('update', $pengeluaran);

        $categories = Pengeluaran::categories();

        return view('pengeluaran.edit', compact('pengeluaran', 'categories'));
    }

    public function update(StorePengeluaranRequest $request, Pengeluaran $pengeluaran)
    {
        $this->authorize('update', $pengeluaran);

        $validated = $request->validated();

        try {
            $pengeluaran->update([
                'tanggal' => $validated['tanggal'],
                'kategori' => $validated['kategori'],
                'keterangan' => $validated['keterangan'] ?? null,
                'nominal' => $validated['nominal'],
                'metode_pembayaran' => $validated['metode_pembayaran'] ?? 'Cash',
            ]);

            return redirect()
                ->route('pengeluaran.index')
                ->with('success', 'Pengeluaran ' . $pengeluaran->nomor_pengeluaran . ' berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pengeluaran: ' . $e->getMessage());
        }
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $this->authorize('delete', $pengeluaran);

        try {
            $pengeluaran->delete();

            return redirect()
                ->route('pengeluaran.index')
                ->with('success', 'Pengeluaran berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()
                ->route('pengeluaran.index')
                ->with('error', 'Gagal menghapus pengeluaran: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('viewAny', Pengeluaran::class);

        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');
        $tanggalDari = $request->query('tanggal_dari');
        $tanggalSampai = $request->query('tanggal_sampai');

        $query = Pengeluaran::query()->with('user');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_pengeluaran', 'LIKE', "%{$q}%")
                    ->orWhere('keterangan', 'LIKE', "%{$q}%");
            });
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($tanggalDari) {
            $query->whereDate('tanggal', '>=', $tanggalDari);
        }

        if ($tanggalSampai) {
            $query->whereDate('tanggal', '<=', $tanggalSampai);
        }

        $pengeluarans = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $pdf = Pdf::loadView('pengeluaran.pdf', [
            'pengeluarans' => $pengeluarans,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-pengeluaran-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('viewAny', Pengeluaran::class);

        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');
        $tanggalDari = $request->query('tanggal_dari');
        $tanggalSampai = $request->query('tanggal_sampai');

        $query = Pengeluaran::query()->with('user');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_pengeluaran', 'LIKE', "%{$q}%")
                    ->orWhere('keterangan', 'LIKE', "%{$q}%");
            });
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($tanggalDari) {
            $query->whereDate('tanggal', '>=', $tanggalDari);
        }

        if ($tanggalSampai) {
            $query->whereDate('tanggal', '<=', $tanggalSampai);
        }

        $pengeluarans = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $rows = [];
        $rows[] = ['Nomor Pengeluaran', 'Tanggal', 'Kategori', 'Keterangan', 'Nominal', 'User'];

        foreach ($pengeluarans as $p) {
            $rows[] = [
                $p->nomor_pengeluaran,
                $p->tanggal->format('d/m/Y'),
                $p->kategori,
                $p->keterangan ?? '',
                (float) $p->nominal,
                $p->user?->name ?? '—',
            ];
        }

        $filename = 'laporan-pengeluaran-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    fputcsv($handle, $row, ',');
                    continue;
                }
                $row[4] = 'Rp ' . number_format($row[4], 0, ',', '.');
                fputcsv($handle, $row, ',');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
