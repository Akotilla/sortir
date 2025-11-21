<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CreatesortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[Route('/sortie')]
final class SortieController extends AbstractController
{


    #[Route('/create', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(
        Request                $request,
        EntityManagerInterface $em,
        EtatRepository         $etatRepo
    ): Response
    {

        $user = $this->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }

        $sortie = new Sortie();
        $form = $this->createForm(CreatesortieType::class, $sortie);
        $form->handleRequest($request);


        if ($form->isSubmitted()) {

            if ($form->get('publish')->isClicked()) {
                $etat = $etatRepo->findByLibelle('Ouverte');
            } else {
                $etat = $etatRepo->findByLibelle('En création');
            }

            if (!$etat) {
                throw $this->createNotFoundException('Etat introuvable en base.');
            }

            $sortie->setEtat($etat);
            $sortie->setOrganisePar($user);
            $sortie->setSiteOrganisateur($user->getCampus());

            if ($form->isValid()) {
                $em->persist($sortie);
                $em->flush();

                if ($form->get('save')->isClicked()) {
                    $this->addFlash('success', 'Sortie enregistrée.');
                } else {
                    $this->addFlash('success', 'Sortie publiée !');
                }

                return $this->redirectToRoute('main_home');


            } else {
                $this->addFlash('danger', 'Le formulaire contient des erreurs, veuillez les corriger.');
            }
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $form,
            'campus' => $user->getCampus(),
        ]);
    }

    #[
        Route('/edit', name: 'sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Sortie $sortie)
    {
// todo: vérifier rôle usercréateur ou admin
    }

    #[Route('/inscription', name: 'sortie_inscription', methods: ['GET', 'POST'])]
    public function inscription()
    {
        // todo vérfier conecter et role actif

    }

    #[Route('/{id}', name: 'sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}
