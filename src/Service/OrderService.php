<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\User;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private ProductRepository      $productRepository,
        private OrderRepository        $orderRepository,
        private EntityManagerInterface $entityManager,
        private AddressRepository      $addressRepository
    )
    {
    }

    public function createOrder(array $shoppingBasket, User $user): ?Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setOrderStatus(Order::STATUS_UNPAID_ORDER);
        $order->setCreatedAt(new \DateTimeImmutable("now"));

        foreach ($shoppingBasket as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            $orderDetails = new OrderDetails();
            $orderDetails->setProduct($product);
            $orderDetails->setQuantity($quantity);
            $order->addOrderDetail($orderDetails);
            $stock = $product->getStock() - $quantity;

            switch (true) {
                case $stock < 0 :
                    throw new \InvalidArgumentException("The {$product->getName()} product is out of stock");
                case $stock === 0 :
                    $product->setVisible(false);
                    $product->setStock($stock);
                    break;
                case $stock > 0 :
                    $product->setStock($stock);
                    break;
            }

            $this->productRepository->save($product);
        }

        $this->orderRepository->save($order, true);
        $this->entityManager->flush();

        return $order;
    }

    public function updateOrderAddressAndOrderStatus(Order $order, array $addressShipping): void
    {
        $address = $this->addressRepository->findOneBy([
            "city" => $addressShipping["city"],
            "line1" => $addressShipping["line1"],
            "line2" => $addressShipping["line2"],
            "zip" => $addressShipping["postal_code"]
        ]);

        if ($address === null) {
            $address = new Address();
            $address->setCity($addressShipping["city"]);
            $address->setZip($addressShipping["postal_code"]);
            $address->setLine1($addressShipping["line1"]);
            $address->setLine2($addressShipping["line2"]);
        }

        $order->setAddress($address);
        $order->setOrderStatus(Order::STATUS_PAID_ORDER);
        $this->orderRepository->save($order, true);
    }
}
