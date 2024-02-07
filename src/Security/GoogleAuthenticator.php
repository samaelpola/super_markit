<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GoogleAuthenticator extends OAuth2Authenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly RouterInterface             $router,
        private readonly ClientRegistry              $clientRegistry,
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    private function getClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('google');
    }

    public function authenticate(Request $request): Passport
    {
        $credential = $this->fetchAccessToken($this->getClient());

        /** @var GoogleUser $resourceOwner */
        $resourceOwner = $this->getClient()->fetchUserFromToken($credential);

        if (!$resourceOwner->toArray()['email_verified']) {
            throw new AuthenticationException('Email not verified');
        }

        $user = $this->userRepository->findOneBy([
            'google_id' => $resourceOwner->getId(),
            'email' => $resourceOwner->getEmail()
        ]);

        if ($user === null) {
            $user = new User();
            $user->setFirstName($resourceOwner->getFirstName());
            $user->setLastName($resourceOwner->getFirstName());
            $user->setEmail($resourceOwner->getEmail());
            $user->setIsVerified(true);
            $user->setGoogleId($resourceOwner->getId());
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    md5("{$resourceOwner->getEmail()}{$resourceOwner->getId()}")
                )
            );
            $this->userRepository->save($user, true);
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), fn() => $user)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        if (
            $targetPath &&
            $targetPath !== $this->router->generate('app_login') &&
            $targetPath !== $this->router->generate('app_register')
        ) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse(
            $this->router->generate(
                count(
                    array_intersect(User::ROLES, $token->getRoleNames())
                ) > 0
                    ? "app_admin"
                    : "app_home"
            )
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse(
            $this->router->generate("app_login")
        );
    }
}
