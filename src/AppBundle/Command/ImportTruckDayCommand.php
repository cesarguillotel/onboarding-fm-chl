<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTruckDayCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:import-truckdays')

            ->setDescription('Importe les créneaux des camions de livraison.')

            ->setHelp('Charge un fichier creneaux.json et camions.json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Import des créneaux');

        $importService = $this->getContainer()->get('truckday_importer');

        $importService->importJson();

        $output->writeln($importService->getImportCount().' créneaux importés.');
    }
}
