<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Requests\Password;

use Mage\Framework\Http\Requests\BaseRequest;

/** @psalm-suppress PropertyNotSetInConstructor */
class PasswordLoginRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }
}
