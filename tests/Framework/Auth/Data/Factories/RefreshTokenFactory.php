<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Passport\RefreshToken;

class RefreshTokenFactory extends Factory
{
    protected $model = RefreshToken::class;

    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'access_token_id' => $this->faker->uuid(),
            'revoked' => 0,
            'expires_at' => now()->addDay(),
        ];
    }
}
