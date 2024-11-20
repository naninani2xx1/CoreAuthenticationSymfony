<?php

namespace App\ApiBundle\EventSubscribers;

use App\Entity\UserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

final class LexikJWTEventSubscriber implements EventSubscriberInterface
{
    private readonly SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' =>  [
                ['onAuthenticationSuccessEvent', 10],
                ['onCreateUserToken', 0]
            ],
            'lexik_jwt_authentication.on_jwt_created' =>  [
                ['onJWTCreatedEvent', 0]
            ]
        ];
    }

    public function onAuthenticationSuccessEvent(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
    
        $arrContent['@type'] = 'Authentication';
        $arrContent['@id'] = "/users/". $user->getId();
        $arrContent['user'] = json_decode($this->serializer->serialize($user, 'json', [
            AbstractNormalizer::GROUPS => ['user:auth:item']
        ]), true);
        $arrContent['token'] = $event->getData()['token'];
        $event->setData($arrContent);
    }

    public function onJWTCreatedEvent(JWTCreatedEvent $event): void
    {

    }

    public function onCreateUserToken(AuthenticationSuccessEvent $event)
    {

    }
}