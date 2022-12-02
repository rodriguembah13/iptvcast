<?php

namespace App\Entity;

use App\Repository\PersonnelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonnelRepository::class)
 */
class Personnel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $agence;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"})
     */
    private $compte;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;
    /**
     * @ORM\Column(type="float")
     */
    private $solde;
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $activate=false;

    /**
     * @ORM\OneToMany(targetEntity=RechargeWallet::class, mappedBy="personnel")
     */
    private $rechargeWallets;

    public function __construct()
    {
        $this->rechargeWallets = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActivate(): bool
    {
        return $this->activate;
    }

    /**
     * @param bool $activate
     */
    public function setActivate(bool $activate): void
    {
        $this->activate = $activate;
    }

    public function getCompte(): ?User
    {
        return $this->compte;
    }

    public function setCompte(?User $compte): self
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSolde()
    {
        return $this->solde;
    }

    /**
     * @param mixed $solde
     */
    public function setSolde($solde): void
    {
        $this->solde = $solde;
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

    /**
     * @return Collection<int, RechargeWallet>
     */
    public function getRechargeWallets(): Collection
    {
        return $this->rechargeWallets;
    }

    public function addRechargeWallet(RechargeWallet $rechargeWallet): self
    {
        if (!$this->rechargeWallets->contains($rechargeWallet)) {
            $this->rechargeWallets[] = $rechargeWallet;
            $rechargeWallet->setPersonnel($this);
        }

        return $this;
    }

    public function removeRechargeWallet(RechargeWallet $rechargeWallet): self
    {
        if ($this->rechargeWallets->removeElement($rechargeWallet)) {
            // set the owning side to null (unless already changed)
            if ($rechargeWallet->getPersonnel() === $this) {
                $rechargeWallet->setPersonnel(null);
            }
        }

        return $this;
    }
}
