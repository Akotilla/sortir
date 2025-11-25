<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ProfileType;
use App\Form\PublicType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        $pseudo = $userOrga->getPseudo();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $profileForm = $this->createForm(PublicType::class, $userOrga);
        $profileForm->handleRequest($request);

        return $this->render('profil/show.html.twig', [
            'orga' => $pseudo,
            'userOrga' => $userOrga,
            'publicForm' => $profileForm,
            'user_images_public_path' => $this->getParameter('user_images_public_path'),
        ]);
    }


    #[Route('/', name: 'user_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request                     $request,
        UtilisateurRepository       $utilisateurRepository,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface            $slugger,
        EntityManagerInterface      $em,): Response
    {
        $user = $this->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $profileForm = $this->createForm(ProfileType::class, $user);
        $profileForm->handleRequest($request);

        //dd($profileForm->getErrors(true));

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $plainPassword = $profileForm->get('password')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $utilisateurRepository->upgradePassword($user, $hashedPassword);
            }
            if ($profileForm->has('deleteImage') && $profileForm->get('deleteImage')->getData()) {
                if ($user->getFilename()) {
                    $filePath = $this->getParameter('user_images_directory') . '/' . $user->getFilename();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                $user->setFilename(null);
            }
            /** @var UploadedFile $image */
            $imageFile = $profileForm->get('image')->getData();
            if ($imageFile) {
                if ($user->getFilename()) {
                    $oldFilePath = $this->getParameter('user_images_directory') . '/' . $user->getFilename();
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                    $user->setFilename(null);
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('user_images_directory'), // défini dans services.yaml
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    $this->addFlash('error', 'Erreur upload : '.$e->getMessage());
                }
                $user->setFilename($newFilename);

            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès !');
            return $this->redirectToRoute('user_profile');
        }
        //dd($profileForm);

        return $this->render('user/profile.html.twig', [
            'profileForm' => $profileForm,
            'user' => $user,
            'user_images_public_path' => $this->getParameter('user_images_public_path'),

        ]);
    }
}
