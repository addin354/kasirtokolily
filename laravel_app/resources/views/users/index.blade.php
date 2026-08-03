@extends('layouts.app')

@section('title', 'Manajemen User — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0">Manajemen User</h1>
            <p class="text-muted small mb-0">Kelola akun pengguna, hak akses, dan peran sistem.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Tambah pengguna</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Role</th>
                        <th style="width: 200px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="fw-medium">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->no_hp ?: '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $u->roleLabel() }}</span></td>
                            <td>
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-warning btn-sm">Edit</a>
                                @if (auth()->id() === $u->id)
                                    <span class="text-muted small ms-1">Akun Anda</span>
                                @else
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pengguna ini?')">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada pengguna.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
@endsection
