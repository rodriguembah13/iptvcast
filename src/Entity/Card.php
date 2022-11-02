<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numerocard;
    /**
     * @ORM\Column(type="float", length=255)
     */
    private $amount;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $bouquets = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    public function getNumerocard(): ?string
    {
        return $this->numerocard;
    }

    public function setNumerocard(string $numerocard): self
    {
        $this->numerocard = $numerocard;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBouquets(): ?array
    {
        return $this->bouquets;
    }

    public function setBouquets(?array $bouquets): self
    {
        $this->bouquets = $bouquets;

        return $this;
    }
}
