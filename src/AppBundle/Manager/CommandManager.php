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
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    /** @var EntityManager */
    private $entityManager;

    /** @var CalendarService */
    private $calendarService;

    /** @var TruckDayRepository */
    private $truckDayRepository;

    /** @var CommandeRepository */
    private $commandRepository;

    public function __construct(EntityManager $entityManager, TruckDayRepository $truckDayRepository, CommandeRepository $commandRepository, CalendarService $calendarService)
    {
        $this->entityManager = $entityManager;
        $this->calendarService = $calendarService;
        $this->truckDayRepository = $truckDayRepository;
        $this->commandRepository = $commandRepository;
    }

    /**
     * @throws \Exception
     */
    public function newCommand(int $truckDayId, int $quantity): Commande
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
        $command->setStatus(self::STATUS_IN_PROGRESS);

        $this->entityManager->persist($command);
        $this->entityManager->flush();

        return $command;
    }

    public function doneCommand(Commande $command): void
    {
        $command = $this->entityManager->merge($command);

        $command->setStatus(self::STATUS_DONE);

        $this->entityManager->persist($command);
        $this->entityManager->flush();
    }
}
