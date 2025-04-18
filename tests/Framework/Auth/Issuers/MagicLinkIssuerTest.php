<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Issuers;

use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Issuers\MagicLinkIssuer;
use Mage\Framework\Auth\Mail\MagicLinkMail;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\MagicLinkController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class MagicLinkIssuerTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/magic-link', [MagicLinkController::class, '_login'])
            ->name('auth.magic-link.request');
    }

    public function test_magic_link_issuer_mailing_ok(): void
    {
        Mail::fake();

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create();

        $request = $this->getRequest($user->username, $user->password);
        $response = (new MagicLinkIssuer($request, $this->getPasswordLessGrant()))->email(
            'localhost:3000',
            'auth.magic-link.request',
            $this->getTtl(),
            fn (string $link) => new MagicLinkMail($link)
        );

        $this->assertEquals(204, $response->getStatusCode());
        Mail::assertQueued(MagicLinkMail::class);
    }

    public function test_magic_link_issuer_token_ok(): void
    {
        Mail::fake();

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create();

        $request = $this->getRequest($user->username, $user->password, 'token');

        /** @psalm-var string $code */
        $code = '';
        (new MagicLinkIssuer($request, $this->getPasswordLessGrant()))->email(
            'localhost:3000',
            'auth.magic-link.request',
            $this->getTtl(),
            function (string $link) use (&$code) {
                $code = $link;
                return new MagicLinkMail($link);
            }
        );

        parse_str(parse_url($code, PHP_URL_QUERY), $query_params);
        $request = $this->getRequest($user->username, $user->password, (string) $query_params['token']);
        $response = (new MagicLinkIssuer($request, $this->getPasswordLessGrant()))->issue([], false);

        $this->assertEquals(200, $response->getStatusCode());
        Mail::assertQueued(MagicLinkMail::class);
    }

    public function test_magic_link_issuer_cookie_token_ok(): void
    {
        Mail::fake();

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create();

        $request = $this->getRequest($user->username, $user->password, 'token');

        /** @psalm-var string $code */
        $code = '';
        (new MagicLinkIssuer($request, $this->getPasswordLessGrant()))->email(
            'localhost:3000',
            'auth.magic-link.request',
            $this->getTtl(),
            function (string $link) use (&$code) {
                $code = $link;
                return new MagicLinkMail($link);
            }
        );

        parse_str(parse_url($code, PHP_URL_QUERY), $query_params);
        $request = $this->getRequest($user->username, $user->password, (string) $query_params['token']);
        $response = (new MagicLinkIssuer($request, $this->getPasswordLessGrant()))->issue([], true);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNotEmpty($response->headers->getCookies());
        Mail::assertQueued(MagicLinkMail::class);
    }
}
