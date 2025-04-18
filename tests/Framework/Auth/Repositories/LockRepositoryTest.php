<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Repositories;

use Illuminate\Support\Facades\Cache;
use Mage\Framework\Auth\Repositories\LockRepository;
use Tests\Framework\Auth\AuthTestCase;

class LockRepositoryTest extends AuthTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Cache::forget('auth.lock.test');
        Cache::forget('auth.lock.test.tries');
    }

    public function tearDown(): void
    {
        Cache::forget('auth.lock.test');
        Cache::forget('auth.lock.test.tries');
        parent::tearDown();
    }

    public function test_check_if_can_be_locked(): void
    {
        $lockRepository = new LockRepository();
        $this->assertFalse($lockRepository->isLocked('test'));
        $lockRepository->setLockable(false);
        $this->assertFalse($lockRepository->isLocked('test'));
    }

    public function test_lock_username(): void
    {
        $lockRepository = new LockRepository();
        $lockRepository->lock('test');
        $this->assertTrue($lockRepository->isLocked('test'));
        $this->assertTrue(Cache::get('auth.lock.test'));
    }

    public function test_unlock_username(): void
    {
        $lockRepository = new LockRepository();
        $lockRepository->lock('test');
        $this->assertTrue($lockRepository->isLocked('test'));
        $lockRepository->resetLock('test');
        $this->assertFalse($lockRepository->isLocked('test'));
        $this->assertFalse(Cache::has('auth.lock.test'));
    }

    public function test_lock_username_after_tries(): void
    {
        $lockRepository = new LockRepository();
        $lockRepository->setMaxTries(3);
        $lockRepository->incrementTries('test');
        $this->assertTrue(Cache::get('auth.lock.test.tries') === 1);
        $lockRepository->incrementTries('test');
        $this->assertTrue(Cache::get('auth.lock.test.tries') === 2);
        $lockRepository->incrementTries('test');
        $this->assertTrue(Cache::get('auth.lock.test.tries') === 3);
        $lockRepository->incrementTries('test');

        $this->assertTrue($lockRepository->isLocked('test'));
        $this->assertFalse(Cache::has('auth.lock.test.tries'));

        $lockRepository->incrementTries('test');
        $this->assertTrue(Cache::get('auth.lock.test.tries') === 1);
    }

    public function test_reset_username_lock(): void
    {
        $lockRepository = new LockRepository();
        $lockRepository->lock('test');
        $lockRepository->incrementTries('test');
        $lockRepository->resetLock('test');
        $this->assertFalse($lockRepository->isLocked('test'));
        $this->assertFalse(Cache::has('auth.lock.test.tries'));
    }
}
