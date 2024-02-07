<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class CheckTypeOfLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack
    )
    {
    }

    public function onCheckPassportEvent(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if (
            $this->requestStack->getCurrentRequest()->get('_route') !== 'connect_google_check' &&
            $user->getGoogleId() !== null
        ) {
            throw new AuthenticationException("sign with google");
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassportEvent'],
        ];
    }
}
