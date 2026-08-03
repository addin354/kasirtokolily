<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Product>  $produkBermasalah
     */
    public function __construct(
        public Collection $produkBermasalah,
        public int $batasMinStok
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Peringatan: stok di bawah minimum',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.low-stock',
        );
    }
}
