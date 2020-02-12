<?php

namespace AppBundle\Controller;

use AppBundle\Manager\CommandManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/commande-fioul", name="commande", methods={"GET"})
     */
    public function commandeFioulAction(Request $request)
    {
        return $this->render('commande-fioul.html.twig', ['error' => '']);
    }

    /**
     * @Route("/commande-fioul", name="commandeValidate", methods={"POST"})
     */
    public function commandeFioulValidateAction(Request $request)
    {
        /** @var CommandManager $commandManager */
        $commandManager = $this->container->get('command_manager');

        $error = '';
        $post = $request->request->all();

        try {
            $command = $commandManager->newCommand($post['truckDayId'] ?? 0, $post['quantity'] ?? 0);

            $session = $this->get('session');
            $session->set('command', $command);

            return $this->redirectToRoute('confirmation-commande');
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->render('commande-fioul.html.twig', ['error' => $error]);
    }

    /**
     * @Route("/confirmation-commande-fioul", name="confirmation-commande", methods={"GET"})
     */
    public function confirmationCommandeAction(Request $request)
    {
        return $this->render('confirmation-commande-fioul.html.twig', ['error' => '']);
    }

    /**
     * @Route("/confirmation-commande-fioul", name="confirmation-commande-validate", methods={"POST"})
     */
    public function confirmationCommandeValidateAction(Request $request)
    {
        $error = '';
        $session = $this->get('session');
        $command = $session->get('command');

        /** @var CommandManager $commandManager */
        $commandManager = $this->container->get('command_manager');

        try {
            $commandManager->doneCommand($command);
            $session->getFlashBag()->add('success', 'Merci pour votre commande.');

            return $this->redirectToRoute('confirmation-commande');
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->render('confirmation-commande-fioul.html.twig', ['error' => $error]);
    }

    /**
     * @Route("/annuler-commande-fioul", name="cancel-command", methods={"GET"})
     */
    public function cancelCommandAction(Request $request)
    {
        $session = $this->get('session');
        $command = $session->get('command');

        /** @var CommandManager $commandManager */
        $commandManager = $this->container->get('command_manager');

        try {
            $commandManager->removeCommand($command);
            $session->getFlashBag()->add('success', 'Merci pour votre commande.');

            return $this->redirectToRoute('confirmation-commande');
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }

        return $this->render('confirmation-commande-fioul.html.twig', ['error' => $error]);
    }

    /**
     * @Route("/generate-calendar/{postalCode}/{quantity}", name="generate-calendar", defaults={"quantity":0})
     */
    public function generateCalendarAction(string $postalCode, int $quantity)
    {
        $calendarService = $this->container->get('calendar_service');

        $calendar = $calendarService->generate($postalCode, $quantity);

        return new JsonResponse(['calendar' => $calendar]);
    }
}
