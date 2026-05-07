<?php

namespace App\Mail;

use App\Models\PembelianLisensi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenseDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PembelianLisensi $pembelian,
        public readonly string $downloadUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lisensi & Link Download Aplikasi Absensi — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.license-delivery',
            with: [
                'namaKlien'   => $this->pembelian->nama_klien,
                'licenseKey'  => $this->pembelian->license_key,
                'domain'      => $this->pembelian->domain,
                'downloadUrl' => $this->downloadUrl,
                'expiresAt'   => $this->pembelian->expires_at,
            ],
        );
    }
}
