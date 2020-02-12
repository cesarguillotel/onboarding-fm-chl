<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    /** @var Session */
    private $session;

    /** @var Kernel */
    private $kernel;

    /** @var Application  */
    private $application;

    /** @var string */
    private $commandOutput;

    /** @var EntityManager */
    private $entityManager;

    /** @var Registry */
    private $doctrine;

    /** @var array */
    private $cleanEntities;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct(Session $session, Kernel $kernel, EntityManager $entityManager, Registry $doctrine)
    {
        $this->session = $session;
        $this->kernel = $kernel;
        $this->application = new Application($kernel);
        $this->entityManager = $entityManager;
        $this->doctrine = $doctrine;
    }

    private function addEntitiesToClean(array $entities)
    {
        $this->cleanEntities = array_merge($this->cleanEntities, $entities);
    }

    /**
     * @BeforeScenario
     */
    public function initCleaning()
    {
        $this->cleanEntities = [];
    }

    /**
     * @AfterScenario
     */
    public function doCleaning()
    {
        foreach ($this->cleanEntities as $entity) {

            $entity = $this->entityManager->merge($entity);
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }

    /**
     * @Given /^there is a "([^"]*)" file into the "([^"]*)" folder wich contains :$/
     */
    public function thereIsAFileIntoTheFolderWichContains(string $fileName, string $path, PyStringNode $string)
    {
        file_put_contents($path . $fileName, $string);
    }

    /**
     * @When /^I execute the command "([^"]*)" with options:$/
     */
    public function iExecuteTheCommand(string $commandName, TableNode $tableNode)
    {
        $commandList = [
            'app:import-truckdays' => AppBundle\Command\ImportTruckDayCommand::class,
        ];

        $this->application->add(new $commandList[$commandName]);

        $command = $this->application->find($commandName);
        $options = $tableNode->getRowsHash();
        $options['command'] = $command->getName();

        $commandTester = new CommandTester($command);
        $commandTester->execute($options);
        $this->commandOutput = $commandTester->getDisplay();
    }

    /**
     * @Then /^the output should contains "([^"]*)"$/
     */
    public function theOutputShouldContains($arg1)
    {
        \PHPUnit\Framework\TestCase::assertContains($arg1, $this->commandOutput);
    }

    /**
     * @Then /^I shoud have in the database "([^"]*)" entity "([^"]*)" with values(, and clean it)*:$/
     */
    public function iShoudHaveInTheDatabaseEntityWithValuesAndCleanIt(int $count, string $entity, string $clean = '', TableNode $tableNode = null)
    {
        $repository = $this->doctrine->getRepository($entity);

        $search = $tableNode->getColumnsHash()[0];

        if (isset($search['date'])) {
            $search['date'] = new \DateTime($search['date']);
        }

        $result = $repository->findBy($search);

        if ($clean) $this->addEntitiesToClean($result);

        \PHPUnit\Framework\TestCase::assertCount($count, $result);
    }

    /**
     * @Then /^I wait (\d+) seconds$/
     */
    public function iWaitSeconds(int $seconds)
    {
        $this->getSession()->wait($seconds * 1000);
    }

    /**
     * @Given /^I have in the database entities "([^"]*)" with values:$/
     */
    public function iHaveInTheDatabaseEntitiesWithValues(string $entityName, TableNode $tableNode)
    {
        $valuesList = $tableNode->getColumnsHash();

        foreach ($valuesList as $values) {

            $entity = new $entityName();

            foreach ($values as $key => $value) {

                if ('date' === $key) {
                    $value = new \DateTime($value);
                }

                $setter = 'set' . ucfirst($key);
                $entity->$setter($value);
            }

            $this->entityManager->persist($entity);
            $this->addEntitiesToClean([$entity]);
        }
        $this->entityManager->flush();
    }

    /**
     * @When /^I select "([^"]*)" element$/
     */
    public function iSelectElement(string $element)
    {
        $page = $this->getSession()->getPage();
        $node = $page->find('css', $element);
        $node->click();
    }
}
