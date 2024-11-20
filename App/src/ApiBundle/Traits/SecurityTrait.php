<?php

namespace App\ApiBundle\Traits;

use App\Entity\UserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\HttpFoundation\Request;

trait SecurityTrait
{
    function isCheckFirewallApi(): bool
    {
        return str_starts_with($this->requestStack->getCurrentRequest()->getPathInfo(), '/api');
    }

    function isCheckPathRefreshToken(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        return str_starts_with($request->getPathInfo(), '/api/v1/token') && $request->getMethod() == "GET";
    }

    /** Security trait support */
    function getTokenFromHeader(?Request $request = null): ?string
    {
        if ($request == null) {
            $request = $this->requestStack->getCurrentRequest();
        }
        $authorization = $request->headers->get('Authorization');
        return str_replace('Bearer ', '', $authorization);
    }

    /**
     * @throws JWTDecodeFailureException
     * @throws \Exception
     */
    function checkTokenInDB(string $token): void
    {
        if ($this->tokenManager == null) {
            throw new \Exception('Not found service jwt token manager in func');
        }
        $payload = $this->tokenManager->parse($token);

        $exp = $payload['exp'];
        $iat = $payload['iat'];
        try {
            $exp = new \DateTime(date('Y-m-d H:i:s', $exp));
            $iat = new \DateTime(date('Y-m-d H:i:s', $iat));

            $userToken = $this->manager->getRepository(UserToken::class)->findOneBy(['jti' => $payload['jti'], 'isValid' => true]);

            $signature = explode('.', $token)[2];
            if (empty($userToken) || $userToken->getToken() != $signature) {
                throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid JWT Token.', null, $payload);
            }

            if ($userToken->getExpiredAt() != $exp || $iat != $userToken->getIssueAt()) {
                throw new JWTDecodeFailureException(JWTDecodeFailureException::EXPIRED_TOKEN, 'Expired JWT Token', null, $payload);
            }
        } catch (\Exception) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid JWT Token..', null, $payload);
        }
    }
}