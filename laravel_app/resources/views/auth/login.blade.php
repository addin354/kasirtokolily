<x-guest-layout>
    @if (session('status'))
        <div class="alert alert-success small mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <h2 class="h5 fw-semibold text-center text-dark mb-4">Masuk ke akun</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                required
                autofocus
                autocomplete="username"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                required
                autocomplete="current-password"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
            <label class="form-check-label" for="remember_me">Ingat saya</label>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
            <button type="submit" class="btn btn-primary px-4">Masuk</button>
        </div>
    </form>
</x-guest-layout>
