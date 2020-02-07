<?php

namespace AppBundle\Importer;

use AppBundle\Manager\TruckDayManager;

class TruckDayImporter
{
    private const PATH = 'import/';
    private const JSON_TRUCKS = 'camions.json';
    private const JSON_TRUCKDAYS = 'creneaux.json';

    /** @var array */
    private $truckDictionaryCapacity;

    /** @var int */
    private $importCount;

    /** @var TruckDayManager */
    private $truckDayManager;

    public function __construct(TruckDayManager $truckDayManager)
    {
        $this->importCount = 0;
        $this->truckDayManager = $truckDayManager;
    }

    public function getImportCount(): int
    {
        return $this->importCount;
    }

    /**
     * @throws \Exception
     * @throws \UnexpectedValueException
     */
    public function importJson(string $path = self::PATH, string $truckFile = self::JSON_TRUCKS, string $truckDayFile = self::JSON_TRUCKDAYS): void
    {
        $this->importCount = 0;

        $jsonTrucks = $this->parseJson($path.$truckFile);

        $this->parseTrucksDictionaryCapacity($jsonTrucks);

        $jsonTruckDays = $this->parseJson($path.$truckDayFile);

        $this->createTruckDayEntities($jsonTruckDays);
    }

    private function createTruckDayEntities(array $jsonTruckDays): void
    {
        if (empty($jsonTruckDays['postal_code'])) {
            throw new \UnexpectedValueException('TruckDay error : Empty postal_code in JSON');
        }

        if (empty($jsonTruckDays['slots'])) {
            return;
        }

        $codePostal = $jsonTruckDays['postal_code'];

        foreach ($jsonTruckDays['slots'] as $slot) {
            if (empty($slot['date']) || empty($slot['truck'])) {
                continue;
            }

            $date = $slot['date'];
            $dateTime = new \DateTime($date);
            $truck = $slot['truck'];
            $capacity = $this->getCapacityFromTruck($date, $truck);

            if (null === $capacity) {
                continue;
            }

            $this->truckDayManager->insertOrUpdateTruckDay($dateTime, $truck, $capacity, $codePostal);
            ++$this->importCount;
        }
    }

    private function getCapacityFromTruck(string $date, string $truck): ?int
    {
        return $this->truckDictionaryCapacity[$date.$truck] ?? null;
    }

    private function parseTrucksDictionaryCapacity(array $jsonTrucks): void
    {
        $this->truckDictionaryCapacity = [];

        if (empty($jsonTrucks['trucks'])) {
            return;
        }

        foreach ($jsonTrucks['trucks'] as $truck) {
            if (empty($truck['date']) || empty($truck['capacity']) || empty($truck['truck'])) {
                continue;
            }

            $key = $truck['date'].$truck['truck'];

            $this->truckDictionaryCapacity[$key] = (int) $truck['capacity'];
        }
    }

    /**
     * @throws \Exception
     * @throws \UnexpectedValueException
     */
    private function parseJson(string $url): array
    {
        try {
            $fileContent = file_get_contents($url);
        } catch (\Exception $exception) {
            $fileContent = false;
        }

        if (!$fileContent) {
            throw new \Exception('Fichier JSON '.$url.' impossible à lire.');
        }

        $json = json_decode($fileContent, true);

        if (empty($json)) {
            throw new \UnexpectedValueException('Fichier JSON '.$url.' invalide.');
        }

        return $json;
    }
}
