<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Requests\ResetPassword;

use Mage\Framework\Http\Requests\BaseRequest;

/** @psalm-suppress PropertyNotSetInConstructor */
class ResetPasswordForgotRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string',
        ];
    }
}
