<?php declare(strict_types=1);

namespace Mage\Framework\Auth;

use Closure;
use DateInterval;
use Mage\Framework\Auth\Grants\PasswordLessGrant;
use Mage\Framework\Auth\Issuers\MagicLinkIssuer;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class PasswordLessAuth
{
    private array $extraData;
    private bool $responseAsCookie;
    private DateInterval $linkTTL;
    private string $linkUrl;
    private string $validateRouteName;
    private Closure $linkMail;

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly PasswordLessGrant $passwordLessGrant
    ) {
        $this->setAccessTokenTTL('1 year');
        $this->setRefreshTokenTTL('2 years');
        $this->setSingleLogin(false);

        $this->extraData = [];
        $this->responseAsCookie = false;
        $this->linkTTL = DateInterval::createFromDateString('5 minutes');
        $this->linkUrl = '';
        $this->validateRouteName = '';
        $this->linkMail = fn (): null => null;
    }

    public function respondToLoginRequest(array $request): Response
    {
        $request = $this->request->withParsedBody($request);
        $issuer = new MagicLinkIssuer($request, $this->passwordLessGrant);
        return $issuer->email($this->linkUrl, $this->validateRouteName, $this->linkTTL, $this->linkMail);
    }

    public function respondToMagicLinkTokenRequest(array $request): Response
    {
        $request = $this->request->withParsedBody($request);
        $issuer = new MagicLinkIssuer($request, $this->passwordLessGrant);
        return $issuer->issue($this->extraData, $this->responseAsCookie);
    }

    public function setAccessTokenTTL(string $accessTokenTTL): self
    {
        $this->passwordLessGrant->setAccessTokenTTL(DateInterval::createFromDateString($accessTokenTTL));
        return $this;
    }

    public function setRefreshTokenTTL(string $refreshTokenTTL): self
    {
        $this->passwordLessGrant->setRefreshTokenTTL(DateInterval::createFromDateString($refreshTokenTTL));
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
        $this->passwordLessGrant->setSingleLogin($singleLogin);
        return $this;
    }

    public function setLinkUrl(string $linkUrl): self
    {
        $this->linkUrl = $linkUrl;
        return $this;
    }

    public function setValidateRouteName(string $validateRouteName): self
    {
        $this->validateRouteName = $validateRouteName;
        return $this;
    }

    public function setLinkTTL(string $linkTTL): self
    {
        $this->linkTTL = DateInterval::createFromDateString($linkTTL);
        return $this;
    }

    public function setLinkMail(Closure $linkMail): self
    {
        $this->linkMail = $linkMail;
        return $this;
    }
}
