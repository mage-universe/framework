<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** @psalm-suppress PropertyNotSetInConstructor */
class TwoFactorEmail extends Mailable implements ShouldBeEncrypted
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
    ) {
        $this->queue = 'email.tfa';
    }

    public function envelope(): Envelope
    {
        /** @psalm-var string $subject */
        $subject = trans('email.tfa.subject');
        return new Envelope(
            subject: $subject,
            tags: ['tfa'],
            metadata: [
                'code' => $this->code,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'Auth::tfa-email',
            with: ['code' => $this->code]
        );
    }
}
