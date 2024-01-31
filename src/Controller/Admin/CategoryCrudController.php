<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategoryCrudController extends AbstractCrudController
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private FileManager           $fileManager
    )
    {
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_CASHIER')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name'),
            DateTimeField::new('created_at')
                ->hideOnForm(),
            DateTimeField::new('updated_at')
                ->hideOnForm(),
            ImageField::new('picture')
                ->formatValue(function ($value, $entity) {
                    return $this->router->generate(
                        "app_get_picture",
                        ["id" => $entity->getId(), "type" => "category"]
                    );
                })
                ->hideOnForm(),
            TextField::new('pictureFile', 'Upload')
                ->onlyOnForms()
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', false)
                ->setFormTypeOption('attr', ['accept' => 'image/*'])
                ->setFormTypeOption('constraints', [])
        ];
    }

    public function createEntity(string $entityFqcn): Category
    {
        $category = new Category();
        $category->setCreatedAt(new \DateTimeImmutable("now"));

        return $category;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $entityInstance
     * @throws \Exception
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $file = $entityInstance->getPictureFile();

        if ($file !== null) {
            if ($entityInstance->getPicture() !== null) {
                $this->fileManager->deleteFile($entityInstance->getPicture());
            }

            $filePath = "categories/{$entityInstance->getId()}/{$file->getClientOriginalName()}";
            $this->fileManager->uploadFile($filePath, $file->getRealPath());
            $entityInstance->setPicture($filePath);
        }

        $entityInstance->setUpdatedAt(new \DateTimeImmutable("now"));
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $entityInstance
     * @throws \Exception
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance->getPicture() !== null) {
            $this->fileManager->deleteFile($entityInstance->getPicture());
        }

        $entityManager->remove($entityInstance);
        $entityManager->flush();
    }
}
