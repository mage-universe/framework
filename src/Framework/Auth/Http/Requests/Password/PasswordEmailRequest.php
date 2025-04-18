<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Requests\Password;

use Mage\Framework\Http\Requests\BaseRequest;

/** @psalm-suppress PropertyNotSetInConstructor */
class PasswordEmailRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string',
        ];
    }
}
