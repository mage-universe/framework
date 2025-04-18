<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** @psalm-suppress PropertyNotSetInConstructor */
class ResetPasswordMail extends Mailable implements ShouldBeEncrypted
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $link,
    ) {
        $this->queue = 'email.reset-password';
    }

    public function envelope(): Envelope
    {
        /** @psalm-var string $subject */
        $subject = trans('email.reset-password.subject');
        return new Envelope(
            subject: $subject,
            tags: ['reset-password'],
            metadata: [
                'link' => $this->link,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'Auth::reset-password-email',
            with: ['link' => $this->link]
        );
    }
}
