<?php

namespace Tests\Unit\AppBundle\Importer;

use AppBundle\Entity\TruckDay;
use AppBundle\Manager\TruckDayManager;
use AppBundle\Repository\TruckDayRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class TruckDayManagerTest extends TestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var TruckDayRepository */
    private $truckDayRepository;

    /** @var TruckDayManager */
    private $truckDayManager;

    public function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManager::class);
        $this->truckDayRepository = $this->prophesize(TruckDayRepository::class);

        $this->truckDayManager = new TruckDayManager($this->truckDayRepository->reveal(), $this->entityManager->reveal());
    }

    public function testGetTruckDayWithRestCapacityById(): void
    {
        $id = 1;
        $capacity = 1000;
        $restCapacity = 900;
        $minRestCapacity = 400;
        $truckDay = new TruckDay();
        $truckDay->setCapacity($capacity);

        $this->truckDayRepository->findByIdWithRestCapacity($id)->willReturn([
            0 => $truckDay,
            'restCapacity' => $restCapacity
        ]);

        $truckDayWithRestCapacity = $this->truckDayManager->getTruckDayWithRestCapacity($id, null, null, $minRestCapacity);

        self::assertSame($restCapacity - $minRestCapacity, $truckDayWithRestCapacity->getRestCapacity());
    }

    public function testGetTruckDayWithRestCapacityByIdWithoutCommands(): void
    {
        $id = 1;
        $capacity = 1000;
        $minRestCapacity = 400;
        $truckDay = new TruckDay();
        $truckDay->setCapacity($capacity);

        $this->truckDayRepository->findByIdWithRestCapacity($id)->willReturn([
            0 => $truckDay,
            'restCapacity' => null
        ]);

        $truckDayWithRestCapacity = $this->truckDayManager->getTruckDayWithRestCapacity($id, null, null, $minRestCapacity);

        self::assertSame($capacity - $minRestCapacity, $truckDayWithRestCapacity->getRestCapacity());
    }

    public function testGetTruckDayWithRestCapacityByDateAndPostalCode(): void
    {
        $id = 1;
        $date = new \DateTime();
        $postalCode = '92150';
        $capacity = 1000;
        $restCapacity = 900;
        $minRestCapacity = 400;
        $truckDay = new TruckDay();
        $truckDay->setCapacity($capacity);

        $this->truckDayRepository->findWithRestCapacity($date, $postalCode)->willReturn([
            0 => $truckDay,
            'restCapacity' => $restCapacity
        ]);

        $truckDayWithRestCapacity = $this->truckDayManager->getTruckDayWithRestCapacity(null, $date, $postalCode, $minRestCapacity);

        self::assertSame($restCapacity - $minRestCapacity, $truckDayWithRestCapacity->getRestCapacity());
        $truckDay->setRestCapacity($truckDayWithRestCapacity->getRestCapacity());

        self::assertEquals($truckDay, $truckDayWithRestCapacity);
    }

    public function testGetTruckDayWithRestCapacityNoResult(): void
    {
        $id = 1;
        $minRestCapacity = 400;

        $this->truckDayRepository->findByIdWithRestCapacity($id)->willReturn([]);

        $truckDayWithRestCapacity = $this->truckDayManager->getTruckDayWithRestCapacity($id, null, null, $minRestCapacity);

        self::assertNull($truckDayWithRestCapacity);
    }

    public function testInsertTruckDay(): void
    {
        $date = new \DateTime();
        $postalCode = '92150';
        $capacity = 1000;
        $truck = 'TRUCK';

        $truckDay = new TruckDay();
        $truckDay->setDate($date);
        $truckDay->setTruck($truck);
        $truckDay->setCapacity($capacity);
        $truckDay->setPostalCode($postalCode);

        $this->truckDayRepository->findOneBy(['date' => $date, 'postalCode' => $postalCode])->willReturn(null);

        $truckDayNew = $this->truckDayManager->insertOrUpdateTruckDay($date, $truck, $capacity, $postalCode);

        $this->entityManager->persist(Argument::type(TruckDay::class))->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        self::assertEquals($truckDay, $truckDayNew);
    }

    public function testUpdateTruckDay(): void
    {
        $date = new \DateTime();
        $postalCode = '92150';
        $capacity = 1000;
        $capacityUpdated = 2000;
        $truck = 'TRUCK';
        $truckUpdated = 'TRUCK_UPDATED';

        $truckDay = new TruckDay();
        $truckDay->setDate($date);
        $truckDay->setTruck($truck);
        $truckDay->setCapacity($capacity);
        $truckDay->setPostalCode($postalCode);

        $truckDayUpdated = new TruckDay();
        $truckDayUpdated->setDate($date);
        $truckDayUpdated->setTruck($truckUpdated);
        $truckDayUpdated->setCapacity($capacityUpdated);
        $truckDayUpdated->setPostalCode($postalCode);

        $this->truckDayRepository->findOneBy(['date' => $date, 'postalCode' => $postalCode])->willReturn($truckDay);

        $truckDayNew = $this->truckDayManager->insertOrUpdateTruckDay($date, $truckUpdated, $capacityUpdated, $postalCode);

        $this->entityManager->persist(Argument::type(TruckDay::class))->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        self::assertEquals($truckDayUpdated, $truckDayNew);
    }
}
