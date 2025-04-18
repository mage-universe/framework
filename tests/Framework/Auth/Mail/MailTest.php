<?php declare(strict_types=1);

namespace Framework\Auth\Mail;

use Mage\Framework\Auth\Mail\MagicLinkMail;
use Mage\Framework\Auth\Mail\ResetPasswordMail;
use Mage\Framework\Auth\Mail\TwoFactorEmail;
use Tests\Framework\Auth\AuthTestCase;

class MailTest extends AuthTestCase
{
    public function test_magic_link_mail(): void
    {
        $mail = new MagicLinkMail('http://localhost:8000/magic-link');
        $this->assertEquals(['magic-link'], $mail->envelope()->tags);
        $this->assertEquals(['link' => 'http://localhost:8000/magic-link'], $mail->envelope()->metadata);
        $this->assertEquals(['link' => 'http://localhost:8000/magic-link'], $mail->content()->with);
        $this->assertStringContainsString('http://localhost:8000/magic-link', $mail->render());
    }

    public function test_tfa_mail(): void
    {
        $mail = new TwoFactorEmail('123456');
        $this->assertEquals(['tfa'], $mail->envelope()->tags);
        $this->assertEquals(['code' => '123456'], $mail->envelope()->metadata);
        $this->assertEquals(['code' => '123456'], $mail->content()->with);
        $this->assertStringContainsString('123456', $mail->render());
    }

    public function test_reset_password_mail(): void
    {
        $mail = new ResetPasswordMail('http://localhost:8000/reset-password');
        $this->assertEquals(['reset-password'], $mail->envelope()->tags);
        $this->assertEquals(['link' => 'http://localhost:8000/reset-password'], $mail->envelope()->metadata);
        $this->assertEquals(['link' => 'http://localhost:8000/reset-password'], $mail->content()->with);
        $this->assertStringContainsString('http://localhost:8000/reset-password', $mail->render());
    }
}
