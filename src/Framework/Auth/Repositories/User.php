<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Repositories;

use Mage\Framework\Auth\Contracts\UserEntityInterface;

/** @psalm-suppress PropertyNotSetInConstructor */
class User extends \Laravel\Passport\Bridge\User implements UserEntityInterface
{
    /** @psalm-param string $identifier */
    public function __construct(
        $identifier,
        private readonly string $email,
        private readonly bool $isVerified,
        private readonly bool $isTwoFactorEmailEnabled,
        private readonly bool $isTwoFactorAppCodeEnabled,
        private readonly ?string $twoFactorSecret,
    ) {
        parent::__construct($identifier);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function getIsTwoFactorEmailEnabled(): bool
    {
        return $this->isTwoFactorEmailEnabled;
    }

    public function getIsTwoFactorAppCodeEnabled(): bool
    {
        return !is_null($this->twoFactorSecret) && $this->isTwoFactorAppCodeEnabled;
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->twoFactorSecret;
    }
}
