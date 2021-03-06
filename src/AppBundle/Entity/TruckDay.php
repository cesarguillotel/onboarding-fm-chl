<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TruckDay.
 *
 * @ORM\Table(name="truckday")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TruckDayRepository")
 */
class TruckDay
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="truck", type="string", length=10)
     */
    private $truck;

    /**
     * @var int
     *
     * @ORM\Column(name="capacity", type="integer")
     */
    private $capacity;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=5)
     */
    private $postalCode;

    /**
     * @var int
     */
    private $restCapacity;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return TruckDay
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set truck.
     *
     * @param string $truck
     *
     * @return TruckDay
     */
    public function setTruck($truck)
    {
        $this->truck = $truck;

        return $this;
    }

    /**
     * Get truck.
     *
     * @return string
     */
    public function getTruck()
    {
        return $this->truck;
    }

    /**
     * Set capacity.
     *
     * @param int $capacity
     *
     * @return TruckDay
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity.
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return TruckDay
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getRestCapacity(): int
    {
        return $this->restCapacity;
    }

    public function setRestCapacity(int $restCapacity): void
    {
        $this->restCapacity = $restCapacity;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'date' => $this->getDate()->format('Y-m-d'),
            'truck' => $this->getTruck(),
            'capacity' => $this->getCapacity(),
            'postalCode' => $this->getPostalCode(),
            'restCapacity' => $this->getRestCapacity(),
        ];
    }
}
