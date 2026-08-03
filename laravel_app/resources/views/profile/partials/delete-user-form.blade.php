<section>
    <div class="mb-4">
        <h2 class="h5 mb-1">Hapus akun</h2>
        <p class="text-muted mb-0">Setelah akun dihapus, semua data akan dihapus permanen. Sebelum menghapus, unduh data yang ingin Anda simpan.</p>
    </div>

    <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Yakin ingin menghapus akun? Semua data akan dihapus permanen.');">
        @csrf
        @method('delete')

        <div class="mb-3">
            <label for="password" class="form-label">Kata sandi</label>
            <input id="password" name="password" type="password" class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif" placeholder="Kata sandi">
            @if($errors->userDeletion->has('password'))
                <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
            @endif
        </div>

        <button type="submit" class="btn btn-danger">Hapus akun</button>
    </form>
</section>
