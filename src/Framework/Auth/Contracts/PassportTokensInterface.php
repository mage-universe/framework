<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Contracts;

interface PassportTokensInterface
{
    public function removeTokens(int $userId, string $clientId): void;
}
