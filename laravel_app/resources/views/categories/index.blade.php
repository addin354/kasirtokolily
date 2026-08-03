@extends('layouts.app')

@section('title', 'Kategori — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Kategori</h1>
        <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">Tambah Kategori</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Jumlah Produk</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="fw-medium">{{ $category->nama }}</td>
                            <td class="text-muted small" style="max-width: 320px;">{{ $category->deskripsi ? \Illuminate\Support\Str::limit($category->deskripsi, 80) : '—' }}</td>
                            <td class="text-end">{{ $category->products_count }}</td>
                            <td>
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kategori ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $categories->links() }}
    </div>
@endsection
