@extends('layouts.app')

@section('title', 'Detail Retur Penjualan #' . ($retur->no_retur ?? $retur->retur_id) . ' — ' . config('app.name'))

@section('content')
    <div class="mb-3">
        <a href="{{ route('laporan.retur') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            &larr; Kembali ke Daftar Retur
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h4 mb-1">Detail Retur Penjualan</h1>
            <p class="text-muted mb-0">Rincian data barang bukti retur penjualan.</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm px-3" onclick="printRetur()">
            Cetak Struk Retur
        </button>
    </div>

    @if($retur->status === 'Menunggu' && auth()->user()->isOwner())
        <div id="approval-banner" class="alert alert-warning border-0 shadow-sm d-flex flex-wrap justify-content-between align-items-center rounded-3 p-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                        <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044 8.003a.5.5 0 0 0-1 0v.5a.5.5 0 0 0 1 0v-.5zm0-3a.5.5 0 0 0-1 0v1.5a.5.5 0 0 0 1 0v-1.5z"/>
                    </svg>
                </div>
                <div class="text-start">
                    <h6 class="mb-0 fw-bold">Menunggu Persetujuan Owner</h6>
                    <span class="small text-muted">Aksi retur diajukan oleh kasir. Harap setujui untuk memperbarui stok barang atau tolak pengajuan ini.</span>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <button type="button" class="btn btn-success btn-sm px-3 shadow-sm fw-semibold" onclick="processApproval('approve')">Approve</button>
                <button type="button" class="btn btn-danger btn-sm px-3 shadow-sm fw-semibold" onclick="processApproval('reject')">Reject</button>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Rincian Informasi Retur -->
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 bg-white rounded-3 overflow-hidden">
                <div class="card-header bg-dark text-white fw-semibold py-3">
                    Rincian Transaksi Retur #{{ $retur->no_retur ?? $retur->retur_id }}
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-borderless mb-0 align-middle">
                        <tbody>
                            <tr>
                                <th class="ps-4 py-3" style="width: 35%;">ID Retur</th>
                                <td class="pe-4 py-3 fw-semibold">{{ $retur->retur_id }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Nomor Retur</th>
                                <td class="pe-4 py-3">{{ $retur->no_retur ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Nomor Transaksi</th>
                                <td class="pe-4 py-3">
                                    @if($retur->transaksi_id)
                                        <span class="badge bg-light text-dark border">#{{ $retur->transaksi_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Tanggal Retur</th>
                                <td class="pe-4 py-3">{{ $retur->tanggal_retur ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Kasir Penerima</th>
                                <td class="pe-4 py-3">{{ $retur->kasir_nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Produk</th>
                                <td class="pe-4 py-3 fw-semibold text-wrap">{{ $retur->produk_nama }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Harga Satuan</th>
                                <td class="pe-4 py-3 text-primary font-monospace">Rp {{ number_format($retur->harga, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Qty</th>
                                <td class="pe-4 py-3">{{ number_format($retur->qty, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Total Nilai Retur</th>
                                <td class="pe-4 py-3 fw-bold text-success font-monospace">Rp {{ number_format($retur->total, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Alasan Retur</th>
                                <td class="pe-4 py-3 text-wrap">{{ $retur->alasan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3">Status</th>
                                <td class="pe-4 py-3">
                                    @php
                                        $status = $retur->status ?? '-';
                                        $badgeClass = 'bg-secondary';
                                        if ($status === 'Diterima') $badgeClass = 'bg-success-subtle text-success';
                                        elseif ($status === 'Dalam Proses') $badgeClass = 'bg-warning-subtle text-warning-emphasis';
                                        elseif ($status === 'Ditolak') $badgeClass = 'bg-danger-subtle text-danger';
                                        elseif ($status === 'Menunggu') $badgeClass = 'bg-warning text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Foto Bukti Retur -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 bg-white rounded-3 h-100">
                <div class="card-header bg-light fw-semibold py-3 border-bottom-0">
                    Foto Barang Bukti
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                    @php
                        $fotoPath = $retur->foto;
                        if ($fotoPath) {
                            $fotoPath = ltrim($fotoPath, '/');
                            if (str_starts_with($fotoPath, 'storage/')) {
                                $fotoPath = substr($fotoPath, 8);
                            }
                            if (str_starts_with($fotoPath, 'public/')) {
                                $fotoPath = substr($fotoPath, 7);
                            }
                            $fotoUrl = url('storage/' . $fotoPath);
                        } else {
                            $fotoUrl = null;
                        }
                    @endphp
                    @if($fotoUrl)
                        <div class="w-100 overflow-hidden rounded-3 mb-3 border" style="max-height: 350px;">
                            <img src="{{ $fotoUrl }}" alt="Foto Bukti Retur" class="img-fluid w-100" style="object-fit: contain; max-height: 350px;">
                        </div>
                        <a href="{{ $fotoUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary px-3 shadow-sm">
                            Lihat Resolusi Penuh
                        </a>
                    @else
                        <div class="text-muted my-5">
                            <div class="mb-3 text-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-image text-muted" viewBox="0 0 16 16">
                                    <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                                    <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                </svg>
                            </div>
                            <span class="d-block fw-semibold text-secondary">Tidak ada foto bukti</span>
                            <span class="small text-muted">Foto barang bukti retur tidak diupload.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function printRetur() {
            const printWindow = window.open('', '_blank', 'width=600,height=700');
            if (!printWindow) {
                alert('Gagal membuka jendela cetak. Pastikan pop-up blocker dimatikan.');
                return;
            }

            const html = `
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk Retur #${{ $retur->retur_id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .toko { font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="center toko">LILY SEMBAKO</div>
    <div class="center">Bukti Retur Penjualan</div>
    <div class="divider"></div>
    <table>
        <tr><td>No. Retur:</td><td class="right">{{ $retur->no_retur ?? $retur->retur_id }}</td></tr>
        <tr><td>No. Transaksi:</td><td class="right">{{ $retur->transaksi_id ?? '-' }}</td></tr>
        <tr><td>Tanggal:</td><td class="right">{{ $retur->tanggal_retur ?? '-' }}</td></tr>
        <tr><td>Kasir:</td><td class="right">{{ $retur->kasir_nama ?? '-' }}</td></tr>
        <tr><td>Status:</td><td class="right">{{ $retur->status ?? '-' }}</td></tr>
    </table>
    <div class="divider"></div>
    <div class="bold" style="margin-bottom: 4px;">{{ $retur->produk_nama }}</div>
    <table>
        <tr>
            <td>{{ number_format($retur->qty, 0, ',', '.') }} x Rp {{ number_format($retur->harga, 0, ',', '.') }}</td>
            <td class="right bold">Rp {{ number_format($retur->total, 0, ',', '.') }}</td>
        </tr>
    </table>
    <div class="divider"></div>
    <table>
        <tr><td>Alasan:</td><td class="right">{{ $retur->alasan ?? '-' }}</td></tr>
    </table>
    <div class="divider"></div>
    <div class="footer">
        Terima kasih.<br>
        Dokumen ini adalah bukti resmi retur barang.
    </div>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 500);
        };
    <\/script>
</body>
</html>
            `;
            printWindow.document.write(html);
            printWindow.document.close();
        }

        async function processApproval(action) {
            const confirmed = confirm(`Apakah Anda yakin ingin melakukan ${action} pada retur ini?`);
            if (!confirmed) return;

            try {
                const response = await fetch(`{{ url('api/laporan/retur') }}/{{ $retur->retur_id }}/${action}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Gagal memproses persetujuan retur');
                }

                alert(result.message);
                window.location.reload();
            } catch (error) {
                console.error(error);
                alert(error.message || 'Terjadi kesalahan saat memproses data.');
            }
        }
    </script>
@endpush
