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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;
    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     */
    private $amount;
    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $bouquets = [];
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created): void
    {
        $this->created = $created;
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

    /**
     * @return mixed
     */
    public function getAmount():?float
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
