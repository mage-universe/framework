<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Database\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\CanResetPassword
{
    use HasApiTokens, CanResetPassword;

    protected $hidden = ['password', 'remember_token', ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_email_enabled' => 'boolean',
            'two_factor_app_code_enabled' => 'boolean',
        ];
    }
}
