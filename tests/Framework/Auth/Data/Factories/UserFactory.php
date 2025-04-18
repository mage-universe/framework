<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mage\Framework\Auth\Database\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id' => 1,
            'username' => 'test@test.com',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'two_factor_secret' => 'C4ALOOZAJB45JSLU',
            'email_verified_at' => null,
            'two_factor_email_enabled' => false,
            'two_factor_app_code_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
