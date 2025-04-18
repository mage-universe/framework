<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Contracts;

interface UserEntityInterface extends \League\OAuth2\Server\Entities\UserEntityInterface
{
    public function getEmail(): ?string;
    public function getIsVerified(): bool;
    public function getIsTwoFactorEmailEnabled(): bool;
    public function getIsTwoFactorAppCodeEnabled(): bool;
    public function getTwoFactorSecret(): ?string;
}
