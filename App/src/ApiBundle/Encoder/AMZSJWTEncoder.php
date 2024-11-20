<?php

namespace App\ApiBundle\Encoder;

use Lcobucci\JWT\UnencryptedToken;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\HeaderAwareJWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'core.jwt_authentication.encoder')]
class AMZSJWTEncoder implements JWTEncoderInterface, HeaderAwareJWTEncoderInterface
{
    public function __construct(
        private readonly JWSProviderInterface   $jwsProvider,
    )
    {

    }

    /**
     * @param array $data
     * @param array $header
     * @return string|UnencryptedToken
     * @throws JWTEncodeFailureException
     */
    public function encode(array $data, array $header = []): string|UnencryptedToken
    {
        try {
            $jws = $this->jwsProvider->create($data, $header);
        } catch (\InvalidArgumentException $e) {
            throw new JWTEncodeFailureException(JWTEncodeFailureException::INVALID_CONFIG, 'An error occurred while trying to encode the JWT token. Please verify your configuration (private key/passphrase)', $e, $data);
        }

        if (!$jws->isSigned()) {
            throw new JWTEncodeFailureException(JWTEncodeFailureException::UNSIGNED_TOKEN, 'Unable to create a signed JWT from the given configuration.', null, $data);
        }
        return $jws->getToken();
    }

    public function decode($token): array
    {
        try {
            $jws = $this->jwsProvider->load($token);

        } catch (\Exception $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid JWT Token', $e);
        }

        if ($jws->isInvalid()) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid JWT Token', null, $jws->getPayload());
        }

        if ($jws->isExpired()) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::EXPIRED_TOKEN, 'Expired JWT Token', null, $jws->getPayload());
        }

        if (!$jws->isVerified()) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::UNVERIFIED_TOKEN, 'Unable to verify the given JWT through the given configuration. If the "lexik_jwt_authentication.encoder" encryption options have been changed since your last authentication, please renew the token. If the problem persists, verify that the configured keys/passphrase are valid.', null, $jws->getPayload());
        }

        return $jws->getPayload();
    }
}