{{-- Fragment untuk refresh AJAX keranjang kasir --}}
@if ($lines->isEmpty())
    <p class="text-muted p-3 mb-0">Keranjang kosong.</p>
@else
    {{-- DESKTOP --}}
    <div class="table-responsive d-none d-md-block">
    <table class="table table-sm table-striped mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th>Produk</th>
                <th class="text-center" style="width: 88px;">Jenis</th>
                <th class="text-end">Harga</th>
                <th class="text-center" style="min-width: 110px;">Qty</th>
                <th class="text-end">Subtotal</th>
                <th style="width: 44px;"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($lines as $line)
                <tr class="{{ !empty($line['stok_kurang']) ? 'table-warning' : '' }}">
                    <td>
                        <div class="small fw-medium">{{ $line['nama'] }}</div>
                        <div class="small text-muted">{{ $line['kode'] }}</div>
                        @if (!empty($line['preview_bal']))
                            <div class="small text-muted mt-1">{{ $line['preview_bal'] }}</div>
                        @endif
                        @if (!empty($line['preview_stok_kurang']))
                            <div class="small text-muted">{{ $line['preview_stok_kurang'] }}</div>
                        @endif
                        @if (!empty($line['stok_kurang']))
                            <div class="small text-danger">Stok tersedia: {{ number_format((int) $line['stok_tersedia'], 0, ',', '.') }} pcs</div>
                        @endif
                    </td>
<td>
    
    <select
    class="form-select form-select-sm kasir-price-type"
    data-line-id="{{ $line['line_id'] }}"
>
        <option value="eceran"
            {{ $line['jenis_harga'] == 'eceran' ? 'selected' : '' }}>
            Eceran
        </option>

        <option value="grosir"
            {{ $line['jenis_harga'] == 'grosir' ? 'selected' : '' }}>
            Grosir
        </option>

        <option value="bal"
            {{ $line['jenis_harga'] == 'bal' ? 'selected' : '' }}>
            Bal
        </option>
    </select>
</td>                   
<td class="text-end small">Rp {{ number_format($line['harga'], 0, ',', '.') }}</td>
                        <input
                            type="text"
                            inputmode="decimal"
                            value="{{ (float) $line['qty'] == (int) $line['qty'] ? (int) $line['qty'] : (float) $line['qty'] }}"
                            class="form-control form-control-sm kasir-qty-input text-center"
                            data-line-id="{{ $line['line_id'] }}"
                            style="width:80px;"
                        >
                    <td class="text-end small">Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</td>
                    <td>
                        <button type="button" class="btn btn-link btn-sm text-danger p-0 kasir-remove" data-line-id="{{ $line['line_id'] }}" style="text-decoration: none;">
                            <i class="bi bi-trash fs-5"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{-- MOBILE --}}
<div class="d-md-none p-2">

    @foreach ($lines as $line)

    <div class="card mb-2 shadow-sm">

        <div class="card-body p-2">

            <div class="fw-bold">
                {{ $line['nama'] }}
            </div>

            <small class="text-muted">
                {{ $line['kode'] }}
            </small>

            <div class="row mt-2">

                <div class="col-6">
                    <small class="text-muted">Jenis Harga</small>

                    <select
    class="form-select form-select-sm kasir-price-type"
    data-line-id="{{ $line['line_id'] }}"
>
                        <option value="eceran"
                            {{ $line['jenis_harga']=='eceran' ? 'selected':'' }}>
                            Eceran
                        </option>

                        <option value="grosir"
                            {{ $line['jenis_harga']=='grosir' ? 'selected':'' }}>
                            Grosir
                        </option>

                        <option value="bal"
                            {{ $line['jenis_harga']=='bal' ? 'selected':'' }}>
                            Bal
                        </option>
                    </select>
                </div>

                <div class="col-6">
                    <small class="text-muted">Harga</small>

                    <div class="fw-semibold">
                        Rp {{ number_format($line['harga'],0,',','.') }}
                    </div>
                </div>

            </div>

            <div class="row mt-2 align-items-center">

                <div class="col-6">

                    <small class="text-muted">
                        Qty
                    </small>

                    <div class="input-group input-group-sm">

    <button
        class="btn btn-outline-secondary kasir-minus"
        data-line-id="{{ $line['line_id'] }}"
    >
        -
    </button>

    <input
        type="text"
        inputmode="decimal"
        class="form-control text-center kasir-qty"
        data-line-id="{{ $line['line_id'] }}"
        value="{{ (float) $line['qty'] == (int) $line['qty'] ? (int) $line['qty'] : (float) $line['qty'] }}"
    >

    <button
        class="btn btn-outline-secondary kasir-plus"
        data-line-id="{{ $line['line_id'] }}"
    >
        +
    </button>

</div>

                </div>

                <div class="col-6 text-end">

                    <small class="text-muted">
                        Subtotal
                    </small>

                    <div class="fw-bold text-success">
                        Rp {{ number_format($line['subtotal'],0,',','.') }}
                    </div>

                </div>

            </div>

            @if (!empty($line['stok_kurang']))
                <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small">
                    Stok tersedia:
                    {{ number_format($line['stok_tersedia'],0,',','.') }}
                    pcs
                </div>
            @endif

            <button
                class="btn btn-outline-danger btn-sm w-100 mt-2 kasir-remove"
                data-line-id="{{ $line['line_id'] }}"
            >
                Hapus
            </button>

        </div>

    </div>

    @endforeach

</div>
    
@endif
