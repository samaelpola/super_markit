<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route("/product/{id}", name: "app_view_product")]
    public function viewProduct(Product $product, Request $request): Response
    {
        if (!$product->isVisible()) {
            throw new AccessDeniedHttpException("This product is not visible");
        }

        return $this->render("product/index.html.twig", [
            "product" => $product,
            'numberOfProduct' => count($request->getSession()->get("shopping_basket", []))
        ]);
    }
}
