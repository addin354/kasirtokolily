@extends('layouts.app')

{{--
  Contoh pola tampilan responsif (Bootstrap 5).
  Akses (lokal, APP_DEBUG): route dev.ui.patterns
  — atau salin ke halaman lain.
--}}

@section('title', 'Contoh layout responsif — ' . config('app.name'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-2">Contoh layout responsif</h1>
        <p class="text-muted small mb-0">
            Stack: <strong>Bootstrap 5.3</strong> + <code>public/css/app-mobile.css</code> (bukan Tailwind, agar ringan & konsisten).
        </p>
    </div>

    <section class="mb-4">
        <h2 class="h5">Tombol besar (jempol)</h2>
        <p class="small text-muted">Gunakan <code>btn btn-lg</code> + <code>btn-lg-touch</code> (min. tinggi di layar sempit).</p>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-lg btn-lg-touch">Aksi utama</button>
            <button type="button" class="btn btn-outline-secondary btn-lg btn-lg-touch">Sekunder</button>
        </div>
        <p class="small text-muted mt-2 mb-0">Aksi penuh lebar di &lt;576px: <code>app-btn-wide</code> (opsional).</p>
    </section>

    <section class="mb-4 app-data-list">
        <h2 class="h5">Tabel → kartu (mobile)</h2>
        <p class="small text-muted">Dua lapisan: <code>.d-lg-block</code> (tabel) + <code>.d-lg-none</code> (kartu) — sesuaikan breakpoint.</p>

        <div class="d-none d-lg-block border rounded overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle small">
                    <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th class="text-end">Harga</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="fw-medium">Beras premium</td>
                        <td>Sembako</td>
                        <td class="text-end">Rp 15.000</td>
                    </tr>
                    <tr>
                        <td class="fw-medium">Minyak 1L</td>
                        <td>Minyak</td>
                        <td class="text-end">Rp 18.000</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-lg-none vstack gap-2">
            <div class="app-card p-3">
                <div class="fw-semibold">Beras premium</div>
                <div class="small text-muted">Sembako</div>
                <div class="d-flex justify-content-between mt-2 small">
                    <span>Harga</span>
                    <span class="fw-medium">Rp 15.000</span>
                </div>
            </div>
            <div class="app-card p-3">
                <div class="fw-semibold">Minyak 1L</div>
                <div class="small text-muted">Minyak</div>
                <div class="d-flex justify-content-between mt-2 small">
                    <span>Harga</span>
                    <span class="fw-medium">Rp 18.000</span>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-0">
        <h2 class="h5">Navbar hamburger</h2>
        <p class="small text-muted mb-0">
            Header global: tombol hamburger hanya tampil di <code>lg</code> ke bawah, membuka
            <code>offcanvas</code> (#appOffcanvasNav). Per halaman, pola sama: tabel penuh di layar lebar, kartu di sempit.
        </p>
    </section>
@endsection
