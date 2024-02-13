<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\OrderService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService  $orderService,
        private readonly StripeService $stripeService,
    )
    {
    }

    #[Route("/order", name: "app_order")]
    public function order(Request $request): Response
    {
        return $this->render("order/order.html.twig", [
            "orders" => $this->getUser()->getOrders(),
            'numberOfProduct' => array_sum($request->getSession()->get("shopping_basket", []))
        ]);
    }

    #[Route("/order/validate", name: "app_validate_shopping_basket")]
    public function validateShoppingBasket(Request $request): Response
    {
        $shoppingBasket = $request->getSession()->get("shopping_basket", []);

        if (count($shoppingBasket) === 0) {
            throw $this->createNotFoundException("shopping basket is empty");
        }

        try {
            $order = $this->orderService->createOrder($shoppingBasket, $this->getUser());
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash("error", $exception->getMessage());
            return $this->redirectToRoute("app_shopping_basket");
        }

        return $this->redirectToRoute("app_create_session_stripe", ["id" => $order->getId()]);
    }

    #[Route("/order/{id}/stripe_session", name: "app_create_session_stripe")]
    public function stripeCheckoutSession(Order $order): Response
    {
        return new RedirectResponse(
            $this->stripeService->createCheckoutSession($order)->url
        );
    }

    #[Route("/order/{id}/success", name: "app_payment_success")]
    public function paymentSuccess(Request $request, Order $order, #[MapQueryParameter] string $session_id): Response
    {
        $request->getSession()->remove("shopping_basket");
        $session = $this->stripeService->getCheckoutSession($session_id);

        $this->orderService->updateOrderAddressAndOrderStatus(
            $order,
            $session->shipping_details->toArray()["address"]
        );

        return $this->render("order/payment_success.html.twig");
    }

    #[Route("/order/{id}/cancel", name: "app_payment_failed")]
    public function paymentFailed(Order $order): Response
    {
        return $this->render("order/payment_cancel.html.twig", [
            "order" => $order
        ]);
    }

    #[Route("/order/{id}/detail", name: "app_order_detail")]
    public function orderDetail(Order $order, Request $request): Response
    {
        return $this->render("order/order_detail.html.twig", [
            "order" => $order,
            'numberOfProduct' => array_sum($request->getSession()->get("shopping_basket", []))
        ]);
    }
}
