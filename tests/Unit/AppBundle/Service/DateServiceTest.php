<?php

namespace Tests\Unit\AppBundle\Importer;

use AppBundle\Service\DateService;
use PHPUnit\Framework\TestCase;

class DateServiceTest extends TestCase
{
    /** @var DateService */
    private $dateService;

    public function setUp()
    {
        $this->dateService = new DateService();
    }

    public function testJoursFeries(): void
    {
        $this->dateService->initJoursFeries(['2020'], true);

        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-01-01')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-04-13')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-05-01')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-05-08')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-05-21')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-06-01')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-07-14')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-08-15')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-11-01')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-11-11')));
        self::assertTrue($this->dateService->estFerie(new \DateTime('2020-12-25')));
    }
}
