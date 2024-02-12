<?php

namespace App\Service;

use App\Entity\Order;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private StripeClient $stripeClient;

    public function __construct(
        private string                $stripeSecretKey,
        private UrlGeneratorInterface $urlGenerator
    )
    {
        $this->stripeClient = new StripeClient($this->stripeSecretKey);
    }

    /**
     * @param Order $order
     * @return Session
     * @throws ApiErrorException
     */
    public function createCheckoutSession(Order $order): Session
    {
        $orderDetails = $order->getOrderDetails();
        $stripeItems = [];

        foreach ($orderDetails as $orderDetail) {
            $stripeItems[] = [
                'price' => $this->stripeClient->prices->create([
                    'currency' => 'EUR',
                    'unit_amount' => $orderDetail->getProduct()->getPrice() * 100,
                    'product_data' => [
                        'name' => $orderDetail->getProduct()->getName()
                    ]
                ]),
                'quantity' => $orderDetail->getQuantity()
            ];
        }

        return $this->stripeClient->checkout->sessions->create([
            'mode' => 'payment',
            'expires_at' => time() + (60 * 30),
            'payment_method_types' => ['card'],
            'customer_email' => $order->getUser()->getEmail(),
            'line_items' => [
                $stripeItems
            ],
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['FR']
            ],
            'success_url' => urldecode($this->urlGenerator->generate(
                "app_payment_success",
                [
                    "id" => $order->getId(),
                    "session_id" => "{CHECKOUT_SESSION_ID}"
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )),
            'cancel_url' => $this->urlGenerator->generate(
                "app_payment_failed",
                ["id" => $order->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ]);
    }

    public function getCheckoutSession(string $sessionId): Session
    {
        return $this->stripeClient->checkout->sessions->retrieve($sessionId);
    }
}
