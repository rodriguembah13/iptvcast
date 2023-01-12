<?php

namespace App\Entity;

use App\Repository\CardSettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardSettingRepository::class)
 */
class CardSetting
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
    private $numero;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $commande;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $commandvalue;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSend;
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getCommande(): ?string
    {
        return $this->commande;
    }

    public function setCommande(?string $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getCommandvalue(): ?int
    {
        return $this->commandvalue;
    }

    public function setCommandvalue(?int $commandvalue): self
    {
        $this->commandvalue = $commandvalue;

        return $this;
    }

    public function isIsSend(): ?bool
    {
        return $this->isSend;
    }

    public function setIsSend(bool $isSend): self
    {
        $this->isSend = $isSend;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

}
