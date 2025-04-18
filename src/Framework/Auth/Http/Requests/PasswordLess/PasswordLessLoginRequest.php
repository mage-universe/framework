<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Requests\PasswordLess;

use Mage\Framework\Http\Requests\BaseRequest;

/** @psalm-suppress PropertyNotSetInConstructor */
class PasswordLessLoginRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string',
        ];
    }
}
