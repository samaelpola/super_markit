<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        if ($this->isGranted('ROLE_CASHIER')) {
            return $this->redirect($adminUrlGenerator->setController(ProductCrudController::class)->generateUrl());
        }

        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Super Markit');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->hideNullValues();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToUrl('Home', 'fa-solid fa-house-user', $this->generateUrl("app_home"));

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Users');
            yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        }

        if ($this->isGranted('ROLE_CASHIER')) {
            yield MenuItem::section('Article');
            yield MenuItem::linkToCrud('Categories', 'fas fa-tags', Category::class);
            yield MenuItem::linkToCrud('Product', 'fas fa-list', Product::class);
            yield MenuItem::linkToCrud('Order', 'fas fa-shopping-cart', Order::class);
        }
    }
}
