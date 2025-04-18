<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Requests\PasswordLess;

use Mage\Framework\Http\Requests\BaseRequest;

/** @psalm-suppress PropertyNotSetInConstructor */
class PasswordLessValidateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string',
        ];
    }
}
