<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReclamationRepository::class)
 */
class Reclamation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $card;

    /**
     * @ORM\ManyToOne(targetEntity=Personnel::class, inversedBy="reclamations")
     */
    private $agent;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reclamationdate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $bouquets = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     */
    private $issend;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCard(): ?string
    {
        return $this->card;
    }

    public function setCard(?string $card): self
    {
        $this->card = $card;

        return $this;
    }

    public function getAgent(): ?Personnel
    {
        return $this->agent;
    }

    public function setAgent(?Personnel $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReclamationdate(): ?\DateTimeInterface
    {
        return $this->reclamationdate;
    }

    public function setReclamationdate(?\DateTimeInterface $reclamationdate): self
    {
        $this->reclamationdate = $reclamationdate;

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

    public function getBouquets(): ?array
    {
        return $this->bouquets;
    }

    public function setBouquets(?array $bouquets): self
    {
        $this->bouquets = $bouquets;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isIssend(): ?bool
    {
        return $this->issend;
    }

    public function setIssend(bool $issend): self
    {
        $this->issend = $issend;

        return $this;
    }
}
