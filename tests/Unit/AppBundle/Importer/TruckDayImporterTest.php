<?php

namespace Tests\Unit\AppBundle\Importer;

use AppBundle\Importer\TruckDayImporter;
use AppBundle\Manager\TruckDayManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class TruckDayImporterTest extends TestCase
{
    /** @var TruckDayManagerTest */
    private $truckDayManager;

    /** @var TruckDayImporter */
    private $truckDayImporter;

    public function setUp(): void
    {
        $this->truckDayManager = $this->prophesize(TruckDayManager::class);
        $this->truckDayImporter = new TruckDayImporter($this->truckDayManager->reveal());
    }

    public function testImportJson(): void
    {
        $truckDays = [
            [
                'date' => new \DateTime('2020-02-01'),
                'truck' => '4CV8F',
                'capacity' => 9000,
            ],
            [
                'date' => new \DateTime('2020-02-02'),
                'truck' => '32F0L',
                'capacity' => 4000,
            ],
            [
                'date' => new \DateTime('2020-02-03'),
                'truck' => 'QWX45',
                'capacity' => 2500,
            ],
            [
                'date' => new \DateTime('2020-02-04'),
                'truck' => 'TEST',
            ],
        ];

        $this->truckDayImporter->importJson(__DIR__.'../../../../Fixtures/', 'camions.json', 'creneaux.json');

        $this->truckDayManager->insertOrUpdateTruckDay($truckDays[0]['date'], $truckDays[0]['truck'], $truckDays[0]['capacity'], '92500')->shouldBeCalled();
        $this->truckDayManager->insertOrUpdateTruckDay($truckDays[1]['date'], $truckDays[1]['truck'], $truckDays[1]['capacity'], '92500')->shouldBeCalled();
        $this->truckDayManager->insertOrUpdateTruckDay($truckDays[2]['date'], $truckDays[2]['truck'], $truckDays[2]['capacity'], '92500')->shouldBeCalled();
        $this->truckDayManager->insertOrUpdateTruckDay($truckDays[3]['date'], $truckDays[3]['truck'], Argument::type('integer'), '92500')->shouldNotBeCalled();

        self::assertSame(3, $this->truckDayImporter->getImportCount());
    }

    public function testImportJsonEmpty(): void
    {
        $this->truckDayImporter->importJson(__DIR__.'../../../../Fixtures/', 'camionsEmptyTrucks.json', 'creneauxEmptySlots.json');

        self::assertSame(0, $this->truckDayImporter->getImportCount());
    }

    public function testJsonIllisible(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Fichier JSON invalid/path/test.json impossible à lire.');

        $this->truckDayImporter->importJson('invalid/path/', 'test.json', 'test.json');
    }

    public function testJsonImparsable(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Fichier JSON '.__DIR__.'../../../../Fixtures/invalid.json invalide.');

        $this->truckDayImporter->importJson(__DIR__.'../../../../Fixtures/', 'invalid.json', 'invalid.json');
    }

    public function testJsonEmptyPostalCode(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('TruckDay error : Empty postal_code in JSON');

        $this->truckDayImporter->importJson(__DIR__.'../../../../Fixtures/', 'camions.json', 'creneauxNoPostalCode.json');
    }
}
