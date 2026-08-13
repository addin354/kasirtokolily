@extends('layouts.app')

@section('title', 'Supplier — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0">Supplier</h1>
        </div>
        <div class="d-flex gap-2">
            @can('write-data')
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">Tambah supplier</a>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover text-nowrap mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Nama supplier</th>
                        <th>Alamat</th>
                        <th>No. HP</th>
                        <th class="text-end">Stok masuk</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($suppliers as $s)
                        <tr>
                            <td class="fw-medium">{{ $s->nama_supplier }}</td>
                            <td class="small text-muted">{{ $s->alamat ? \Illuminate\Support\Str::limit($s->alamat, 60) : '—' }}</td>
                            <td>{{ $s->no_hp ?? '—' }}</td>
                            <td class="text-end">{{ $s->stok_masuks_count }}</td>
                            <td>
                                @can('write-data')
                                <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('suppliers.destroy', $s) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus supplier ini?')">Hapus</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada supplier. Tambahkan untuk restok.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $suppliers->links() }}
    </div>
@endsection
