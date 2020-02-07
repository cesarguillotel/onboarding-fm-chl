<?php

namespace Tests\Unit\AppBundle\Importer;

use AppBundle\Entity\Commande;
use AppBundle\Entity\TruckDay;
use AppBundle\Manager\CommandManager;
use AppBundle\Repository\TruckDayRepository;
use AppBundle\Service\CalendarService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class CommandManagerTest extends TestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var CalendarService */
    private $calendarService;

    /** @var TruckDayRepository */
    private $truckDayRepository;

    /** @var CommandManager */
    private $commandManager;

    public function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManager::class);
        $this->calendarService = $this->prophesize(CalendarService::class);
        $this->truckDayRepository = $this->prophesize(TruckDayRepository::class);

        $this->commandManager = new CommandManager($this->entityManager->reveal(), $this->truckDayRepository->reveal(), $this->calendarService->reveal());
    }

    public function testNewCommand(): void
    {
        $truckDayId = 1;
        $quantity = 1000;
        $truckDay = new TruckDay();
        $command = new Commande();
        $command->setQuantity($quantity);
        $command->setTruckDay($truckDay);
        $command->setDate(new \DateTime());
        $command->setStatus(CommandManager::STATUS_IN_PROGRESS);

        $this->calendarService->checkAvailability($truckDayId, $quantity)->willReturn(true);
        $this->truckDayRepository->find($truckDayId)->willReturn($truckDay);
        $this->entityManager->persist(Argument::type(Commande::class))->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $newCommand = $this->commandManager->newCommand($truckDayId, $quantity);

        self::assertInstanceOf(\DateTime::class, $newCommand->getDate());
        $command->setDate($newCommand->getDate());

        self::assertEquals($command, $newCommand);
    }

    public function testNewCommandNotAvailable(): void
    {
        $truckDayId = 1;
        $quantity = 100;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(CalendarService::ERROR_QUANTITY_MIN_MAX);

        $this->calendarService->checkAvailability($truckDayId, $quantity)->willReturn(CalendarService::ERROR_QUANTITY_MIN_MAX);

        $this->entityManager->persist(Argument::type(Commande::class))->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();

        $this->commandManager->newCommand($truckDayId, $quantity);
    }

    public function testDoneCommand(): void
    {
        $quantity = 1000;
        $truckDay = new TruckDay();
        $command = new Commande();
        $command->setQuantity($quantity);
        $command->setTruckDay($truckDay);
        $command->setDate(new \DateTime());
        $command->setStatus(CommandManager::STATUS_IN_PROGRESS);

        $this->entityManager->merge($command)->willReturn($command);
        $this->entityManager->persist($command)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->commandManager->doneCommand($command);

        self::assertSame(CommandManager::STATUS_DONE, $command->getStatus());
    }
}
