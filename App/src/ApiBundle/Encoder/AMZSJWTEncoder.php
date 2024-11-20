<?php

namespace App\ApiBundle\Encoder;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class AMZSJWTEncoder implements JWTEncoderInterface
{
    private readonly string $key;

    public function __construct(string $key = 'super_secret_key')
    {
        $this->key = $key;
    }
    public function encode(array $data)
    {
        // TODO: Implement encode() method.
    }

    public function decode($token)
    {
        // TODO: Implement decode() method.
    }
}