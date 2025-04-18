<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Request;

/** @psalm-suppress PropertyNotSetInConstructor */
class ResetPasswordResetRequest extends \Mage\Framework\Auth\Http\Requests\ResetPassword\ResetPasswordResetRequest
{
    protected function prefix(): string
    {
        return 'prefix';
    }
}
