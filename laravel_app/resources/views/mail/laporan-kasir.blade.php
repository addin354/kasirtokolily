<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kasir Masuk</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1f2937; background-color: #f3f4f6; margin: 0; padding: 20px 10px;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e5e7eb;">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 24px; text-align: center; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">{{ config('app.name', 'Kasir Toko Lily') }}</h1>
                <p style="margin: 6px 0 0 0; font-size: 14px; opacity: 0.9;">Notifikasi Laporan Kasir Masuk</p>
            </td>
        </tr>

        <!-- Content Body -->
        <tr>
            <td style="padding: 28px 24px;">
                <div style="background-color: #eff6ff; border-left: 4px solid #2563eb; border-radius: 4px; padding: 14px 16px; margin-bottom: 24px;">
                    <p style="margin: 0; font-size: 15px; color: #1e40af; font-weight: 600;">
                        Laporan baru telah dikirimkan oleh Kasir.
                    </p>
                </div>

                <!-- Info Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 14px;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; width: 140px; font-weight: 500;">Nama Kasir</td>
                        <td style="padding: 8px 0; color: #111827; font-weight: 600;">: {{ $report->user?->name ?? 'Kasir' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Tanggal Laporan</td>
                        <td style="padding: 8px 0; color: #111827; font-weight: 600;">: {{ $report->report_date ? $report->report_date->format('d M Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Tipe Laporan</td>
                        <td style="padding: 8px 0; color: #111827; font-weight: 600;">
                            : {{ $report->type === \App\Models\Report::TYPE_HARIAN ? 'Ringkasan Penjualan Harian' : 'Detail Per Transaksi' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Waktu Dikirim</td>
                        <td style="padding: 8px 0; color: #111827; font-weight: 600;">: {{ $report->created_at ? $report->created_at->format('d M Y H:i') : now()->format('d M Y H:i') }} WIB</td>
                    </tr>
                </table>

                @if(!empty($report->notes))
                    <div style="margin-bottom: 24px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 16px;">
                        <h4 style="margin: 0 0 6px 0; font-size: 13px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px;">Catatan Kasir:</h4>
                        <p style="margin: 0; font-size: 14px; color: #374151; white-space: pre-line;">{{ $report->notes }}</p>
                    </div>
                @endif

                <!-- Data Summary Section -->
                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 12px 0; border-bottom: 2px solid #f3f4f6; padding-bottom: 8px;">
                    Ringkasan Data Laporan
                </h3>

                @if($report->type === \App\Models\Report::TYPE_HARIAN)
                    @php
                        $data = $report->data ?? [];
                        $totalRevenue = $data['total_revenue'] ?? 0;
                        $totalTransactions = $data['total_transactions'] ?? 0;
                        $productsSold = $data['products_sold'] ?? [];
                    @endphp
                    <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px; width: 100%;">
                            <span style="font-size: 12px; color: #166534; display: block; font-weight: 500;">Total Omset / Pendapatan</span>
                            <span style="font-size: 20px; font-weight: 700; color: #15803d;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                        </div>
                        <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 14px; width: 100%;">
                            <span style="font-size: 12px; color: #075985; display: block; font-weight: 500;">Jumlah Transaksi</span>
                            <span style="font-size: 20px; font-weight: 700; color: #0284c7;">{{ number_format($totalTransactions, 0, ',', '.') }} Transaksi</span>
                        </div>
                    </div>

                    @if(!empty($productsSold))
                        <h4 style="font-size: 14px; color: #374151; margin: 16px 0 8px 0; font-weight: 600;">Produk Terjual:</h4>
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px;">
                            <thead>
                                <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                                    <th style="padding: 8px 10px; text-align: left; color: #4b5563; font-weight: 600;">Nama Produk</th>
                                    <th style="padding: 8px 10px; text-align: right; color: #4b5563; font-weight: 600; width: 90px;">Jumlah Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productsSold as $produkNama => $qty)
                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 8px 10px; color: #1f2937;">{{ $produkNama }}</td>
                                        <td style="padding: 8px 10px; text-align: right; color: #1f2937; font-weight: 600;">{{ $qty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @elseif($report->type === \App\Models\Report::TYPE_TRANSAKSI)
                    @php
                        $transactions = $report->data ?? [];
                        $totalTrx = count($transactions);
                        $totalRevenue = array_sum(array_column($transactions, 'total'));
                    @endphp
                    <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 14px; margin-bottom: 20px;">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <span style="font-size: 12px; color: #075985; display: block; font-weight: 500;">Total Transaksi:</span>
                                    <span style="font-size: 18px; font-weight: 700; color: #0284c7;">{{ $totalTrx }} Transaksi</span>
                                </td>
                                <td style="text-align: right;">
                                    <span style="font-size: 12px; color: #166534; display: block; font-weight: 500;">Total Nilai Transaksi:</span>
                                    <span style="font-size: 18px; font-weight: 700; color: #15803d;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif

                <!-- Action Button -->
                <div style="text-align: center; margin: 32px 0 16px 0;">
                    <a href="{{ route('reports.show', $report->id) }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-size: 14px; font-weight: 600; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">
                        Buka Detail Laporan di Dashboard
                    </a>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f9fafb; padding: 16px 24px; text-align: center; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 12px;">
                <p style="margin: 0;">Email ini dikirimkan secara otomatis oleh sistem {{ config('app.name', 'Kasir Toko Lily') }}.</p>
            </td>
        </tr>
    </table>
</body>
</html>
