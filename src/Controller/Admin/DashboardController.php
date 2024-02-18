<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly OrderRepository       $orderRepository
    )
    {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        if ($this->isGranted('ROLE_ACCOUNTANT')) {
            return $this->redirectToRoute("app_finance_dashboard");
        }

        return $this->redirect($adminUrlGenerator->setController(ProductCrudController::class)->generateUrl());
    }

    #[IsGranted("ROLE_ACCOUNTANT")]
    #[Route('/admin/finance', name: 'app_finance_dashboard')]
    public function finance(): Response
    {
        $orders = $this->orderRepository->getOrdersWhoseStatusIsNotUnpaid();
        $labels = [];
        $data = [];

        foreach ($orders as $order) {
            $labels[] = $order->getCreatedAt()->format('d-m-Y');
            $data[] = $order->getAmount();
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Finance',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $data,
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $this->render('admin/finance_dashboard/index.html.twig', [
            'chart' => $chart,
        ]);
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
        yield MenuItem::linkToUrl('Back to shop', 'fa-solid fa-house-user', $this->generateUrl("app_home"));

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

        if ($this->isGranted('ROLE_ACCOUNTANT')) {
            yield MenuItem::section('Finance');
            yield MenuItem::linkToUrl('Finance', 'fas fa-chart-line', $this->generateUrl("app_finance_dashboard"));
        }
    }
}
