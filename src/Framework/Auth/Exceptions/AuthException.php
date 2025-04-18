<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Exceptions;

use Mage\Framework\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

class AuthException extends Exception
{
    public static function invalidCredentials(): self
    {
        return new self('auth.invalid_credentials', Response::HTTP_UNAUTHORIZED);
    }

    public static function lockedAccount(): self
    {
        return new self('auth.locked_account', Response::HTTP_FORBIDDEN);
    }

    public static function invalidToken(): self
    {
        return new self('auth.invalid_token', Response::HTTP_UNAUTHORIZED);
    }

    public static function invalidType(): self
    {
        return new self('auth.invalid_type', Response::HTTP_FORBIDDEN);
    }

    public static function invalidTwoFactorCode(): self
    {
        return new self('auth.invalid_two_factor_code', Response::HTTP_FORBIDDEN);
    }
}
