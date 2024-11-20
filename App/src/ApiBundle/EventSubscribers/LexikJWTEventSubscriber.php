<?php

namespace App\ApiBundle\EventSubscribers;

use App\ApiBundle\Traits\SecurityTrait;
use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;

final class LexikJWTEventSubscriber implements EventSubscriberInterface
{
    use SecurityTrait;
    private readonly SerializerInterface $serializer;
    private readonly JWTTokenManagerInterface $tokenManager;
    private readonly EntityManagerInterface $manager;
    private readonly LoggerInterface $logger;
    private readonly RequestStack $requestStack;
    public function __construct(
        SerializerInterface $serializer, JWTTokenManagerInterface $tokenManager,
        EntityManagerInterface $manager, LoggerInterface $logger, RequestStack $requestStack
    )
    {
        $this->requestStack = $requestStack;
        $this->serializer = $serializer;
        $this->tokenManager = $tokenManager;
        $this->manager = $manager;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS =>  [
                ['onAuthenticationSuccessEvent', 10],
                ['onCreateUserToken', 0]
            ],
            Events::JWT_CREATED =>  [
                ['onJWTCreatedEvent', 0]
            ],
            Events::JWT_DECODED => [
                ['onJwtDecoded', 0]
            ]
        ];
    }

    /**
     * @throws JWTDecodeFailureException
     */
    public function onAuthenticationSuccessEvent(AuthenticationSuccessEvent $event): void
    {
//        $user = $event->getUser();
//        dd($event->getData());
//        $arrContent['@type'] = 'Authentication';
//        $arrContent['@id'] = "/users/". $user->getId();
//        $arrContent['user'] = json_decode($this->serializer->serialize($user, 'json', [
//            AbstractNormalizer::GROUPS => ['user:auth:item']
//        ]), true);
//        $arrContent['token'] = $event->getData()['token'];

//        $arrContent['refreshToken'] = $event->getData()['refreshToken'];
//        $event->setData($arrContent);
    }

    public function onJWTCreatedEvent(JWTCreatedEvent $event): void
    {

    }

    public function onCreateUserToken(AuthenticationSuccessEvent $event): void
    {
        $token = $event->getData()['token'];
        $payload = $this->tokenManager->parse($token);

        $exp = $payload['exp'];
        $iat = $payload['iat'];

        try{
            $exp = new \DateTime(date('Y-m-d H:i:s', $exp));
            $iat = new \DateTime(date('Y-m-d H:i:s', $iat));

            $userToken = new UserToken();
            $userToken->setJti($payload['jti']);
            $userToken->setToken(explode('.',$token)[2]);
            $userToken->setUsername($event->getUser()->getUserIdentifier());
            $userToken->setExpiredAt($exp);
            $userToken->setIssueAt($iat);
            $this->manager->persist($userToken);
            $this->manager->flush();
        }catch (\Exception $e){
            $this->logger->error('onCreateUserToken', [
                'transId' => Uuid::v4()->toRfc4122(),
                'message' => $e->getMessage()
            ]);
        }
    }

    public function onJwtDecoded(JWTDecodedEvent $event): void
    {

    }
}