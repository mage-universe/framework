<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Issuers;

use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Issuers\TwoFactorAuthIssuer;
use Mage\Framework\Auth\Mail\TwoFactorEmail;
use RobThree\Auth\TwoFactorAuth;
use Symfony\Component\HttpFoundation\Response;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class TwoFactorAuthIssuerTest extends AuthTestCase
{
    public function test_auth_issuer_respond_correctly(): void
    {
        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null } $user */
        $user = UserFactory::new()->create(['two_factor_app_code_enabled' => true]);

        $request = $this->getRequest($user->username, 'password');

        /** @psalm-var Response $response */
        $response = (new TwoFactorAuthIssuer($request, $this->getPasswordAuthGrant()))->token($this->getTtl());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_auth_issuer_mailed_tfa_correctly(): void
    {
        Mail::fake();

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null } $user */
        $user = UserFactory::new()->create([
            'two_factor_app_code_enabled' => true,
        ]);

        $request = $this->getRequest($user->username, 'password');

        /** @psalm-var Response $response */
        $response = (new TwoFactorAuthIssuer($request, $this->getPasswordAuthGrant()))->token($this->getTtl());
        /** @psalm-var object{ token:string } $content */
        $content = json_decode((string) $response->getContent());

        $request = $this->getRequest($user->username, 'password', $content->token);
        $response = (new TwoFactorAuthIssuer($request, $this->getPasswordAuthGrant()))
            ->email($this->getTtl(), fn (string $code) => new TwoFactorEmail($code));

        $this->assertEquals(204, $response->getStatusCode());
        Mail::assertQueued(TwoFactorEmail::class);
    }

    public function test_generated_tfa_from_app_correctly(): void
    {
        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create(['two_factor_app_code_enabled' => true]);

        $request = $this->getRequest($user->username, 'password');

        /** @psalm-var Response $response */
        $response = (new TwoFactorAuthIssuer($request, $this->getPasswordAuthGrant()))->token($this->getTtl());

        /** @psalm-var object{ token:string } $content */
        $content = json_decode((string) $response->getContent());
        $code = (new TwoFactorAuth())->getCode('C4ALOOZAJB45JSLU');

        $request = $this->getRequest($user->username, 'password', $content->token, $code, 'code');
        $response = (new TwoFactorAuthIssuer($request, $this->getPasswordAuthGrant()))->issue([], false);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
