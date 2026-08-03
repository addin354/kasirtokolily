@extends('layouts.app')

@section('title', 'Beranda — ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5 text-center">
                    <h1 class="h3 mb-3">{{ config('app.name', 'POS Kasir') }}</h1>
                    <p class="text-muted mb-4">
                        Kelola penjualan, stok, dan laporan dari satu tempat.
                    </p>
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4">Masuk</a>
                    @endguest
                    @auth
                        <a href="{{ route(auth()->user()->defaultDashboardRoute()) }}" class="btn btn-primary btn-lg px-4">Lanjut ke aplikasi</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection
