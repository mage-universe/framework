<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Issuers;

use Mage\Framework\Auth\Issuers\BearerTokenIssuer;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class BearerTokenIssuerTest extends AuthTestCase
{
    public function test_bearer_tokens_issuer_response_ok(): void
    {
        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create();

        $request = $this->getRequest($user->username, 'password');
        $response = (new BearerTokenIssuer($request, $this->getPasswordAuthGrant()))->issue([], false);

        /** @psalm-var object{ token_type:string,expires_in:int,access_token:string,refresh_token:string } $content */
        $content = json_decode((string) $response->getContent());

        $this->assertEquals('Bearer', $content->token_type);
        $this->assertNotEmpty($content->expires_in);
        $this->assertNotEmpty($content->access_token);
        $this->assertNotEmpty($content->refresh_token);

        /** @psalm-var object{jti:string} $token */
        $token = json_decode(base64_decode(explode('.', $content->access_token)[1]));
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $token->jti,
            'user_id' => 1,
            'client_id' => 1,
        ]);
    }
}
