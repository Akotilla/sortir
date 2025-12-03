<?php

namespace App\Controller;

use App\DTO\SortieFiltre;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


final class MainController extends AbstractController
{
    #[Route("/", name: "main_home", methods: ['GET', 'POST'])]
    public function home(
        Request          $request,
        SortieRepository $sortieRepository,
    ): Response
    {

        $user = $this->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $sortieFiltre = new SortieFiltre();
        $sortieFiltre->setSiteOrganisateur($user->getCampus());
        $sortieForm = $this->createForm(SortieType::class, $sortieFiltre);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortieFiltre = $sortieForm->getData();
            $sorties = $sortieRepository->findByFiltre($sortieFiltre, $user->getId());
        } else {
            $sorties = $sortieRepository->findByCampusWithRelations($user->getCampus()->getId(), $user->getId());
        }
        return $this->render('main/sortie.html.twig', [
            'sortieForm' => $sortieForm,
            'sorties' => $sorties,
        ]);
    }

}
