<?php declare(strict_types=1);

namespace Mage\Framework\Auth;

use Closure;
use DateInterval;
use Mage\Framework\Auth\Grants\PasswordAuthGrant;
use Mage\Framework\Auth\Issuers\BearerTokenIssuer;
use Mage\Framework\Auth\Issuers\TwoFactorAuthIssuer;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class PasswordAuth
{
    private array $extraData;
    private bool $responseAsCookie;
    private bool $tfaEnabled;
    private DateInterval $tfaTTL;
    private Closure $tfaMail;

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly PasswordAuthGrant $passwordAuthGrant
    ) {
        $this->setAccessTokenTTL('1 year');
        $this->setRefreshTokenTTL('2 years');
        $this->setSingleLogin(false);

        $this->extraData = [];
        $this->responseAsCookie = false;
        $this->tfaEnabled = false;
        $this->tfaTTL = DateInterval::createFromDateString('5 minutes');
        $this->tfaMail = fn (): null => null;

        $this->setLockable(true);
        $this->setLockTTL('5 minutes');
        $this->setMaxTries(5);
        $this->setTriesTTL('30 seconds');
    }

    public function respondToLoginRequest(array $request): Response
    {
        $request = $this->request->withParsedBody($request);
        if ($this->tfaEnabled) {
            return $this->issueTemporalAuthToken($request);
        }
        return $this->issueBearerToken($request, $this->extraData);
    }

    public function respondToLoginUsingTwoFactorAuthTokenRequest(array $request): Response
    {
        $request = $this->request->withParsedBody($request);
        $issuer = new TwoFactorAuthIssuer($request, $this->passwordAuthGrant);
        return $issuer->issue($this->extraData, $this->responseAsCookie);
    }

    public function issueTwoFactorAuthTokenToEmail(array $request): Response
    {
        $request = $this->request->withParsedBody($request);
        $issuer = new TwoFactorAuthIssuer($request, $this->passwordAuthGrant);
        return $issuer->email($this->tfaTTL, $this->tfaMail);
    }

    private function issueTemporalAuthToken(ServerRequestInterface $request): Response
    {
        $issuer = new TwoFactorAuthIssuer($request, $this->passwordAuthGrant);
        $response = $issuer->token($this->tfaTTL);
        if ($response === false) {
            return $this->issueBearerToken($request, $this->extraData);
        }
        return $response;
    }

    private function issueBearerToken(ServerRequestInterface $request, array $extraData): Response
    {
        $issuer = new BearerTokenIssuer($request, $this->passwordAuthGrant);
        return $issuer->issue($extraData, $this->responseAsCookie);
    }

    public function setAccessTokenTTL(string $accessTokenTTL): self
    {
        $this->passwordAuthGrant->setAccessTokenTTL(DateInterval::createFromDateString($accessTokenTTL));
        return $this;
    }

    public function setRefreshTokenTTL(string $refreshTokenTTL): self
    {
        $this->passwordAuthGrant->setRefreshTokenTTL(DateInterval::createFromDateString($refreshTokenTTL));
        return $this;
    }

    public function setExtraData(array $extraData): self
    {
        $this->extraData = $extraData;
        return $this;
    }

    public function setResponseAsCookie(bool $responseAsCookie): self
    {
        $this->responseAsCookie = $responseAsCookie;
        return $this;
    }

    public function setSingleLogin(bool $singleLogin): self
    {
        $this->passwordAuthGrant->setSingleLogin($singleLogin);
        return $this;
    }

    public function setTfaEnabled(bool $tfaEnabled): self
    {
        $this->tfaEnabled = $tfaEnabled;
        return $this;
    }

    public function setTfaTTL(string $tfaTTL): self
    {
        $this->tfaTTL = DateInterval::createFromDateString($tfaTTL);
        return $this;
    }

    public function setLockable(bool $isLockable): self
    {
        $this->passwordAuthGrant->setLockable($isLockable);
        return $this;
    }

    public function setLockTTL(string $lockTTL): self
    {
        $this->passwordAuthGrant->setLockTTL(DateInterval::createFromDateString($lockTTL));
        return $this;
    }

    public function setMaxTries(int $maxTries): self
    {
        $this->passwordAuthGrant->setMaxTries($maxTries);
        return $this;
    }

    public function setTriesTTL(string $triesTTL): self
    {
        $this->passwordAuthGrant->setTriesTTL(DateInterval::createFromDateString($triesTTL));
        return $this;
    }

    public function setTfaMail(Closure $tfaMail): self
    {
        $this->tfaMail = $tfaMail;
        return $this;
    }
}
