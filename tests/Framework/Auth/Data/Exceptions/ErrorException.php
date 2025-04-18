<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Exceptions;

use Mage\Framework\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

class ErrorException extends Exception
{
    public static function multiple(): self
    {
        return new self(
            (string) Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            ['error.1', 'error.2', 'error.3', ]
        );
    }
    public static function single(): self
    {
        return new self(
            (string) Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'error.1'
        );
    }
}
