@extends('layouts.app')

@section('title', 'Pelanggan — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Pelanggan</h1>
        <a href="{{ route('pelanggan.create') }}" class="btn btn-primary btn-sm">Tambah Pelanggan</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($pelanggans as $pelanggan)
                        <tr>
                            <td class="fw-medium">{{ $pelanggan->nama }}</td>
                            <td>
                                <a href="{{ route('pelanggan.edit', $pelanggan) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('pelanggan.destroy', $pelanggan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pelanggan ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-4 text-muted">Belum ada data pelanggan.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $pelanggans->links() }}
    </div>
@endsection
