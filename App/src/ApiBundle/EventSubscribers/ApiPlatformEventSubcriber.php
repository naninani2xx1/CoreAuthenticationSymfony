<?php 

namespace App\ApiBundle\EventSubscribers;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ApiPlatform\State\LifecycleEventInterface;

class ApiPlatformEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'api.state.pre_write' => [
                ['onHashPassword', 10]
            ],
        ];
    }

    public function onHashPassword(LifecycleEventInterface $event)
    {

    }
}
