<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Contracts;

use Closure;
use DateInterval;
use League\OAuth2\Server\Entities\ClientEntityInterface;

interface UserRepositoryInterface extends \League\OAuth2\Server\Repositories\UserRepositoryInterface
{
    public function getUserEntityByUserName(
        string $username,
        string $grantType,
        ClientEntityInterface $clientEntity
    ): UserEntityInterface;

    public function queueForgotPasswordTokenToEmail(
        string $username,
        ClientEntityInterface $client,
        string $url,
        string $resetRouteName,
        DateInterval $ttl,
        Closure $mail
    ): void;
}
