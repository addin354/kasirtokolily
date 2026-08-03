@extends('layouts.app')

@section('title', 'Laporan kasir — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-column flex-md-row flex-wrap justify-content-md-between align-items-stretch align-items-md-center gap-2 mb-3 @if(auth()->user()->isKasir()) app-mobile-pad-bottom @endif">
        <div>
            <h1 class="h4 mb-0">Laporan ke owner</h1>
            <p class="text-muted small mb-0">
                @if(auth()->user()->isKasir())
                    Laporan yang Anda kirim disimpan dari data transaksi aktual.
                @else
                    Laporan dari kasir (status terkirim / dibaca).
                @endif
            </p>
        </div>
        @if(auth()->user()->isKasir())
            <a href="{{ route('reports.create') }}" class="btn btn-primary btn-lg d-md-none w-100">Buat laporan</a>
            <a href="{{ route('reports.create') }}" class="btn btn-primary btn-sm d-none d-md-inline-flex">Buat laporan</a>
        @endif
    </div>

    <div class="d-none d-md-block card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle small">
                    <thead class="table-light">
                    <tr>
                        <th>Tanggal laporan</th>
                        <th>Jenis</th>
                        <th>Kasir</th>
                        <th>Dibuat</th>
                        <th>Status</th>
                        <th style="min-width: 7rem;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->report_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $report->type === 'harian' ? 'Harian' : 'Transaksi' }}</span>
                            </td>
                            <td>{{ $report->user?->name ?? '—' }}</td>
                            <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($report->status === \App\Models\Report::STATUS_DIBACA)
                                    <span class="badge bg-success">Dibaca</span>
                                    @if($report->read_at)
                                        <span class="text-muted">· {{ $report->read_at->format('d/m H:i') }}</span>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark">Terkirim</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                @if(auth()->user()->isKasir())
                                    Belum ada laporan. <a href="{{ route('reports.create') }}">Buat laporan pertama</a>.
                                @else
                                    Belum ada laporan dari kasir.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-md-none vstack gap-2">
        @forelse($reports as $report)
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-1">
                        <div class="fs-6 fw-semibold">{{ $report->report_date->format('d/m/Y') }}</div>
                        <span class="badge bg-secondary">{{ $report->type === 'harian' ? 'Harian' : 'Transaksi' }}</span>
                    </div>
                    <p class="small text-muted mb-1">Kasir: {{ $report->user?->name ?? '—' }}</p>
                    <p class="small text-muted mb-2">Dibuat {{ $report->created_at->format('d/m/Y H:i') }}</p>
                    <div class="mb-2">
                        @if($report->status === \App\Models\Report::STATUS_DIBACA)
                            <span class="badge bg-success">Dibaca</span>
                            @if($report->read_at)
                                <span class="text-muted small">· {{ $report->read_at->format('d/m H:i') }}</span>
                            @endif
                        @else
                            <span class="badge bg-warning text-dark">Terkirim</span>
                        @endif
                    </div>
                    <a href="{{ route('reports.show', $report) }}" class="btn btn-primary w-100 btn-lg-touch">Detail</a>
                </div>
            </div>
        @empty
            <p class="text-center text-muted py-4">
                @if(auth()->user()->isKasir())
                    Belum ada laporan. <a href="{{ route('reports.create') }}">Buat laporan pertama</a>.
                @else
                    Belum ada laporan dari kasir.
                @endif
            </p>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $reports->links() }}
    </div>

    @if(auth()->user()->isKasir())
        <div class="sticky-actions-mobile d-md-none">
            <a href="{{ route('reports.create') }}" class="btn btn-primary w-100 btn-lg">Buat laporan</a>
        </div>
    @endif
@endsection
