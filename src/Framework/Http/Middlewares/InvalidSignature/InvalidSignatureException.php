<?php declare(strict_types=1);

namespace Mage\Framework\Http\Middlewares\InvalidSignature;

use Mage\Framework\Exceptions\Exception;

class InvalidSignatureException extends Exception
{
    public function __construct()
    {
        parent::__construct('validation.invalid-signature', 401);
    }
}
