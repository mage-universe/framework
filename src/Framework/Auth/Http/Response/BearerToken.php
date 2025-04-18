<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Response;

use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Nyholm\Psr7\Response;

final readonly class BearerToken
{
    public function __construct(private ResponseTypeInterface $response) {}

    public function token(array $extraData): array
    {
        $response = $this->response->generateHttpResponse(new Response());
        /** @psalm-var array<int, string> $responseArray */
        $responseArray = json_decode($response->getBody()->__toString(), true);
        return [...$responseArray, ...$extraData];
    }
}
