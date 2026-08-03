@extends('layouts.app')

@section('title', 'Profil — ' . config('app.name'))

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h1 class="h4 mb-1">Profil</h1>
                <p class="text-muted mb-0">Kelola informasi akun, ganti kata sandi, atau hapus akun Anda.</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
