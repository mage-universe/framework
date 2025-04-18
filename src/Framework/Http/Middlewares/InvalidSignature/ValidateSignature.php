<?php declare(strict_types=1);

namespace Mage\Framework\Http\Middlewares\InvalidSignature;

use Closure;

/** @psalm-suppress all */
class ValidateSignature extends \Illuminate\Routing\Middleware\ValidateSignature
{
    public function handle($request, Closure $next, ...$args)
    {
        [$relative, $ignore] = $this->parseArguments($args);

        if ($request->hasValidSignatureWhileIgnoring($ignore, !$relative)) {
            return $next($request);
        }

        throw new InvalidSignatureException();
    }
}
