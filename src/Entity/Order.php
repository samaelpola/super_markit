<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    const STATUS_UNPAID_ORDER = 0;
    const STATUS_PAID_ORDER = 1;
    const STATUS_ORDER_IN_PROGRESS = 2;
    const STATUS_ORDER_EXPEDITED = 3;
    const STATUS_ORDER_RECEIVED = 4;

    const STATUS_LIST = [
        self::STATUS_UNPAID_ORDER => 'STATUS_UNPAID_ORDER',
        self::STATUS_PAID_ORDER => 'STATUS_PAID_ORDER',
        self::STATUS_ORDER_IN_PROGRESS => 'STATUS_ORDER_IN_PROGRESS',
        self::STATUS_ORDER_EXPEDITED => 'STATUS_ORDER_EXPEDITED',
        self::STATUS_ORDER_RECEIVED => 'STATUS_ORDER_RECEIVED'
    ];

    const BADGE_COLOR = [
        self::STATUS_UNPAID_ORDER => 'danger',
        self::STATUS_PAID_ORDER => 'info',
        self::STATUS_ORDER_IN_PROGRESS => 'warning',
        self::STATUS_ORDER_EXPEDITED => 'primary',
        self::STATUS_ORDER_RECEIVED => 'success'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private int $order_status;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderDetails::class, cascade: ["persist", "remove"])]
    private Collection $orderDetails;

    #[ORM\ManyToOne(cascade: ["persist"], inversedBy: 'order')]
    private ?Address $address = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getOrderStatus(): int
    {
        return $this->order_status;
    }

    public function getOrderStatusName(int $orderStatus): string
    {
        return self::STATUS_LIST[$orderStatus];
    }

    public function setOrderStatus(int $order_status): static
    {
        $this->order_status = $order_status;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): static
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getOrder() === $this) {
                $orderDetail->setOrder(null);
            }
        }

        return $this;
    }

    public function getAmount(): float
    {
        $amount = 0.0;

        foreach ($this->getOrderDetails() as $orderDetail) {
            $amount += (int)($orderDetail->getQuantity() * $orderDetail->getProduct()->getPrice());
        }

        return $amount;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }
}
