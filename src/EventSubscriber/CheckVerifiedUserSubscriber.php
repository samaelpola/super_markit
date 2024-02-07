<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Security\Authentication\AccountNotVerifiedAuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router
    )
    {
    }

    /**
     * @param CheckPassportEvent $event
     * @throws \Exception
     */
    public function onCheckPassportEvent(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if (!$user->IsVerified()) {
            throw new AccountNotVerifiedAuthenticationException();
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        if (!$event->getException() instanceof AccountNotVerifiedAuthenticationException) {
            return;
        }

        $request = $event->getRequest();
        $email = $event->getPassport()->getUser()->getEmail();
        $request->getSession()->set('email_not_verified', $email);

        $event->setResponse(
            new RedirectResponse(
                $this->router->generate('app_verify_resend_email')
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassportEvent', -10],
            LoginFailureEvent::class => 'onLoginFailure'
        ];
    }
}
