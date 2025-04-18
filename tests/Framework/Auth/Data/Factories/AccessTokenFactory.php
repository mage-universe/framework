<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Passport\Token;

class AccessTokenFactory extends Factory
{
    protected $model = Token::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => 1,
            'client_id' => 1,
            'revoked' => 0,
            'scopes' => [],
            'expires_at' => now()->addDay(),
        ];
    }
}
