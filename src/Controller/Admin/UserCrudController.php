<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    private const ROLES = [
        'ROLE_ADMIN' => 'ROLE_ADMIN',
        'ROLE_CASHIER' => 'ROLE_CASHIER',
        'ROLE_ACCOUNTANT' => 'ROLE_ACCOUNTANT',
        'ROLE_USER' => 'ROLE_USER'
    ];

    public function __construct(
        private UserRepository    $userRepository,
        private AdminUrlGenerator $adminUrlGenerator
    )
    {
    }

    private function createShowUserAction(bool $showAll): Action
    {
        return Action::new('show ' . ($showAll ? 'all' : 'admin'))
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-eye')
            ->displayAsLink()
            ->createAsGlobalAction()
            ->linkToUrl(function () use ($showAll) {
                return $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::INDEX)
                    ->set('show_all_users', $showAll)
                    ->generateUrl();
            });
    }

    public function configureActions(Actions $actions): Actions
    {
        $showAllUser = $this->createShowUserAction(true);
        $showAdmin = $this->createShowUserAction(false);

        return $actions
            ->add(Crud::PAGE_INDEX, $showAllUser)
            ->add(Crud::PAGE_INDEX, $showAdmin)
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('last_name'),
            TextField::new('first_name'),
            EmailField::new('email'),
            TextField::new('password')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'Password',
                        'row_attr' => ['class' => 'col-md-6 col-xxl-5']
                    ],
                    'second_options' => [
                        'label' => 'Confirm password',
                        'row_attr' => ['class' => 'col-md-6 col-xxl-5']
                    ],
                ])
                ->onlyWhenCreating(),
            ChoiceField::new('roles')
                ->setChoices(self::ROLES)
                ->allowMultipleChoices()
                ->renderExpanded()
                ->renderAsBadges([
                    'ROLE_ADMIN' => 'success',
                    'ROLE_CASHIER' => 'primary',
                    'ROLE_ACCOUNTANT' => 'info'
                ]),
            BooleanField::new('isVerified')
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        if ($this->getContext()->getRequest()->query->get('show_all_users')) {
            return $this->userRepository->findAllQuery();
        }

        return $this->userRepository->filterByRolesQuery(self::ROLES);
    }
}
