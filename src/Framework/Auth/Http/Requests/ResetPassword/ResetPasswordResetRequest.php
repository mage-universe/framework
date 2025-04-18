<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Requests\ResetPassword;

use Mage\Framework\Auth\Http\Requests\Rules\StrongPassword;
use Mage\Framework\Http\Requests\BaseRequest;

/** @psalm-suppress PropertyNotSetInConstructor */
class ResetPasswordResetRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'token' => 'required|string',
            'password' => ['required', 'string', 'confirmed', new StrongPassword($this->prefix())],
        ];
    }
}
