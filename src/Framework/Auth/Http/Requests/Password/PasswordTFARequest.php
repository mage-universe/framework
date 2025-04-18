<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Requests\Password;

use Mage\Framework\Http\Requests\BaseRequest;

/** @psalm-suppress PropertyNotSetInConstructor */
class PasswordTFARequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'code' => 'required|string',
            'type' => 'required|string|in:email,code',
        ];
    }
}
