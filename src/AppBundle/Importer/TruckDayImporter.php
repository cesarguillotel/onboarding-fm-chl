<?php

namespace AppBundle\Importer;

use AppBundle\Manager\TruckDayManager;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

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

    public function importJson(): void
    {
        $this->importCount = 0;

        $jsonCamions = $this->parseJson(self::PATH.self::JSON_TRUCKS);

        $this->parseTrucksDictionaryCapacity($jsonCamions);

        $jsonCreneaux = $this->parseJson(self::PATH.self::JSON_TRUCKDAYS);

        $this->createCreneauxEntities($jsonCreneaux);
    }

    private function createCreneauxEntities(array $jsonCreneaux): void
    {
        if (empty($jsonCreneaux['postal_code'])) {
            throw new \UnexpectedValueException('Creneaux error : Empty postal_code in JSON');
        }

        if (empty($jsonCreneaux['slots'])) {
            return;
        }

        $codePostal = $jsonCreneaux['postal_code'];

        foreach ($jsonCreneaux['slots'] as $slot) {
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

    private function parseTrucksDictionaryCapacity(array $jsonCamions): void
    {
        $this->truckDictionaryCapacity = [];

        if (empty($jsonCamions['trucks'])) {
            return;
        }

        foreach ($jsonCamions['trucks'] as $camion) {
            if (empty($camion['date']) || empty($camion['capacity']) || empty($camion['truck'])) {
                continue;
            }

            $key = $camion['date'].$camion['truck'];

            $this->truckDictionaryCapacity[$key] = (int) $camion['capacity'];
        }
    }

    private function parseJson(string $url): array
    {
        $fileContent = file_get_contents($url);

        if (!$fileContent) {
            throw new FileNotFoundException('Fichier JSON '.$url.' non trouvé.');
        }

        $json = json_decode($fileContent, true);

        if (false === $json) {
            throw new \UnexpectedValueException('Fichier JSON '.$url.' invalide.');
        }

        return $json;
    }
}
