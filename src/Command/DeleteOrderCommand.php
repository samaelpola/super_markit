<?php

namespace App\Command;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-order',
    description: 'delete unpaid orders older than 7 days',
)]
class DeleteOrderCommand extends Command
{
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private int $retentionDay;

    public function __construct(OrderRepository $orderRepository, ProductRepository $productRepository, int $retentionDay)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->retentionDay = $retentionDay;
    }

    protected function configure(): void
    {
        $this->setDescription('delete unpaid orders older than 7 days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $orders = $this->orderRepository->findBy([
            "order_status" => Order::STATUS_UNPAID_ORDER
        ]);

        if (count($orders) === 0) {
            $io->success("No orders with status UNPAID");
        }

        foreach ($orders as $order) {
            if ($order->getCreatedAt()->diff(new \DateTimeImmutable('now'))->days === $this->retentionDay) {
                $orderId = $order->getId();

                foreach ($order->getOrderDetails() as $orderDetail) {
                    $product = $orderDetail->getProduct();
                    $product->setStock($product->getStock() + $orderDetail->getQuantity());
                    $this->productRepository->save($product, true);
                }

                $this->orderRepository->remove($order, true);
                $io->success("order [{$orderId}] has been successfully remove");
            }
        }

        return Command::SUCCESS;
    }
}
