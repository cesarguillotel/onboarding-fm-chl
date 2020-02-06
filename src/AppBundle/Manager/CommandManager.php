<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Commande;
use AppBundle\Entity\TruckDay;
use Doctrine\ORM\EntityManager;

class CommandManager
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function insertCommand(TruckDay $truckDay, int $quantity): void
    {
        $command = new Commande();
        $command->setQuantity($quantity);
        $command->setTruckDay($truckDay);
        $command->setDate(new \DateTime());

        $this->entityManager->persist($command);
        $this->entityManager->flush();
    }
}
