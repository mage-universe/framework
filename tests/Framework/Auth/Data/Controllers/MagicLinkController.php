<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Controllers;

use Mage\Framework\Auth\Http\Controllers\PasswordLessAuthController;
use Mage\Framework\Auth\Http\Requests\PasswordLess\PasswordLessLoginRequest;
use Mage\Framework\Auth\Http\Requests\PasswordLess\PasswordLessValidateRequest;
use Symfony\Component\HttpFoundation\Response;

class MagicLinkController extends PasswordLessAuthController
{
    public function login(PasswordLessLoginRequest $request): Response
    {
        return parent::_login($request);
    }

    public function validate(PasswordLessValidateRequest $request): Response
    {
        return parent::_validate($request);
    }

    protected function clientId(): int
    {
        return 1;
    }

    protected function clientSecret(): string
    {
        return 'YIohKpRaizpUPokxEEiqZpe6hCea7fv4kTkK2BLR';
    }

    public function validateRouteName(): string
    {
        return 'auth.magic-link.validate';
    }

    protected function extraData(): array
    {
        return [
            'extra' => 'data',
        ];
    }
}
