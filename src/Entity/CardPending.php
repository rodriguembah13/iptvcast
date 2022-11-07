<?php

namespace App\Entity;

use App\Repository\CardPendingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardPendingRepository::class)
 */
class CardPending
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cardid;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bouquet;

    /**
     * @ORM\Column(type="integer")
     */
    private $sendornot;

    /**
     * @ORM\Column(type="integer")
     */
    private $cardstatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiredtime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isdelete;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status=self::PENDING;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $activation;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCardid(): ?string
    {
        return $this->cardid;
    }

    public function setCardid(?string $cardid): self
    {
        $this->cardid = $cardid;

        return $this;
    }

    public function getSendornot(): ?int
    {
        return $this->sendornot;
    }

    public function setSendornot(int $sendornot): self
    {
        $this->sendornot = $sendornot;

        return $this;
    }

    public function getCardstatus(): ?int
    {
        return $this->cardstatus;
    }

    public function setCardstatus(int $cardstatus): self
    {
        $this->cardstatus = $cardstatus;

        return $this;
    }

    public function getExpiredtime(): ?\DateTimeInterface
    {
        return $this->expiredtime;
    }

    public function setExpiredtime(?\DateTimeInterface $expiredtime): self
    {
        $this->expiredtime = $expiredtime;

        return $this;
    }

    public function isIsdelete(): ?bool
    {
        return $this->isdelete;
    }

    public function setIsdelete(bool $isdelete): self
    {
        $this->isdelete = $isdelete;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBouquet()
    {
        return $this->bouquet;
    }

    /**
     * @param mixed $bouquet
     */
    public function setBouquet($bouquet): void
    {
        $this->bouquet = $bouquet;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getActivation(): ?int
    {
        return $this->activation;
    }

    public function setActivation(?int $activation): self
    {
        $this->activation = $activation;

        return $this;
    }

}
