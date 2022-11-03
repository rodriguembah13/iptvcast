<?php

namespace App\Entity;

use App\Repository\BouquetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BouquetRepository::class)
 */
class Bouquet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bouquetid;
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price=0.0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numero;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $smsid;

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBouquetid()
    {
        return $this->bouquetid;
    }

    /**
     * @param mixed $bouquetid
     */
    public function setBouquetid($bouquetid): void
    {
        $this->bouquetid = $bouquetid;
    }

    /**
     * @return mixed
     */
    public function getSmsid()
    {
        return $this->smsid;
    }

    /**
     * @param mixed $smsid
     */
    public function setSmsid($smsid): void
    {
        $this->smsid = $smsid;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero): void
    {
        $this->numero = $numero;
    }
}
