@extends('layouts.app')

@section('title', 'Buat laporan — ' . config('app.name'))

@section('content')
    <h1 class="h4 mb-3">Buat laporan ke owner</h1>
    <p class="text-muted small">Data diambil otomatis dari transaksi pada tanggal yang dipilih (tanpa entri manual).</p>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('reports.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="type" class="form-label">Jenis laporan</label>
                    <select
                        name="type"
                        id="type"
                        class="form-select @error('type') is-invalid @enderror"
                        required
                    >
                        <option value="">— Pilih —</option>
                        @foreach($typeList as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="report_date" class="form-label">Tanggal</label>
                    <input
                        type="date"
                        name="report_date"
                        id="report_date"
                        value="{{ old('report_date', now()->toDateString()) }}"
                        class="form-control @error('report_date') is-invalid @enderror"
                        required
                    >
                    @error('report_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan (opsional)</label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="2"
                        class="form-control @error('notes') is-invalid @enderror"
                        placeholder="Contoh: shift pagi, penjelasan khusus…"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan &amp; kirim</button>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
