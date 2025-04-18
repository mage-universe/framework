<?php declare(strict_types=1);

namespace Mage\Framework\Auth\PasswordBroker;

/**
 * @method static \Mage\Framework\Auth\PasswordBroker\PasswordBroker broker(string|null $name = null)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static string sendResetLink(array $credentials, \Closure|null $callback = null)
 * @method static mixed reset(array $credentials, \Closure $callback)
 * @method static \Illuminate\Contracts\Auth\CanResetPassword|null getUser(array $credentials)
 * @method static string createToken(\Illuminate\Contracts\Auth\CanResetPassword $user)
 * @method static string createClientToken(\Illuminate\Contracts\Auth\CanResetPassword $user, int $clientId)
 * @method static void deleteToken(\Illuminate\Contracts\Auth\CanResetPassword $user)
 * @method static bool tokenExists(\Illuminate\Contracts\Auth\CanResetPassword $user, string $token)
 * @method static \Illuminate\Auth\Passwords\TokenRepositoryInterface getRepository()
 *
 * @see BrokerManager
 * @see PasswordBroker
 */
class Password extends \Illuminate\Support\Facades\Password {}
