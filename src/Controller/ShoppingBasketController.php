<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ShoppingBasketController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository
    )
    {
    }

    #[Route("/basket", name: "app_shopping_basket")]
    public function shoppingBasket(Request $request): Response
    {
        $shoppingBasket = $request->getSession()->get("shopping_basket", []);
        $total = 0;
        $basket = [];

        foreach ($shoppingBasket as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            $total += $quantity * $product->getPrice();
            $basket[] = [
                "product" => $product,
                "quantity" => $quantity
            ];
        }

        return $this->render("basket/index.html.twig", [
            "shoppingBasket" => $basket,
            "numberOfProduct" => array_sum($shoppingBasket),
            "total" => $total
        ]);
    }

    #[Route("/basket/{id}", name: "app_add_product_to_basket")]
    public function addProductToBasket(Product $product, Request $request, #[MapQueryParameter] int $quantity): Response
    {
        if (!$product->isVisible()) {
            throw new AccessDeniedHttpException("This product is not visible");
        }

        $shoppingBasket = $request->getSession()->get("shopping_basket", []);
        $productExist = false;

        foreach ($shoppingBasket as $productId => $productQuantity) {
            if ($productId === $product->getId()) {
                $productExist = true;
                $this->addFlash("success", "the product already exist in the basket");
                break;
            }
        }

        if (!$productExist) {
            $shoppingBasket[$product->getId()] = min($quantity, $product->getStock());
            $this->addFlash("success", "the product has been successfully added to the basket");
        }

        $request->getSession()->set("shopping_basket", $shoppingBasket);

        return $this->redirectToRoute("app_home");
    }

    #[Route("/basket/{id}/add_quantity", name: "app_add_product_quantity_to_basket")]
    public function addQuantity(Product $product, Request $request): Response
    {
        if (!$product->isVisible()) {
            throw new AccessDeniedHttpException("This product is not visible");
        }

        $shoppingBasket = $request->getSession()->get("shopping_basket", []);

        foreach ($shoppingBasket as $productId => &$quantity) {
            if ($productId === $product->getId()) {
                $quantity >= $product->getStock() ?: $quantity++;
                break;
            }
        }

        $request->getSession()->set("shopping_basket", $shoppingBasket);
        return $this->redirectToRoute("app_shopping_basket");
    }

    #[Route("/basket/{id}/reduce_quantity", name: "app_reduce_product_quantity_to_basket")]
    public function reduceQuantity(Product $product, Request $request): Response
    {
        if (!$product->isVisible()) {
            throw new AccessDeniedHttpException("This product is not visible");
        }

        $shoppingBasket = $request->getSession()->get("shopping_basket", []);

        foreach ($shoppingBasket as $productId => &$quantity) {
            if ($productId === $product->getId()) {
                if ($quantity <= 1) {
                    unset($shoppingBasket[$productId]);
                } else {
                    $quantity--;
                }

                break;
            }
        }

        $request->getSession()->set("shopping_basket", $shoppingBasket);
        return $this->redirectToRoute("app_shopping_basket");
    }

    #[Route("/basket/{id}/delete", name: "app_remove_product_to_basket")]
    public function removeProductToBasket(Product $product, Request $request): Response
    {
        if (!$product->isVisible()) {
            throw new AccessDeniedHttpException("This product is not visible");
        }

        $shoppingBasket = $request->getSession()->get("shopping_basket", []);

        foreach ($shoppingBasket as $productId => $quantity) {
            if ($productId === $product->getId()) {
                unset($shoppingBasket[$productId]);
                break;
            }
        }

        $request->getSession()->set("shopping_basket", $shoppingBasket);

        return $this->redirectToRoute("app_shopping_basket");
    }
}
