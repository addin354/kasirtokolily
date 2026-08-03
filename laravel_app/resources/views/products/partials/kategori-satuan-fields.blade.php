@php
    $product = $product ?? null;
    $kategoriVal = old('kategori_id', $product?->kategori_id);
    $satuanVal = old('satuan_id', $product?->satuan_id);
@endphp

<div class="row g-3 align-items-end mb-0">
    <div class="col-12 col-md-6">
        <label for="kategori_id" class="form-label">Kategori</label>
        <div class="input-group input-group-lg">
            <select
                id="kategori_id"
                name="kategori_id"
                class="form-select @error('kategori_id') is-invalid @enderror"
            >
                <option value="">-- Pilih kategori --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $kategoriVal === (string) $category->id)>
                        {{ $category->nama }}
                    </option>
                @endforeach
            </select>
            <button
                class="btn btn-outline-primary btn-lg-touch d-flex align-items-center justify-content-center gap-1"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#modalKategoriBaru"
                title="Tambah kategori baru"
            >
                <span class="fw-bold" aria-hidden="true">+</span><span class="d-none d-sm-inline">Baru</span>
            </button>
        </div>
        @error('kategori_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="satuan_id" class="form-label">Satuan</label>
        <div class="input-group input-group-lg">
            <select
                id="satuan_id"
                name="satuan_id"
                class="form-select @error('satuan_id') is-invalid @enderror"
            >
                <option value="">— Opsional —</option>
                @foreach($satuans as $satuan)
                    <option value="{{ $satuan->id }}" @selected((string) $satuanVal === (string) $satuan->id)>
                        {{ $satuan->nama }}
                    </option>
                @endforeach
            </select>
            <button
                class="btn btn-outline-primary btn-lg-touch d-flex align-items-center justify-content-center gap-1"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#modalSatuanBaru"
                title="Tambah satuan baru"
            >
                <span class="fw-bold" aria-hidden="true">+</span><span class="d-none d-sm-inline">Baru</span>
            </button>
        </div>
        @error('satuan_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Modal kategori --}}
<div class="modal fade" id="modalKategoriBaru" tabindex="-1" aria-labelledby="modalKategoriBaruLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="modalKategoriBaruLabel">Kategori baru</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="modalKategoriErrors" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <div class="mb-3">
                    <label for="modal_nama_kategori" class="form-label">Nama kategori</label>
                    <input type="text" class="form-control" id="modal_nama_kategori" maxlength="255" autocomplete="off">
                </div>
                <div class="mb-0">
                    <label for="modal_desk_kategori" class="form-label">Deskripsi <span class="text-muted">(opsional)</span></label>
                    <textarea class="form-control" id="modal_desk_kategori" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanKategoriModal">Simpan</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal satuan --}}
<div class="modal fade" id="modalSatuanBaru" tabindex="-1" aria-labelledby="modalSatuanBaruLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="modalSatuanBaruLabel">Satuan baru</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="modalSatuanErrors" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <div class="mb-0">
                    <label for="modal_nama_satuan" class="form-label">Nama satuan</label>
                    <input type="text" class="form-control" id="modal_nama_satuan" maxlength="50" autocomplete="off" placeholder="cth. pcs, dus, kg">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanSatuanModal">Simpan</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const kategoriSelect = document.getElementById('kategori_id');
    const satuanSelect = document.getElementById('satuan_id');
    if (!kategoriSelect || !satuanSelect) return;

    const urlKategori = @json(route('categories.store-json'));
    const urlSatuan = @json(route('satuans.store-json'));

    function setErrors(el, message) {
        if (!el) return;
        if (message) {
            el.textContent = message;
            el.classList.remove('d-none');
        } else {
            el.textContent = '';
            el.classList.add('d-none');
        }
    }

    function formatValidationErrors(data) {
        if (!data.errors) return data.message || 'Validasi gagal.';
        return Object.values(data.errors).flat().join(' ');
    }

    function appendOption(select, id, label) {
        const o = document.createElement('option');
        o.value = id;
        o.textContent = label;
        select.appendChild(o);
    }

    document.getElementById('btnSimpanKategoriModal')?.addEventListener('click', async function () {
        const errEl = document.getElementById('modalKategoriErrors');
        setErrors(errEl, '');
        const nama = document.getElementById('modal_nama_kategori')?.value?.trim() || '';
        const deskripsi = document.getElementById('modal_desk_kategori')?.value?.trim() || null;
        if (!nama) {
            setErrors(errEl, 'Nama kategori wajib diisi.');
            return;
        }
        try {
            const res = await fetch(urlKategori, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ nama, deskripsi }),
            });
            const data = await res.json();
            if (!res.ok) {
                setErrors(errEl, formatValidationErrors(data) || 'Gagal menyimpan kategori.');
                return;
            }
            appendOption(kategoriSelect, data.id, data.nama);
            kategoriSelect.value = String(data.id);
            document.getElementById('modal_nama_kategori').value = '';
            document.getElementById('modal_desk_kategori').value = '';
            (function (id) {
                const el = document.getElementById(id);
                const m = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
                m.hide();
            })('modalKategoriBaru');
        } catch (e) {
            setErrors(errEl, 'Koneksi gagal, coba lagi.');
        }
    });

    document.getElementById('btnSimpanSatuanModal')?.addEventListener('click', async function () {
        const errEl = document.getElementById('modalSatuanErrors');
        setErrors(errEl, '');
        const nama = document.getElementById('modal_nama_satuan')?.value?.trim() || '';
        if (!nama) {
            setErrors(errEl, 'Nama satuan wajib diisi.');
            return;
        }
        try {
            const res = await fetch(urlSatuan, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ nama }),
            });
            const data = await res.json();
            if (!res.ok) {
                setErrors(errEl, formatValidationErrors(data) || 'Gagal menyimpan satuan.');
                return;
            }
            appendOption(satuanSelect, data.id, data.nama);
            satuanSelect.value = String(data.id);
            document.getElementById('modal_nama_satuan').value = '';
            (function (id) {
                const el = document.getElementById(id);
                const m = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
                m.hide();
            })('modalSatuanBaru');
        } catch (e) {
            setErrors(errEl, 'Koneksi gagal, coba lagi.');
        }
    });

    document.getElementById('modalKategoriBaru')?.addEventListener('show.bs.modal', function () {
        setErrors(document.getElementById('modalKategoriErrors'), '');
    });
    document.getElementById('modalSatuanBaru')?.addEventListener('show.bs.modal', function () {
        setErrors(document.getElementById('modalSatuanErrors'), '');
    });
})();
</script>
@endpush
