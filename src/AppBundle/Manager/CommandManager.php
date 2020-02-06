<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Commande;
use AppBundle\Entity\TruckDay;
use AppBundle\Repository\CommandeRepository;
use AppBundle\Repository\TruckDayRepository;
use AppBundle\Service\CalendarService;
use Doctrine\ORM\EntityManager;

class CommandManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var CalendarService */
    private $calendarService;

    /** @var TruckDayRepository */
    private $truckDayRepository;

    public function __construct(EntityManager $entityManager, TruckDayRepository $truckDayRepository, CalendarService $calendarService)
    {
        $this->entityManager = $entityManager;
        $this->calendarService = $calendarService;
        $this->truckDayRepository = $truckDayRepository;
    }

    /**
     * @throws \Exception
     */
    public function createCommand(int $truckDayId, int $quantity): Commande
    {
        $availableOrError = $this->calendarService->checkAvailability($truckDayId, $quantity);

        if (true !== $availableOrError) {
            throw new \Exception($availableOrError);
        }

        /** @var TruckDay $truckDay */
        $truckDay = $this->truckDayRepository->find($truckDayId);

        $command = new Commande();
        $command->setQuantity($quantity);
        $command->setTruckDay($truckDay);
        $command->setDate(new \DateTime());

        return $command;
    }

    public function insertCommand(Commande $command): void
    {
        $command->setTruckDay($this->truckDayRepository->find($command->getTruckDay()->getId()));
        $this->entityManager->persist($command);
        $this->entityManager->flush();
    }
}
