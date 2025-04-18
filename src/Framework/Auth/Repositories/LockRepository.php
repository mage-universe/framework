<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Repositories;

use DateInterval;
use Illuminate\Support\Facades\Cache;
use Mage\Framework\Auth\Contracts\LockInterface;

final class LockRepository implements LockInterface
{
    private bool $isLockable = true;
    private DateInterval $lockTTL;
    private int $maxTries = 5;
    private DateInterval $triesTTL;

    public function __construct()
    {
        $this->lockTTL = DateInterval::createFromDateString('5 minutes');
        $this->triesTTL = DateInterval::createFromDateString('30 seconds');
    }

    public function isLocked(string $username): bool
    {
        if (!$this->isLockable) {
            return false;
        }
        return Cache::has('auth.lock.' . $username);
    }

    public function lock(string $username): void
    {
        Cache::put('auth.lock.' . $username, true, $this->lockTTL);
    }

    public function incrementTries(string $username): void
    {
        /** @psalm-var int $tries */
        $tries = Cache::get('auth.lock.' . $username . '.tries', 0);
        Cache::put('auth.lock.' . $username . '.tries', $tries + 1, $this->triesTTL);

        if ($tries >= $this->maxTries) {
            $this->lock($username);
            $this->resetTries($username);
        }
    }

    private function unlock(string $username): void
    {
        Cache::forget('auth.lock.' . $username);
    }

    private function resetTries(string $username): void
    {
        Cache::forget('auth.lock.' . $username . '.tries');
    }

    public function resetLock(string $username): void
    {
        $this->unlock($username);
        $this->resetTries($username);
    }

    public function setLockable(bool $isLockable): void
    {
        $this->isLockable = $isLockable;
    }

    public function setLockTTL(DateInterval $lockTTL): void
    {
        $this->lockTTL = $lockTTL;
    }

    public function setMaxTries(int $maxTries): void
    {
        $this->maxTries = $maxTries;
    }

    public function setTriesTTL(DateInterval $triesTTL): void
    {
        $this->triesTTL = $triesTTL;
    }
}
