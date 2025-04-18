<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Issuers;

use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Mage\Framework\Auth\Grants\PasswordGrant;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

final class BearerTokenIssuer extends Issuer
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly PasswordGrant $grant
    ) {}

    public function issue(array $extraData, bool $responseAsCookie): Response
    {
        $response = $this->grant->respondToAccessTokenRequest(
            $this->request,
            new BearerTokenResponse(),
            $this->grant->accessTokenTTL()
        );
        return $this->bearer($response, $this->grant->accessTokenTTL(), $extraData, $responseAsCookie);
    }
}
