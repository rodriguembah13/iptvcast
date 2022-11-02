<?php

namespace App\Entity;

use App\Repository\ActivationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActivationRepository::class)
 */
class Activation
{
    public const PENDING="PENDING";
    public const ECHEC="ECHEC";
    public const SUCCESS="SUCCESS";
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class)
     */
    private $card;

    /**
     * @ORM\Column(type="integer")
     */
    private $monthto;

    /**
     * @ORM\ManyToOne(targetEntity=Personnel::class)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status=self::PENDING;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reference;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): self
    {
        $this->card = $card;

        return $this;
    }

    public function getMonthto(): ?int
    {
        return $this->monthto;
    }

    public function setMonthto(int $monthto): self
    {
        $this->monthto = $monthto;

        return $this;
    }

    public function getCreatedBy(): ?Personnel
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Personnel $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }
}
