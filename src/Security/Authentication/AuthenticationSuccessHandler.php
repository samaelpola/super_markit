<?php

namespace App\Security\Authentication;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly RouterInterface      $router,
        private readonly FirewallMapInterface $firewallMap
    )
    {
    }

    private function getFirewallName(Request $request): ?string
    {
        $firewallConfig = $this->firewallMap->getFirewallConfig($request);

        return $firewallConfig?->getName();
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $targetPath = $this->getTargetPath(
            $request->getSession(),
            $this->getFirewallName($request)
        );

        if (
            $targetPath &&
            $targetPath !== $this->router->generate('app_login') &&
            $targetPath !== $this->router->generate('app_register')
        ) {
            var_dump($targetPath);
            return new RedirectResponse($targetPath);
        }
        var_dump(count(
            array_intersect(User::ROLES, $token->getRoleNames())
        ));
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
}
