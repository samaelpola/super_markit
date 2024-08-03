<?php

namespace App\Tests\Controller;

use App\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'ghost@gmail.com']);
        $this->client->loginUser($user);
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testShowProfile(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'ghost@gmail.com']);
        $this->client->loginUser($user);
        $this->client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();
    }

    public function testUserAccessToFinanceDashboard(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'ghost@gmail.com']);
        $this->client->loginUser($user);
        $this->client->request('GET', '/admin/finance');
//        $this->client->catchExceptions(false);
//        $this->expectException(AccessDeniedHttpException::class);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
