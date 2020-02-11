<?php

namespace AppBundle\Manager;

use AppBundle\Entity\TruckDay;
use AppBundle\Repository\TruckDayRepository;
use Doctrine\ORM\EntityManager;

class TruckDayManager
{
    /** @var TruckDayRepository */
    private $truckDayRepository;

    /** @var EntityManager */
    private $entityManager;

    public function __construct(TruckDayRepository $truckDayRepository, EntityManager $entityManager)
    {
        $this->truckDayRepository = $truckDayRepository;
        $this->entityManager = $entityManager;
    }

    public function getTruckDayWithRestCapacity(
        ?int $id = null,
        ?\DateTime $date = null,
        ?string $postalCode = null,
        int $minRestCapacity = 0
    ): ?TruckDay {
        $result = null;

        if (null !== $id) {
            $result = $this->truckDayRepository->findByIdWithRestCapacity($id);
        } elseif (null !== $date && null !== $postalCode) {
            $result = $this->truckDayRepository->findWithRestCapacity($date, $postalCode);
        }

        if (!empty($result[0])) {
            /** @var TruckDay $truckDay */
            $truckDay = $result[0];

            $restCapacity = $result['restCapacity'] ?? $truckDay->getCapacity();
            $restCapacity -= $minRestCapacity;

            $truckDay->setRestCapacity($restCapacity);

            return $truckDay;
        }

        return null;
    }

    public function insertOrUpdateTruckDay(\DateTime $dateTime, string $truck, int $capacity, string $postalCode): ?TruckDay
    {
        /** @var TruckDay $truckDay */
        $truckDay = $this->truckDayRepository->findOneBy(['date' => $dateTime, 'postalCode' => $postalCode]);

        if (null !== $truckDay) {
            $truckDay->setTruck($truck);
            $truckDay->setCapacity($capacity);
        } else {
            $truckDay = new TruckDay();
            $truckDay->setDate($dateTime);
            $truckDay->setTruck($truck);
            $truckDay->setCapacity($capacity);
            $truckDay->setPostalCode($postalCode);
        }

        $this->entityManager->persist($truckDay);
        $this->entityManager->flush();

        return $truckDay;
    }
}
