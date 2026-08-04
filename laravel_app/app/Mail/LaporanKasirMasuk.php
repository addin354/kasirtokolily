<?php

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LaporanKasirMasuk extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Report $report
    ) {}

    public function envelope(): Envelope
    {
        $kasirName = $this->report->user?->name ?? 'Kasir';
        $tanggal = $this->report->report_date ? $this->report->report_date->format('d/m/Y') : date('d/m/Y');
        $tipeLabel = $this->report->type === Report::TYPE_HARIAN ? 'Ringkasan Harian' : 'Per Transaksi';

        return new Envelope(
            subject: "Laporan Kasir Masuk: {$kasirName} - {$tanggal} ({$tipeLabel})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.laporan-kasir',
        );
    }
}
