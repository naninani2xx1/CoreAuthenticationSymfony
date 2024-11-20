<?php

declare(strict_types=1);

namespace App\ApiBundle\Controller;

use App\ApiBundle\Traits\SecurityTrait;
use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{
    use SecurityTrait;

    private readonly RequestStack $requestStack;
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    public function logout(EntityManagerInterface $manager, JWTTokenManagerInterface $tokenManager): Response
    {
        $token = $this->getTokenFromHeader();
        $payload = $tokenManager->parse($token);

        $userToken = $manager->getRepository(UserToken::class)->findOneBy(['jti' => $payload['jti'], 'isValid' => true]);
        //remove token is here
        $userToken->setValid(false);
        $manager->flush();
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
