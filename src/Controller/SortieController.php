<?php

namespace App\Controller;


use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Form\CreatesortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
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
        EtatRepository         $etatRepo,
        LieuRepository         $lieuRepo,
        VilleRepository        $villeRepo,
    ): Response
    {

        $user = $this->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }

        $villes = $villeRepo->findAll();
        $lieux = $lieuRepo->findAll();
        $sortie = new Sortie();
        $form = $this->createForm(CreatesortieType::class, $sortie, [
            'is_edit' => false]);
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
            'villes' => $villes,
            'lieux' => $lieux,
        ]);
    }

    #[Route('/edit/{id}', name: 'sortie_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Sortie                 $sortie,
        Request                $request,
        EntityManagerInterface $em,
        EtatRepository         $etatRepo,
        VilleRepository        $villeRepo,
    ): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        if ($sortie->getOrganisePar() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n’avez pas les droits pour modifier cette sortie.');
        }
        if ($sortie->getEtat()->getLibelle() === 'En création') {
            $villes = $villeRepo->findAll();
            $route = $request->attributes->get('_route');
            $form = $this->createForm(CreatesortieType::class, $sortie, [
                'is_edit' => $route === 'sortie_edit',
            ]);
            $form->handleRequest($request);


            if ($form->isSubmitted()) {

                if ($form->get('publish')->isClicked()) {
                    $etat = $etatRepo->findByLibelle('Ouverte');
                } else {
                    $etat = $etatRepo->findByLibelle('En création');
                }

                $sortie->setEtat($etat);

                if ($form->isValid()) {
                    $em->flush();

                    $this->addFlash('success', 'Sortie mise à jour !');
                    return $this->redirectToRoute('main_home');
                } else {
                    $this->addFlash('danger', 'Erreurs dans le formulaire.');
                }
            }

            return $this->render('sortie/edit.html.twig', [
                'sortieForm' => $form,
                'sortie' => $sortie,
                'campus' => $sortie->getSiteOrganisateur(),
                'villes' => $villes,

            ]);
        }
        $this->addFlash('danger', 'Cette sortie ne peut être modifiée.');
        return $this->redirectToRoute('main_home');
    }

    #[Route('/publish/{id}', name: 'sortie_publish', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function publish(Sortie                 $sortie,
                            EtatRepository         $etatRepo,
                            EntityManagerInterface $em,
    ): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        if ($sortie->getOrganisePar() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n’avez pas les droits pour modifier cette sortie.');
        }

        if ($sortie->getDateLimiteInscription() > new \DateTime('now') && in_array($sortie->getEtat()->getLibelle(), ['Ouverte', 'En création'])) {
            $etat = $etatRepo->findByLibelle('Ouverte');
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Sortie mise à jour !');
        } else {
            $this->addFlash('danger', 'Cette sortie ne peut être mise à jour !');
        }

        return $this->redirectToRoute('main_home');

    }

    #[Route('/cancel/{id}', name: 'sortie_cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancel(Sortie                 $sortie,
                           EtatRepository         $etatRepo,
                           EntityManagerInterface $em,
    ): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        if ($sortie->getOrganisePar() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n’avez pas les droits pour modifier cette sortie.');
        }

        if ($sortie->getDateHeureDebut() > new \DateTime('now') && $sortie->getEtat()->getLibelle() === 'Ouverte') {
            $etat = $etatRepo->findByLibelle('Annulée');
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Sortie mise à jour !');

        } else {
            $this->addFlash('danger', 'Cette sortie ne peut être mise à jour !');
        }

        return $this->redirectToRoute('main_home');
    }

    #[Route('/delete/{id}', name: 'sortie_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        if ($sortie->getOrganisePar() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n’avez pas les droits pour supprimer cette sortie.');
        }

        $em->remove($sortie);
        $em->flush();

        $this->addFlash('success', 'Sortie supprimée.');

        return $this->redirectToRoute('main_home');

    }

    #[Route('/unsub/{id}', name: 'sortie_unsub', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function unsub(Sortie                 $sortie,
                          EntityManagerInterface $em
    ): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }
        $sortie->removeParticipant($user);
        $em->flush();

        $this->addFlash('success', 'Inscription supprimée.');

        return $this->redirectToRoute('main_home');
    }

    #[Route('/sub/{id}', name: 'sortie_sub', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function sub(Sortie                 $sortie,
                        EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }
        $sortie->addParticipant($user);
        $em->flush();


        $this->addFlash('success', 'Inscription validée.');

        return $this->redirectToRoute('main_home');
    }

    #[Route('/{id}', name: 'sortie_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}
