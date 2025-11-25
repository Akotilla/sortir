<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ProfileType;
use App\Form\PublicType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[Route('/user')]
final class UserController extends AbstractController
{

    #[Route('/orga/{id}', name: 'user_show', methods: ['GET'])]
    public function show(
        Utilisateur $userOrga,
        Request     $request,
    ): Response
    {
        $user = $this->getUser();
        $pseudo =  $userOrga->getPseudo();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $profileForm = $this->createForm(PublicType::class, $userOrga);
        $profileForm->handleRequest($request);

        return $this->render('profil/show.html.twig', [
            'orga' => $pseudo,
            'publicForm' => $profileForm,
        ]);
    }


    #[Route('/', name: 'user_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request                     $request,
        UtilisateurRepository       $utilisateurRepository,
        UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $profileForm = $this->createForm(ProfileType::class, $user);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $plainPassword = $profileForm->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $utilisateurRepository->upgradePassword($user, $hashedPassword);
            }

            $this->addFlash('success', 'Profil mis à jour avec succès !');
        }
        //dd($profileForm);

        return $this->render('user/profile.html.twig', [
            'profileForm' => $profileForm,
        ]);
    }


}
