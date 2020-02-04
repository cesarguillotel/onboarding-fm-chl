<?php

namespace AppBundle\Controller;

use AppBundle\Service\CalendrierService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ));
    }

    /**
     * @Route("/commande-fioul", name="commande")
     */
    public function commandeFioulAction(Request $request)
    {
        $method = $request->getMethod();
        $error = '';

        if (strtolower($method) === 'post') {
            $calendrierService = $this->container->get('app.calendrier_service');

            $post = $request->request->all();

            try {
                $creneau = $calendrierService->checkCommand($post['creneau'] ?? 0, $post['quantite'] ?? 0);

                $session = $this->get('session');
                $session->set('creneau', $creneau);
                $session->set('quantite', $post['quantite']);

                return $this->redirectToRoute('confirmation-commande-fioul');
            }
            catch (\Exception $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->render('commande-fioul.html.twig', ['error' => $error]);
    }

    /**
     * @Route("/confirmation-commande-fioul", name="confirmation-commande-fioul")
     */
    public function confirmationCommandeFioulAction(Request $request)
    {
        $method = $request->getMethod();
        $session = $this->get('session');
        $error = '';

        $creneau = $session->get('creneau');
        $quantite = $session->get('quantite');

        $calendrierService = $this->container->get('app.calendrier_service');

        if (strtolower($method) === 'post') {
            try {
                $calendrierService->commander($creneau ? $creneau->getId() : 0, $quantite);
                $session->getFlashBag()->add('success', 'Merci pour votre commande.');
                return $this->redirectToRoute('confirmation-commande-fioul');
            }
            catch (\Exception $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->render('confirmation-commande-fioul.html.twig',
            [
                'creneau' => $creneau,
                'quantite' => $quantite,
                'error' => $error,
            ]
        );
    }


    /**
     * @Route("/generer-calendrier/{postalCode}/{quantite}", name="generer-calendrier", defaults={"quantite":0})
     */
    public function genererCalendrierAction(string $postalCode, int $quantite)
    {
        $calendrierService = $this->container->get('app.calendrier_service');

        $calendrier = $calendrierService->generer($postalCode, $quantite);

        return new JsonResponse(['calendrier' => $calendrier]);
    }
}
