<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Repositories;

use Closure;
use DateInterval;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Mage\Framework\Auth\Contracts\UserEntityInterface;
use Mage\Framework\Auth\Contracts\UserRepositoryInterface;
use Mage\Framework\Auth\PasswordBroker\Password;
use RuntimeException;

/**
 * @psalm-suppress all
 * @infection-ignore-all
 */
class UserRepository extends \Laravel\Passport\Bridge\UserRepository implements UserRepositoryInterface
{
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $provider = $clientEntity->provider ?: config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.' . $provider . '.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (method_exists($model, 'findAndValidateForPassport')) {
            $user = (new $model())->findAndValidateForPassport($username, $password);

            if (!$user) {
                return;
            }

            return new User(
                $user->getAuthIdentifier(),
                $user->email,
                $user->email_verified_at ?? false,
                $user->two_factor_email_enabled ?? false,
                $user->two_factor_app_code_enabled ?? false,
                $user->two_factor_secret ?? null,
            );
        }

        if (method_exists($model, 'findForPassport')) {
            $user = (new $model())->findForPassport($username);
        } else {
            $user = (new $model())->where(function ($q) use ($username) {
                $q->where('email', $username)->orWhere('username', $username);
            })->first();
        }

        if (!$user) {
            return;
        } elseif (method_exists($user, 'validateForPassportPasswordGrant')) {
            if (!$user->validateForPassportPasswordGrant($password)) {
                return;
            }
        } elseif (!$this->hasher->check($password, $user->getAuthPassword())) {
            return;
        }

        return new User(
            $user->getAuthIdentifier(),
            $user->email,
            $user->email_verified_at ?? false,
            $user->two_factor_email_enabled ?? false,
            $user->two_factor_app_code_enabled ?? false,
            $user->two_factor_secret ?? null,
        );
    }

    public function getUserEntityByUserName(
        string $username,
        string $grantType,
        ClientEntityInterface $clientEntity
    ): UserEntityInterface {
        $user = $this->getUser($clientEntity, $username);

        if (!$user) {
            return new User('', '', false, false, false, null);
        }

        return new User(
            $user->getAuthIdentifier(),
            $user->email,
            $user->email_verified_at ?? false,
            $user->two_factor_email_enabled ?? false,
            $user->two_factor_app_code_enabled ?? false,
            $user->two_factor_secret ?? null,
        );
    }

    public function queueForgotPasswordTokenToEmail(
        string $username,
        ClientEntityInterface $client,
        string $url,
        string $resetRouteName,
        DateInterval $ttl,
        Closure $mail
    ): void {
        $user = $this->getUser($client, $username);
        if (!$user || is_null($email = $user->getEmailForPasswordReset())) {
            return;
        }

        $token = Password::broker()->createClientToken($user, (int) $client->getIdentifier());
        $link = URL::temporarySignedRoute($resetRouteName, $ttl, [
            'token' => $token,
            'email' => $email,
        ]);

        /** @psalm-var array{query: string} $parsedUrl */
        $parsedUrl = \Safe\parse_url($link);
        $link = $url . '?' . $parsedUrl['query'];

        /** @psalm-var Mailable $mailable */
        $mailable = $mail($link);
        Mail::to($email)->queue($mailable);
    }

    public function resetPassword(
        string $token,
        string $username,
        string $password,
        ClientEntityInterface $client
    ): int {
        $user = $this->getUser($client, $username);
        if (!$user) {
            throw new RuntimeException('Unable to reset password for unknown reason.');
        }
        Password::broker()->resetClientToken($user, (int) $client->getIdentifier(), $token);

        $user->password = Hash::make($password);
        $user->save();

        return $user->id;
    }

    private function getUser(ClientEntityInterface $clientEntity, string $username): mixed
    {
        $provider = $clientEntity->provider ?: config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.' . $provider . '.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (method_exists($model, 'findForPassport')) {
            $user = (new $model())->findForPassport($username);
        } else {
            $user = (new $model())->where(function ($q) use ($username) {
                $q->where('email', $username)->orWhere('username', $username);
            })->first();
        }
        return $user;
    }
}
