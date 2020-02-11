<?php

namespace AppBundle\Command;

use AppBundle\Importer\TruckDayImporter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTruckDayCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:import-truckdays')
            ->setDescription('Importe les créneaux des camions de livraison.')
            ->setHelp('Charge un fichier JSON des créneaux et des camions.')
            ->addOption('truckdays', null, InputOption::VALUE_OPTIONAL, "Truckdays JSON file name")
            ->addOption('trucks', null, InputOption::VALUE_OPTIONAL, "Truck JSON file name")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Import des créneaux');

        /** @var TruckDayImporter $truckdayImporter */
        $truckdayImporter = $this->getContainer()->get('truckday_importer');

        $truckdayImporter->importJson($input->getOption('trucks') ?? TruckDayImporter::JSON_TRUCKS, $input->getOption('truckdays') ?? TruckDayImporter::JSON_TRUCKDAYS);

        $output->writeln($truckdayImporter->getImportCount().' créneaux importés.');
    }
}
