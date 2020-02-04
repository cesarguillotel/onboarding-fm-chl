<?php


namespace AppBundle\Service;

use AppBundle\Entity\Creneau;
use AppBundle\Repository\CreneauRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ImportService
{
    const PATH = 'import/';
    const JSON_CAMIONS = 'camions.json';
    const JSON_CRENEAUX = 'creneaux.json';

    /** @var array */
    private $truckDictionaryCapacity;

    /** @var int  */
    private $importCount;

    /** @var EntityManager  */
    private $entityManager;

    /** @var CreneauRepository  */
    private $creneauRepository;

    public function __construct(EntityManager $entityManager, CreneauRepository $creneauRepository)
    {
        $this->importCount = 0;
        $this->entityManager = $entityManager;
        $this->creneauRepository = $creneauRepository;
    }

    public function getImportCount(): int
    {
        return $this->importCount;
    }

    public function importJson() {

        $this->importCount = 0;

        $jsonCamions = $this->parseJson(self::PATH . self::JSON_CAMIONS);

        $this->parseTrucksDictionaryCapacity($jsonCamions);

        $jsonCreneaux = $this->parseJson(self::PATH . self::JSON_CRENEAUX);

        $this->createCreneauxEntities($jsonCreneaux);
    }

    private function createCreneauxEntities(array $jsonCreneaux) {

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

            if (null === $capacity) continue;

            $this->insertOrUpdateCreneau($dateTime, $truck, $capacity, $codePostal);
        }

        $this->entityManager->flush();
    }

    private function insertOrUpdateCreneau(\DateTime $dateTime, string $truck, int $capacity, string $postalCode) {

        $creneau = $this->creneauRepository->findOneBy(['date' => $dateTime, 'postalCode' => $postalCode]);

        if (null !== $creneau) {
            $creneau->setTruck($truck);
            $creneau->setCapacity($capacity);
        }
        else {
            $creneau = new Creneau();
            $creneau->setDate($dateTime);
            $creneau->setTruck($truck);
            $creneau->setCapacity($capacity);
            $creneau->setPostalCode($postalCode);
        }

        $this->entityManager->persist($creneau);
        $this->importCount++;
    }

    private function getCapacityFromTruck(string $date, string $truck) {

        $key = $date . $truck;
        return $this->truckDictionaryCapacity[$key] ?? null;
    }

    private function parseTrucksDictionaryCapacity(array $jsonCamions) {

        $this->truckDictionaryCapacity = [];

        if (empty($jsonCamions['trucks'])) {
            return;
        }

        foreach ($jsonCamions['trucks'] as $camion) {

            if (empty($camion['date']) || empty($camion['capacity']) || empty($camion['truck'])) {
                continue;
            }

            $key = $camion['date'] . $camion['truck'];

            $this->truckDictionaryCapacity[$key] = (int)$camion['capacity'];
        }
    }

    private function parseJson(string $url) {

        $fileContent = file_get_contents($url);

        if (!$fileContent) {
            throw new FileNotFoundException('Fichier JSON ' . $url . ' non trouvé.');
        }

        $json = json_decode($fileContent, true);

        if (false === $json) {
            throw new \UnexpectedValueException('Fichier JSON ' . $url . ' invalide.');
        }

        return $json;
    }
}