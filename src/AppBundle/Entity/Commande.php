<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Commande.
 *
 * @ORM\Table(name="commande")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommandeRepository")
 */
class Commande
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var TruckDay
     *
     * @ORM\ManyToOne(targetEntity="TruckDay", inversedBy="commandes")
     * @ORM\JoinColumn(name="truckday_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $truckDay;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     */
    private $status;

    public function getId(): int
    {
        return $this->id;
    }

    public function setQuantity(int $quantity): Commande
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTruckDay(): TruckDay
    {
        return $this->truckDay;
    }

    public function setTruckDay(TruckDay $truckDay): void
    {
        $this->truckDay = $truckDay;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
