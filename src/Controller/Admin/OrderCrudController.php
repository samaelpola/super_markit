<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CASHIER')]
class OrderCrudController extends AbstractCrudController
{
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_CASHIER')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/detail', 'admin/dashboard/order_detail.html.twig');
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('user'),
            CollectionField::new('orderDetails', 'products')
                ->hideOnIndex()
                ->setRequired(true)
                ->useEntryCrudForm()
                ->setFormTypeOption('by_reference', false),
            DateTimeField::new('created_at')
                ->hideOnForm(),
            MoneyField::new('amount', 'Amount')
                ->hideOnForm()
                ->setTextAlign('left')
                ->setStoredAsCents(false)
                ->setCurrency('EUR')
                ->setFormTypeOption('mapped', false),
            ChoiceField::new('order_status')
                ->setFormType(ChoiceType::class)
                ->setChoices(array_flip(Order::STATUS_LIST))
                ->renderAsBadges(Order::BADGE_COLOR)
        ];
    }

    public function createEntity(string $entityFqcn): Order
    {
        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable('now'));
        $order->setOrderStatus(Order::STATUS_UNPAID_ORDER);
        $order->setUser($this->getUser());

        return $order;
    }
}
