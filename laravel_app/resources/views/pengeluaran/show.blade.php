@extends('layouts.app')

@section('title', 'Detail Pengeluaran ' . $pengeluaran->nomor_pengeluaran . ' — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Detail Pengeluaran: {{ $pengeluaran->nomor_pengeluaran }}</h1>
        <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    <div class="card shadow-sm max-width-600">
        <div class="card-header fw-semibold">Informasi Pengeluaran</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <tr>
                    <td class="text-muted small fw-semibold ps-3" style="width: 180px;">Nomor Pengeluaran</td>
                    <td class="fw-semibold">{{ $pengeluaran->nomor_pengeluaran }}</td>
                </tr>
                <tr>
                    <td class="text-muted small fw-semibold ps-3">Tanggal</td>
                    <td>{{ $pengeluaran->tanggal->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="text-muted small fw-semibold ps-3">Kategori</td>
                    <td><span class="badge bg-light text-dark border">{{ $pengeluaran->kategori }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted small fw-semibold ps-3">Nominal</td>
                    <td class="fs-5 fw-bold text-danger">Rp {{ number_format($pengeluaran->nominal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted small fw-semibold ps-3">Metode Pembayaran</td>
                    <td class="fw-semibold">{{ $pengeluaran->metode_pembayaran ?? 'Cash' }}</td>
                </tr>
                <tr>
                    <td class="text-muted small fw-semibold ps-3">Dicatat Oleh</td>
                    <td>{{ $pengeluaran->user?->name ?? '—' }} ({{ $pengeluaran->user?->roleLabel() ?? '—' }})</td>
                </tr>
                <tr>
                    <td class="text-muted small fw-semibold ps-3">Keterangan</td>
                    <td>
                        <div class="p-2 border rounded bg-light text-secondary small" style="white-space: pre-wrap;">{{ $pengeluaran->keterangan ?? 'Tidak ada keterangan.' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted small fw-semibold ps-3">Waktu Pencatatan</td>
                    <td class="small text-muted">{{ $pengeluaran->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
