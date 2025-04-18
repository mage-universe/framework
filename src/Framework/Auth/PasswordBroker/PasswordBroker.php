<?php declare(strict_types=1);

namespace Mage\Framework\Auth\PasswordBroker;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\UserProvider;
use Mage\Framework\Auth\Exceptions\AuthException;

class PasswordBroker extends \Illuminate\Auth\Passwords\PasswordBroker
{
    public function __construct(TokenRepositoryInterface $tokens, UserProvider $users)
    {
        parent::__construct($tokens, $users);
    }

    public function createClientToken(CanResetPassword $user, int $clientId): string
    {
        /**
         * @psalm-var string
         * @psalm-suppress UndefinedInterfaceMethod
         */
        return $this->tokens->createWithClient($user, $clientId);
    }

    public function resetClientToken(CanResetPassword $user, int $clientId, string $token): void
    {
        if (!$this->tokens->exists($user, $token)) {
            throw AuthException::invalidToken();
        }
        /** @psalm-suppress UndefinedInterfaceMethod */
        $this->tokens->deleteExistingWithClient($user, $clientId);
    }
}
