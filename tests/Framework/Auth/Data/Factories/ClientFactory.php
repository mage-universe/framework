<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Passport\Client;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'id' => 1,
            'user_id' => 0,
            'name' => 'Password',
            'secret' => 'YIohKpRaizpUPokxEEiqZpe6hCea7fv4kTkK2BLR',
            'provider' => 'users',
            'redirect' => 'http://localhost',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
        ];
    }
}
