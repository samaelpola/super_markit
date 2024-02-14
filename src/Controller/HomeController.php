<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository
    )
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        return $this->render('home/index.html.twig', [
            'products' => $this->productRepository->getProductsVisible(),
            'numberOfProduct' => array_sum($request->getSession()->get("shopping_basket", []))
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function showProfile(Request $request): Response
    {
        return $this->render('home/profile.html.twig', [
            'numberOfProduct' => array_sum($request->getSession()->get("shopping_basket", []))
        ]);
    }
}
