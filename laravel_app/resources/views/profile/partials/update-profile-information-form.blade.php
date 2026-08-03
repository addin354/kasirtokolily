<section>
    <div class="mb-4">
        <h2 class="h5 mb-1">Informasi profil</h2>
        <p class="text-muted mb-0">Perbarui nama dan alamat email akun Anda.</p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mb-0">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-3 mb-0">
                    <div class="mb-2">Alamat email Anda belum diverifikasi.</div>
                    <button type="submit" form="send-verification" class="btn btn-sm btn-outline-primary">Kirim ulang email verifikasi</button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success mt-3 mb-0">
                        Tautan verifikasi baru telah dikirim ke email Anda.
                    </div>
                @endif
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>

            @if (session('status') === 'profile-updated')
                <div class="text-success small">Tersimpan.</div>
            @endif
        </div>
    </form>
</section>
