@extends('layouts.app')

@section('title', 'Edit Satuan — ' . config('app.name'))

@section('content')
    <h1 class="h4 mb-3">Edit Satuan</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('satuans.update', $satuan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama satuan</label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama', $satuan->nama) }}"
                        class="form-control @error('nama') is-invalid @enderror"
                    >
                    @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('products.index', ['tab' => 'satuan']) }}" class="btn btn-outline-secondary">Kembali</a>
            </form>
        </div>
    </div>
@endsection
