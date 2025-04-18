<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Tests\Framework\Auth\AuthTestCase;

class MigrationsTest extends AuthTestCase
{
    public function test_migrations_are_created(): void
    {
        $this->assertTrue(Schema::hasTable('migrations'));
        $this->assertTrue(Schema::hasTable('oauth_auth_codes'));
        $this->assertTrue(Schema::hasTable('oauth_access_tokens'));
        $this->assertTrue(Schema::hasTable('oauth_refresh_tokens'));
        $this->assertTrue(Schema::hasTable('oauth_clients'));
        $this->assertTrue(Schema::hasTable('oauth_personal_access_clients'));
        $this->assertTrue(Schema::hasTable('users'));
    }
}
