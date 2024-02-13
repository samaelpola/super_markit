<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    )
    {
    }

    #[Route("/categories", name: "app_categories")]
    public function category(Request $request): Response
    {
        return $this->render("category/categories.html.twig", [
            "categories" => $this->categoryRepository->findAll(),
            'numberOfProduct' => array_sum($request->getSession()->get("shopping_basket", []))
        ]);
    }

    #[Route("/categories/{id}", name: "app_product_of_category")]
    public function showProductOfCategory(Category $category, Request $request): Response
    {
        return $this->render("category/product.html.twig", [
            "category" => $category,
            'numberOfProduct' => array_sum($request->getSession()->get("shopping_basket", []))
        ]);
    }
}
