<?php

namespace AppBundle\Service;

use AppBundle\Entity\TruckDay;
use AppBundle\Manager\TruckDayManager;

class CalendarService
{
    public const DAYS_COUNT_AFTER_COMMAND = 2;
    public const DAYS_COUNT_CALENDAR = 6;
    public const QUANTITY_MIN = 500;
    public const QUANTITY_MAX = 10000;
    public const ERROR_QUANTITY_MIN_MAX = 'Veuillez saisir entre 500 et 10000 Litres';
    public const ERROR_UNAVAILABLE_QUANTITY = 'Créneau non disponible pour cette quantité.';
    public const ERROR_UNAVAILABLE_DAY = 'Date non disponible.';

    /** @var TruckDayManager */
    private $truckDayManager;

    /** @var DateService */
    private $dateService;

    public function __construct(TruckDayManager $truckDayManager, DateService $dateService)
    {
        $this->truckDayManager = $truckDayManager;
        $this->dateService = $dateService;
    }

    public function generate($postalCode, int $quantity, \DateTime $commandDate = null): array
    {
        if (null === $commandDate) {
            $commandDate = new \DateTime();
        }

        $workingDays = $this->getNextWorkingDays($commandDate);

        $calendar = [];

        foreach ($workingDays as $day) {

            /** @var TruckDay $truckDay */
            $truckDay = $this->truckDayManager->getTruckDayWithRestCapacity(null, $day, $postalCode, $quantity);

            if (null !== $truckDay) {
                $calendar[] = $truckDay->toArray();
            } else {
                $calendar[] = [
                    'date' => $day->format('Y-m-d'),
                ];
            }
        }

        return $calendar;
    }

    /**
     * @return true|string
     */
    public function checkAvailability(int $truckDayId, int $quantity, \DateTime $commandDate = null)
    {
        if (null === $commandDate) {
            $commandDate = new \DateTime();
        }

        if ($quantity < self::QUANTITY_MIN || $quantity > self::QUANTITY_MAX) {
            return self::ERROR_QUANTITY_MIN_MAX;
        }

        /** @var TruckDay $truckDay */
        $truckDay = $this->truckDayManager->getTruckDayWithRestCapacity($truckDayId, null, null, $quantity);

        if (null === $truckDay || $truckDay->getRestCapacity() < 0) {
            return self::ERROR_UNAVAILABLE_QUANTITY;
        }

        $workingDays = $this->getNextWorkingDays($commandDate);

        if (!\in_array($truckDay->getDate(), $workingDays, false)) {
            return self::ERROR_UNAVAILABLE_DAY;
        }

        return true;
    }

    private function getNextWorkingDays(\DateTime $commandDate): array
    {
        $days = [];
        $date = new \DateTime($commandDate->format('Y-m-d'));
        $date->modify('+'.(self::DAYS_COUNT_AFTER_COMMAND - 1).' day');

        $years = [$date->format('Y')];
        if ('12' === $date->format('m')) {
            $years[] = ((int) $years[0]) + 1;
        }

        $this->dateService->initJoursFeries($years);

        $i = 0;
        while ($i < self::DAYS_COUNT_CALENDAR) {
            $date->modify('+1 day');

            if ($this->dateService->estNonTravaille($date)) {
                continue;
            }

            $days[] = $date;
            $date = clone $date;
            ++$i;
        }

        return $days;
    }
}
