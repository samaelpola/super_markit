<?php

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use App\Security\Authentication\GoogleAuthenticator;
use App\Service\FileManager;
use Aws\S3\S3Client;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends WebTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    public ContainerInterface|Container $container;
    public KernelBrowser $client;
    public S3Client $s3Client;
    public string $testFile;
    public GoogleAuthenticator $googleAuthenticator;
    public FileManager $fileManager;
    public UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->container = static::getContainer();
        $this->databaseTool = $this->container->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadFixtures([UserFixtures::class]);

        /** @var GoogleAuthenticator $googleAuthenticator */
        $this->googleAuthenticator = $this->container->get(GoogleAuthenticator::class);
        /** @var S3Client $s3Client */
        $this->s3Client = $this->container->get(S3Client::class);
        /** @var FileManager $fileManager */
        $this->fileManager = $this->container->get(FileManager::class);
        /** @var UserRepository $userRepository */
        $this->userRepository = $this->container->get(UserRepository::class);
    }
}
