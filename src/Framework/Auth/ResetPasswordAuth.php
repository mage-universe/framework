<?php declare(strict_types=1);

namespace Mage\Framework\Auth;

use Closure;
use DateInterval;
use Mage\Framework\Auth\Grants\PasswordAuthGrant;
use Mage\Framework\Auth\Issuers\ForgotPasswordIssuer;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordAuth
{
    private DateInterval $linkTTL;
    private string $linkUrl;
    private string $resetRouteName;
    private Closure $linkMail;

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly PasswordAuthGrant $passwordAuthGrant
    ) {
        $this->passwordAuthGrant->setAccessTokenTTL(DateInterval::createFromDateString('1 hour'));
        $this->linkTTL = DateInterval::createFromDateString('1 hour');
        $this->linkUrl = '';
        $this->resetRouteName = '';
        $this->linkMail = fn (): null => null;
    }

    public function issueForgotPasswordTokenToEmail(array $request): Response
    {
        $request = $this->request->withParsedBody($request);
        $issuer = new ForgotPasswordIssuer($request, $this->passwordAuthGrant);
        return $issuer->email($this->linkUrl, $this->resetRouteName, $this->linkTTL, $this->linkMail);
    }

    public function setNewPassword(array $request): Response
    {
        $request = $this->request->withParsedBody($request);
        $issuer = new ForgotPasswordIssuer($request, $this->passwordAuthGrant);
        return $issuer->reset();
    }

    public function setLinkUrl(string $linkUrl): self
    {
        $this->linkUrl = $linkUrl;
        return $this;
    }

    public function setResetRouteName(string $resetRouteName): self
    {
        $this->resetRouteName = $resetRouteName;
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
