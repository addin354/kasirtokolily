@extends('layouts.app')

@section('title', 'Satuan — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Satuan</h1>
        <a href="{{ route('satuans.create') }}" class="btn btn-primary btn-sm">Tambah Satuan</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th class="text-end">Jumlah Produk</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($satuans as $satuan)
                        <tr>
                            <td class="fw-medium">{{ $satuan->nama }}</td>
                            <td class="text-end">{{ $satuan->products_count }}</td>
                            <td>
                                <a href="{{ route('satuans.edit', $satuan) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('satuans.destroy', $satuan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus satuan ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada satuan. Jalankan <code>php artisan db:seed --class=SatuanSeeder</code> atau tambah manual.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $satuans->links() }}
    </div>
@endsection
