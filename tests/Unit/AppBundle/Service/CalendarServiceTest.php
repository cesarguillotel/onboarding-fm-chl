<?php

namespace Tests\Unit\AppBundle\Importer;

use AppBundle\Entity\TruckDay;
use AppBundle\Manager\TruckDayManager;
use AppBundle\Service\CalendarService;
use AppBundle\Service\DateService;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class CalendarServiceTest extends TestCase
{
    /** @var TruckDayManager */
    private $truckDayManager;

    /** @var DateServiceTest */
    private $dateService;

    /** @var CalendarService */
    private $calendarService;

    public function setUp(): void
    {
        $this->truckDayManager = $this->prophesize(TruckDayManager::class);
        $this->dateService = $this->prophesize(DateService::class);

        $this->calendarService = new CalendarService($this->truckDayManager->reveal(), $this->dateService->reveal());
    }

    public function testGenerate(): void
    {
        $postalCode = '92150';
        $quantity = 1000;
        $commandDate = new \DateTime('2020/02/07');
        $truckDay = new TruckDay();
        $truckDay->setDate(new \DateTime('2020-02-09'));
        $truckDay->setCapacity(5000);
        $truckDay->setTruck('TRUCK');
        $truckDay->setPostalCode($postalCode);
        $truckDay->setRestCapacity(5000);

        $this->dateService->initJoursFeries(['2020'])->shouldBeCalled();
        $this->dateService->estNonTravaille(new \DateTime('2020-02-09'))->willReturn(false);
        $this->dateService->estNonTravaille(new \DateTime('2020-02-10'))->willReturn(false);
        $this->dateService->estNonTravaille(new \DateTime('2020-02-11'))->willReturn(true); // Test férié
        $this->dateService->estNonTravaille(new \DateTime('2020-02-12'))->willReturn(false);
        $this->dateService->estNonTravaille(new \DateTime('2020-02-13'))->willReturn(false);
        $this->dateService->estNonTravaille(new \DateTime('2020-02-14'))->willReturn(false);
        $this->dateService->estNonTravaille(new \DateTime('2020-02-15'))->willReturn(false);

        $this->truckDayManager->getTruckDayWithRestCapacity(null, new \DateTime('2020-02-09'), $postalCode, $quantity)->willReturn($truckDay);
        $this->truckDayManager->getTruckDayWithRestCapacity(null, new \DateTime('2020-02-10'), $postalCode, $quantity)->willReturn(null);
        $this->truckDayManager->getTruckDayWithRestCapacity(null, new \DateTime('2020-02-12'), $postalCode, $quantity)->willReturn(null);
        $this->truckDayManager->getTruckDayWithRestCapacity(null, new \DateTime('2020-02-13'), $postalCode, $quantity)->willReturn(null);
        $this->truckDayManager->getTruckDayWithRestCapacity(null, new \DateTime('2020-02-14'), $postalCode, $quantity)->willReturn(null);
        $this->truckDayManager->getTruckDayWithRestCapacity(null, new \DateTime('2020-02-15'), $postalCode, $quantity)->willReturn(null);

        $calendar = $this->calendarService->generate($postalCode, $quantity, $commandDate);

        self::assertSame([
            [
                'id' => null,
                'date' => '2020-02-09',
                'truck' => 'TRUCK',
                'capacity' => 5000,
                'postalCode' => '92150',
                'restCapacity' => 5000,
            ],
            ['date' => '2020-02-10'],
            ['date' => '2020-02-12'],
            ['date' => '2020-02-13'],
            ['date' => '2020-02-14'],
            ['date' => '2020-02-15'],
        ], $calendar);
    }

    public function testGenerateInDecember(): void
    {
        $commandDate = new \DateTime('2020/12/29');

        $this->dateService->initJoursFeries(['2020', '2021'])->shouldBeCalled(); // Assertion principale

        $this->dateService->estNonTravaille(Argument::type(\DateTime::class))->willReturn(false);
        $this->truckDayManager->getTruckDayWithRestCapacity(null, Argument::type(\DateTime::class), '92150', 1000)->shouldBeCalled()->willReturn(null);

        $this->calendarService->generate('92150', 1000, $commandDate);
    }

    public function testGenerateWithoutDate(): void
    {
        $this->dateService->initJoursFeries(Argument::type('array'))->shouldBeCalled();
        $this->dateService->estNonTravaille(Argument::type(\DateTime::class))->willReturn(false);
        $this->truckDayManager->getTruckDayWithRestCapacity(null, Argument::type(\DateTime::class), '92150', 1000)->shouldBeCalled()->willReturn(null);

        $this->calendarService->generate('92150', 1000);
    }

    public function testCheckAvailabilityOk(): void
    {
        $truckDayId = 1;
        $quantity = 1000;
        $truckDay = new TruckDay();
        $truckDay->setRestCapacity(1000);
        $truckDay->setDate(new \DateTime('2020/02/09'));
        $commandDate = new \DateTime('2020/02/07');

        $this->truckDayManager->getTruckDayWithRestCapacity($truckDayId, null, null, $quantity)->willReturn($truckDay);
        $this->dateService->initJoursFeries(['2020'])->shouldBeCalled();
        $this->dateService->estNonTravaille(Argument::type(\DateTime::class))->willReturn(false);

        $result = $this->calendarService->checkAvailability($truckDayId, $quantity, $commandDate);

        self::assertTrue($result);
    }

    public function testCheckAvailabilityInvalidQuanity(): void
    {
        $result = $this->calendarService->checkAvailability(1, CalendarService::QUANTITY_MAX + 1);

        self::assertSame(CalendarService::ERROR_QUANTITY_MIN_MAX, $result);
    }

    public function testCheckAvailabilityUnavailableQuanity(): void
    {
        $truckDayId = 1;
        $quantity = 1000;

        $this->truckDayManager->getTruckDayWithRestCapacity($truckDayId, null, null, $quantity)->willReturn(null);

        $result = $this->calendarService->checkAvailability($truckDayId, $quantity);

        self::assertSame(CalendarService::ERROR_UNAVAILABLE_QUANTITY, $result);
    }

    public function testCheckAvailabilityUnavailableDay(): void
    {
        $truckDayId = 1;
        $quantity = 1000;
        $truckDay = new TruckDay();
        $truckDay->setRestCapacity(1000);
        $truckDay->setDate(new \DateTime('1990/01/01'));
        $commandDate = new \DateTime('2020/02/07');

        $this->truckDayManager->getTruckDayWithRestCapacity($truckDayId, null, null, $quantity)->willReturn($truckDay);
        $this->dateService->initJoursFeries(['2020'])->shouldBeCalled();
        $this->dateService->estNonTravaille(Argument::type(\DateTime::class))->willReturn(false);

        $result = $this->calendarService->checkAvailability($truckDayId, $quantity, $commandDate);

        self::assertSame(CalendarService::ERROR_UNAVAILABLE_DAY, $result);
    }
}
