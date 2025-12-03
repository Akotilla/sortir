<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Form\CampusAdminType;
use App\Form\CampusType;
use App\Form\FiltreAdminType;
use App\Form\UtilisateurType;
use App\Form\VilleAdminType;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use App\Repository\FiltreInterface;
use App\Repository\UtilisateurRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin')]
final class AdminController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly UserPasswordHasherInterface $hasher)
    {
    }

    private function checkAdmin(): void
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas les droits pour accéder à cette page");
        }
    }

    private function adminListInit(
        ?FormInterface &$filterForm,
        ?FormInterface &$createForm,
        string         $filterType,
        array          $filterOptions,
        string         $createType,
        mixed          $entityClass,

    ): void
    {
        // Formulaire filtre
        $filterForm = $this->createForm($filterType, null, $filterOptions);
        // Formulaire création
        $createForm = $this->createForm($createType, $entityClass);
        // dd($filterForm, $createForm);
    }

    private function adminFiltre(
        FiltreInterface $repository,
        FormInterface   $filterForm,
    ): mixed
    {
        // Liste filtrée
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $items = $repository->filtre($filterForm->getData());
        } else {
            $items = $repository->findAll();
        }
        return $items;
    }


    private function adminCreate(
        FormInterface $createForm,
    ): void
    {
        // Traitement création
        $entity = $createForm->getData();
        $this->em->persist($entity);
        $this->em->flush();
        $this->addFlash('success', ' Création avec succès.');

    }


    #[Route('/utilisateurs', name: 'admin_utilisateurs', methods: ['GET', 'POST'])]
    public function utilisateurs(Request $request, UtilisateurRepository $userRepo): Response
    {
        $this->checkAdmin();

        $filterForm = null;
        $createForm = null;

        $this->adminListInit($filterForm, $createForm,
            FiltreAdminType::class,
            ['utilisateurs' => true],
            UtilisateurType::class,
            new Utilisateur(),

        );
        $filterForm->handleRequest($request);
        $createForm->handleRequest($request);

        $users = $this->adminFiltre($userRepo, $filterForm);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $this->adminCreate($createForm);
            return $this->redirectToRoute('admin_utilisateurs');
        }

        return $this->render('admin/admin_list.html.twig', [
            'items' => $users,
            'filterForm' => $filterForm,
            'createForm' => $createForm,
            'type' => 'utilisateurs',
        ]);
    }

    #[Route('/utilisateur/create', name: 'admin_create_utilisateur', methods: ['GET', 'POST'])]
    public function createUtilisateur(Request $request, EntityManagerInterface $em): Response
    {
        $this->checkAdmin();

        $user = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($plainPassword = $form->get('password')->getData()) {
                $user->setPassword($this->hasher->hashPassword($user, $plainPassword)); // hash pswd
            }
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('admin_utilisateurs');
        }

        return $this->render('admin/edit_utilisateur.html.twig', [
            'form' => $form,
            'user' => $user,
            'create' => true,
            'entityName' => 'Utilisateur'
        ]);

    }

    #[Route('/campus', name: 'admin_campus', methods: ['GET', 'POST'])]
    public function campus(Request $request, CampusRepository $campusRepo): Response
    {
        $this->checkAdmin();

        $filterForm = null;
        $createForm = null;

        $this->adminListInit($filterForm, $createForm,
            FiltreAdminType::class,
            ['campus' => true],
            CampusAdminType::class,
            new Campus(),

        );
        $filterForm->handleRequest($request);
        $createForm->handleRequest($request);

        $campuses = $this->adminFiltre($campusRepo, $filterForm);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $this->adminCreate($createForm);
            return $this->redirectToRoute('admin_campus');
        }

        return $this->render('admin/admin_list.html.twig', [
            'items' => $campuses,
            'filterForm' => $filterForm,
            'createForm' => $createForm,
            'type' => 'campus',
        ]);
    }


    #[Route('/villes', name: 'admin_villes', methods: ['GET', 'POST'])]
    public function villes(Request $request, VilleRepository $villeRepo): Response
    {
        $this->checkAdmin();

        $filterForm = null;
        $createForm = null;

        $this->adminListInit($filterForm, $createForm,
            FiltreAdminType::class,
            ['villes' => true],
            VilleAdminType::class,
            new Ville(),

        );
        $filterForm->handleRequest($request);
        $createForm->handleRequest($request);

        $villes = $this->adminFiltre($villeRepo, $filterForm);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $this->adminCreate($createForm);
            return $this->redirectToRoute('admin_villes');
        }

        return $this->render('admin/admin_list.html.twig', [
            'items' => $villes,
            'filterForm' => $filterForm,
            'createForm' => $createForm,
            'type' => 'villes',
        ]);
    }

    private function adminFactoEdit(Request $request, mixed $entity, string $formType): mixed
    {
        $form = $this->createForm($formType, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($entity instanceof Utilisateur) {
                if ($plainPassword = $form->get('password')->getData()) {
                    $entity->setPassword($this->hasher->hashPassword($entity, $plainPassword)); // hash pswd
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Modification effectuée avec succès.');
            return null;
        }

        return $form;
    }

    #[Route('/utilisateur/{id}/edit', name: 'admin_edit_utilisateur', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editUtilisateur(Request $request, Utilisateur $user): Response
    {
        $this->checkAdmin();

        $form = $this->adminFactoEdit($request, $user, UtilisateurType::class);

        if ($form === null) {
            return $this->redirectToRoute('admin_utilisateurs');
        }

        return $this->render('admin/edit_utilisateur.html.twig', [
            'form' => $form,
            'entityName' => 'Utilisateur',
            'edit' => true,
            'user' => $user,
        ]);
    }

    #[Route('/campus/{id}/edit', name: 'admin_edit_campus', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editCampus(Request $request, Campus $campus): Response
    {
        $this->checkAdmin();
        $form = $this->adminFactoEdit($request, $campus, CampusType::class);

        if ($form === null) {
            return $this->redirectToRoute('admin_campus');
        }

        return $this->render('admin/edit_utilisateur.html.twig', [
            'form' => $form,
            'entityName' => 'Campus',
            'edit' => true,
            'campus' => $campus,
        ]);
    }

    #[Route('/ville/{id}/edit', name: 'admin_edit_ville', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editVille(Request $request, Ville $ville): Response
    {
        $this->checkAdmin();
        $form = $this->adminFactoEdit($request, $ville, VilleType::class);

        if ($form === null) {
            return $this->redirectToRoute('admin_villes');
        }

        return $this->render('admin/edit_utilisateur.html.twig', [
            'form' => $form,
            'entityName' => 'Ville',
            'edit' => true,
            'ville' => $ville,
        ]);
    }

    private function adminFactoDelete(mixed $entity, string $routeName): mixed
    {
        $this->em->remove($entity);
        $this->em->flush();
        $this->addFlash('success', ucfirst($routeName) . ' supprimé avec succès.');
        return $this->redirectToRoute($routeName);
    }

    #[Route('/utilisateur/{id}/delete', name: 'admin_delete_utilisateur', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteUtilisateur(Utilisateur $user): Response
    {
        $this->checkAdmin();
        return $this->adminFactoDelete($user, 'admin_utilisateurs');
    }

    #[Route('/campus/{id}/delete', name: 'admin_delete_campus', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteCampus(Campus $campus): Response
    {
        $this->checkAdmin();
        return $this->adminFactoDelete($campus, 'admin_campus');
    }

    #[Route('/ville/{id}/delete', name: 'admin_delete_ville', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteVille(Ville $ville): Response
    {
        $this->checkAdmin();
        return $this->adminFactoDelete($ville, 'admin_villes');
    }

    #[Route('/', name: 'admin_control')]
    public function control(): Response
    {
        $this->checkAdmin();

        return $this->render('admin/control.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}