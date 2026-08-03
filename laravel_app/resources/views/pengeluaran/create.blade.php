@extends('layouts.app')

@section('title', 'Catat Pengeluaran — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Catat Pengeluaran</h1>
        <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm max-width-600">
        <div class="card-body">
            <form action="{{ route('pengeluaran.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="nomor_pengeluaran" class="form-label small fw-semibold">Nomor Pengeluaran</label>
                    <input type="text" id="nomor_pengeluaran" class="form-control form-control-sm bg-light" value="{{ $predictedNomor }}" disabled readonly>
                    <div class="form-text small">Nomor dihasilkan otomatis setelah data disimpan.</div>
                </div>

                <div class="mb-3">
                    <label for="tanggal" class="form-label small fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" class="form-control form-control-sm" required>
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label small fw-semibold">Kategori Pengeluaran</label>
                    <select name="kategori" id="kategori" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(old('kategori') == $cat)>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="nominal" class="form-label small fw-semibold">Nominal (Rupiah)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal" id="nominal" value="{{ old('nominal', 0) }}" min="0" step="0.01" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="metode_pembayaran" class="form-label small fw-semibold">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-select form-select-sm" required>
                        <option value="Cash" @selected(old('metode_pembayaran', 'Cash') === 'Cash')>Cash</option>
                        <option value="Transfer Bank" @selected(old('metode_pembayaran') === 'Transfer Bank')>Transfer Bank</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label small fw-semibold">Keterangan / Rincian</label>
                    <textarea name="keterangan" id="keterangan" rows="3" class="form-control form-control-sm" placeholder="Rincian pengeluaran, nomor tagihan, dll.">{{ old('keterangan') }}</textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4 btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
