<?php declare(strict_types=1);

namespace Mage\Framework\Auth\PasswordBroker;

use Illuminate\Auth\Passwords\DatabaseTokenRepository as IlluminateDatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class DatabaseTokenRepository extends IlluminateDatabaseTokenRepository implements TokenRepositoryInterface
{
    public function createWithClient(CanResetPasswordContract $user, int $clientId): string
    {
        $email = $user->getEmailForPasswordReset();

        $this->deleteExistingWithClient($user, $clientId);

        $token = $this->createNewToken();

        $this->getTable()->insert(['client_id' => $clientId, ...$this->getPayload($email, $token)]);

        return $token;
    }

    public function deleteExistingWithClient(CanResetPasswordContract $user, int $clientId): int
    {
        return $this->getTable()
            ->where('email', $user->getEmailForPasswordReset())
            ->where('client_id', $clientId)
            ->delete();
    }
}
