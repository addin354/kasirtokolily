@extends('layouts.app')

@section('title', 'Detail laporan — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Detail laporan</h1>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2 small">
                <div class="col-md-6">
                    <span class="text-muted">Jenis</span>
                    <div class="fw-medium">{{ $typeList[$report->type] ?? $report->type }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">Tanggal data</span>
                    <div class="fw-medium">{{ $report->report_date->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">Kasir</span>
                    <div class="fw-medium">{{ $report->user?->name }} ({{ $report->user?->email }})</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">Dibuat</span>
                    <div class="fw-medium">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="col-12">
                    <span class="text-muted">Status</span>
                    <div>
                        @if($report->status === \App\Models\Report::STATUS_DIBACA)
                            <span class="badge bg-success">Dibaca</span>
                            @if($report->read_at)
                                <span class="text-muted">· Dibaca {{ $report->read_at->format('d/m/Y H:i') }}</span>
                            @endif
                        @else
                            <span class="badge bg-warning text-dark">Terkirim</span>
                        @endif
                    </div>
                </div>
                @if($report->notes)
                    <div class="col-12">
                        <span class="text-muted">Catatan kasir</span>
                        <div class="border rounded p-2 bg-light mt-1">{{ $report->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header py-2">
            <span class="fw-semibold">Isi laporan (snapshot database)</span>
        </div>
        <div class="card-body">
            @if($report->type === 'harian' && is_array($report->data))
                <p class="small text-muted mb-2">Ringkasan penjualan tanggal <strong>{{ $report->report_date->format('d/m/Y') }}</strong></p>
                <ul class="list-unstyled mb-3 small">
                    <li>Total penjualan: <strong>Rp {{ number_format($report->data['total_revenue'] ?? 0, 0, ',', '.') }}</strong></li>
                    <li>Jumlah transaksi: <strong>{{ (int) ($report->data['total_transactions'] ?? 0) }}</strong></li>
                </ul>
                <h2 class="h6">Terjual per produk</h2>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th class="text-end">Qty</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($report->data['products_sold'] ?? [] as $nama => $qty)
                            <tr>
                                <td>{{ $nama }}</td>
                                <td class="text-end">{{ (int) $qty }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-muted">Tidak ada penjualan produk tercatat.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif($report->type === 'transaksi' && is_array($report->data))
                <p class="small text-muted mb-2">Semua transaksi tanggal <strong>{{ $report->report_date->format('d/m/Y') }}</strong></p>
                @forelse($report->data as $tx)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex flex-wrap justify-content-between gap-2 small fw-medium">
                            <span>Transaksi #{{ $tx['id'] ?? '—' }}</span>
                            <span>Total: Rp {{ number_format($tx['total'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="table-responsive mt-1">
                            <table class="table table-sm table-borderless small mb-0">
                                <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tx['items'] ?? [] as $row)
                                    <tr>
                                        <td>{{ $row['produk'] ?? '—' }}</td>
                                        <td class="text-end">{{ (int) ($row['qty'] ?? 0) }}</td>
                                        <td class="text-end">Rp {{ number_format($row['harga'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($row['subtotal'] ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Tidak ada transaksi pada tanggal ini.</p>
                @endforelse
            @else
                <p class="text-muted mb-0">Tidak ada data.</p>
            @endif
        </div>
    </div>
@endsection
