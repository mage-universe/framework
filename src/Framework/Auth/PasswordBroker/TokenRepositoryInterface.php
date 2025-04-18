<?php declare(strict_types=1);

namespace Mage\Framework\Auth\PasswordBroker;

use Illuminate\Contracts\Auth\CanResetPassword;

interface TokenRepositoryInterface extends \Illuminate\Auth\Passwords\TokenRepositoryInterface
{
    public function createWithClient(CanResetPassword $user, int $clientId): string;
    public function deleteExistingWithClient(CanResetPassword $user, int $clientId): int;
}
