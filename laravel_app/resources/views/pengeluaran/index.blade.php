@extends('layouts.app')

@section('title', 'Daftar Pengeluaran — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Pengeluaran Toko</h1>
        @can('write-data')
        <a href="{{ route('pengeluaran.create') }}" class="btn btn-primary btn-sm">Catat Pengeluaran</a>
        @endcan
    </div>

    <!-- Dashboard Kecil (Summary Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-info">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Hari Ini</div>
                    <div class="fs-4 fw-bold text-dark">Rp {{ number_format($pengeluaranHariIni, 0, ',', '.') }}</div>
                    <div class="small text-muted">Total pengeluaran hari ini</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-primary">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Minggu Ini</div>
                    <div class="fs-4 fw-bold text-primary">Rp {{ number_format($pengeluaranMingguIni, 0, ',', '.') }}</div>
                    <div class="small text-muted">Senin s.d. Minggu ini</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-success">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Bulan Ini</div>
                    <div class="fs-4 fw-bold text-success">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</div>
                    <div class="small text-muted">Total bulan berjalan</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-warning">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Tahun Ini</div>
                    <div class="fs-4 fw-bold text-warning">Rp {{ number_format($pengeluaranTahunIni, 0, ',', '.') }}</div>
                    <div class="small text-muted">Total tahun berjalan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pengeluaran.index') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="q" class="form-label small mb-1">Cari nomor pengeluaran / keterangan</label>
                    <input type="text" name="q" id="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Ketik lalu enter...">
                </div>

                <div class="col-12 col-md-3">
                    <label for="kategori" class="form-label small mb-1">Kategori</label>
                    <select name="kategori" id="kategori" class="form-select form-select-sm">
                        <option value="">-- Semua Kategori --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('kategori') == $cat)>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label for="tanggal_dari" class="form-label small mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_dari" id="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
                </div>

                <div class="col-12 col-md-2">
                    <label for="tanggal_sampai" class="form-label small mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_sampai" id="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
                </div>

                <div class="col-12 col-md-1 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">✕</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tombol Ekspor -->
    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('pengeluaran.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-pdf" viewBox="0 0 16 16">
                <path d="M4 .5a.5.5 0 0 0-1 0V1H1.5a.5.5 0 0 0 0 1H3v1H1.5a.5.5 0 0 0 0 1H3v1H1.5a.5.5 0 0 0 0 1H3v1H1.5a.5.5 0 0 0 0 1H3v1H1.5a.5.5 0 0 0 0 1H3v1h-1.5a.5.5 0 0 0 0 1H3v1.5a.5.5 0 0 0 1 0V15h1.5a.5.5 0 0 0 0-1H4v-1h1.5a.5.5 0 0 0 0-1H4v-1h1.5a.5.5 0 0 0 0-1H4v-1h1.5a.5.5 0 0 0 0-1H4V6h1.5a.5.5 0 0 0 0-1H4V4h1.5a.5.5 0 0 0 0-1H4V2h1.5a.5.5 0 0 0 0-1H4z"/>
                <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zM13 5.018l-3-3V5h3z"/>
            </svg>
            PDF
        </a>
        <a href="{{ route('pengeluaran.export.excel', request()->query()) }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-excel" viewBox="0 0 16 16">
                <path d="M5.18 4.616a.5.5 0 0 1 .704.03L8 7.293l2.116-2.647a.5.5 0 1 1 .768.64L8.707 8l2.177 2.716a.5.5 0 1 1-.768.64L8 8.707l-2.116 2.647a.5.5 0 0 1-.768-.64L7.293 8 5.116 5.284a.5.5 0 0 1 .064-.668z"/>
                <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zM13 5.018l-3-3V5h3z"/>
            </svg>
            Excel (CSV)
        </a>
    </div>

    <!-- Data Tabel -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover text-nowrap mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Nomor</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nominal</th>
                        <th>User</th>
                        <th style="width: 180px;" class="text-center">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($pengeluarans as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->nomor_pengeluaran }}</td>
                            <td>{{ $p->tanggal->format('d/m/Y') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $p->kategori }}</span></td>
                            <td class="small text-muted">{{ $p->keterangan ? \Illuminate\Support\Str::limit($p->keterangan, 50) : '—' }}</td>
                            <td class="text-end fw-medium text-danger">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td>{{ $p->user?->name ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('pengeluaran.show', $p) }}" class="btn btn-outline-info btn-sm">Detail</a>
                                @can('write-data')
                                <a href="{{ route('pengeluaran.edit', $p) }}" class="btn btn-outline-warning btn-sm">Edit</a>
                                <form action="{{ route('pengeluaran.destroy', $p) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-pengeluaran">Hapus</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada catatan pengeluaran.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $pengeluarans->links() }}
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-delete-pengeluaran').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Hapus pengeluaran ini?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
