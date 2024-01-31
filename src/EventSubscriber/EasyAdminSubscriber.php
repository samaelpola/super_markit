<?php

namespace App\EventSubscriber;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\FileManager;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FileManager                 $fileManager,
        private CategoryRepository          $categoryRepository,
        private ProductRepository           $productRepository,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => ["uploadImage"],
            BeforeEntityPersistedEvent::class => ["hashPassword"]
        ];
    }

    /**
     * @param AfterEntityPersistedEvent $event
     * @throws Exception
     */
    public function uploadImage(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Category) && !($entity instanceof Product)) {
            return;
        }

        /** @var UploadedFile $file */
        $file = $entity->getPictureFile();

        if ($file === null) {
            return;
        }

        if ($entity instanceof Category) {
            $filePath = "categories/{$entity->getId()}/{$file->getClientOriginalName()}";

            $this->fileManager->uploadFile(
                $filePath,
                $file->getRealPath()
            );

            $entity->setPicture($filePath);
            $this->categoryRepository->save($entity, true);
            return;
        }

        $filePath = "products/{$entity->getId()}/{$file->getClientOriginalName()}";

        $this->fileManager->uploadFile(
            $filePath,
            $file->getRealPath()
        );

        $entity->setPicture($filePath);
        $this->productRepository->save($entity, true);
    }

    public function hashPassword(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof User)) {
            return;
        }

        $password = $entity->getPassword();
        $entity->setPassword(
            $this->passwordHasher->hashPassword(
                $entity,
                $password
            )
        );
    }
}
