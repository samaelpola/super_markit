<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $street_number;

    #[ORM\Column(length: 255)]
    private string $street;

    #[ORM\Column(length: 255)]
    private string $city;

    #[ORM\Column]
    private int $zip;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreetNumber(): int
    {
        return $this->street_number;
    }

    public function setStreetNumber(int $street_number): static
    {
        $this->street_number = $street_number;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): int
    {
        return $this->zip;
    }

    public function setZip(int $zip): static
    {
        $this->zip = $zip;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
