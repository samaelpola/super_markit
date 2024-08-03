<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $listUsers = [
            [
                'firstName' => 'john',
                'lastName' => 'doe',
                'isVerified' => true,
                'email' => 'john.doe@gmail.com',
                'role' => ['ROLE_ADMIN'],
                'password' => 'test-admin'
            ],
            [
                'firstName' => 'ghost',
                'lastName' => 'hack',
                'isVerified' => true,
                'email' => 'ghost@gmail.com',
                'role' => ['ROLE_USER'],
                'password' => 'test'
            ]
        ];

        foreach ($listUsers as $user) {
            $newUser = new User();
            $newUser->setEmail($user['email']);
            $newUser->setRoles($user['role']);
            $newUser->setFirstName($user['firstName']);
            $newUser->setLastName($user['lastName']);
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $user['password']));
            $newUser->setIsVerified($user['isVerified']);
            $manager->persist($newUser);
        }

        $manager->flush();
    }
}
