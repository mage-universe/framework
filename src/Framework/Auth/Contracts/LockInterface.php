<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Contracts;

use DateInterval;

interface LockInterface
{
    public function isLocked(string $username): bool;
    public function lock(string $username): void;
    public function incrementTries(string $username): void;
    public function resetLock(string $username): void;
    public function setLockable(bool $isLockable): void;
    public function setLockTTL(DateInterval $lockTTL): void;
    public function setMaxTries(int $maxTries): void;
    public function setTriesTTL(DateInterval $triesTTL): void;
}
