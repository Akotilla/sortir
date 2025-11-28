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
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $hasher)
    {
        $this->em = $em;
        $this->hasher = $hasher;
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
                       $entityClass,

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
    public function villes(Request $request, VilleRepository $villeRepo)
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

    private function adminFactoEdit(Request $request, $entity, string $formType): mixed
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
    public function editUtilisateur(Request $request, Utilisateur $user)
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
    public function editCampus(Request $request, Campus $campus)
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
    public function editVille(Request $request, Ville $ville)
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

    private function adminFactoDelete($entity, string $routeName)
    {
        $this->em->remove($entity);
        $this->em->flush();
        $this->addFlash('success', ucfirst($routeName) . ' supprimé avec succès.');
        return $this->redirectToRoute($routeName);
    }

    #[Route('/utilisateur/{id}/delete', name: 'admin_delete_utilisateur', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteUtilisateur(Utilisateur $user)
    {
        $this->checkAdmin();
        return $this->adminFactoDelete($user, 'admin_utilisateurs');
    }

    #[Route('/campus/{id}/delete', name: 'admin_delete_campus', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteCampus(Campus $campus)
    {
        $this->checkAdmin();
        return $this->adminFactoDelete($campus, 'admin_campus');
    }

    #[Route('/ville/{id}/delete', name: 'admin_delete_ville', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteVille(Ville $ville)
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


//-------------------------------------------------------------------------------------------------------------------------------------------------------------------


//
//    #[Route('/utilisateurs', name: 'admin_utilisateurs', methods: ['GET', 'POST'])]
//    public function utilisateurs(
//        Request               $request,
//        UtilisateurRepository $userRepo,
//    ): Response
//    {
//        $this->checkAdmin();
//
//        $filterForm = $this->createForm(FiltreAdminType::class, null, ['utilisateurs' => true]);
//        $filterForm->handleRequest($request);
//
//        $users = $userRepo->findAll();
//        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
//            $data = $filterForm->getData();
//            if (!empty($data['nomUtilisateur'])) {
//                $users = $userRepo->createQueryBuilder('u')
//                    ->where('u.nom LIKE :nom OR u.prenom LIKE :nom OR u.pseudo LIKE :nom')
//                    ->setParameter('nom', '%' . $data['nomUtilisateur'] . '%')
//                    ->getQuery()
//                    ->getResult();
//            }
//        }
//
//        return $this->render('admin/admin_list.html.twig', [
//            'filterForm' => $filterForm,
//            'items' => $users,
//            'type' => 'utilisateurs',
//        ]);
//    }
//
//    #[Route('/utilisateur/create', name: 'admin_create_utilisateur', methods: ['GET','POST'])]
//    public function createUtilisateur(Request $request, EntityManagerInterface $em): Response
//    {
//        $this->checkAdmin();
//
//        $user = new Utilisateur();
//        $form = $this->createForm(UtilisateurType::class, $user);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            // Ici, tu peux gérer le hash du mot de passe si nécessaire
//            if ($plainPassword = $form->get('password')->getData()) {
//                $user->setPassword($plainPassword); // ou hasher si tu veux directement
//            }
//
//            $em->persist($user);
//            $em->flush();
//
//            $this->addFlash('success', 'Utilisateur créé avec succès.');
//            return $this->redirectToRoute('admin_utilisateurs');
//        }
//
//        return $this->render('admin/edit_utilisateur.html.twig', [
//            'form' => $form,
//            'user' => $user,
//            'create' => true,
//            'entityName' => 'Utilisateur'
//        ]);
//
//    }
//
//    #[Route('/utilisateur/{id}/edit', name: 'admin_edit_utilisateur', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
//    public function editUtilisateur(Request $request, Utilisateur $user, EntityManagerInterface $em): Response
//    {
//        $this->checkAdmin();
//
//        $form = $this->createForm(UtilisateurType::class, $user);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em->flush();
//            $this->addFlash('success', 'Utilisateur modifié avec succès.');
//            return $this->redirectToRoute('admin_utilisateurs');
//        }
//
//        return $this->render('admin/edit_utilisateur.html.twig', [
//            'form' => $form,
//            'user' => $user,
//            'edit' => true,
//            'entityName' => 'Utilisateur'
//        ]);
//    }
//
//    #[Route('/campus', name: 'admin_campus', methods: ['GET', 'POST'])]
//    public function campus(Request $request, CampusRepository $campusRepo): Response
//    {
//        $this->checkAdmin();
//
//        $filterForm = $this->createForm(FiltreAdminType::class, null, ['campus' => true]);
//        $filterForm->handleRequest($request);
//
//        $campuses = $campusRepo->findAll();
//
//        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
//
//            $data = $filterForm->getData();
//            if (!empty($data['nomCampus'])) {
//
//                $campuses = $campusRepo->createQueryBuilder('c')
//                    ->where('c.nom LIKE :nom')
//                    ->setParameter('nom', '%' . $data['nomCampus'] . '%')
//                    ->getQuery()
//                    ->getResult();
//            }
//
//        }
//        return $this->render('admin/admin_list.html.twig', [
//            'filterForm' => $filterForm,
//            'items' => $campuses,
//            'type' => 'campus',
//        ]);
//    }
//
//    #[Route('/campus/create', name: 'admin_create_campus', methods: ['POST'])]
//    public function createCampus(Request $request, EntityManagerInterface $em): Response
//    {
//        $this->checkAdmin();
//
//        $nom = $request->request->get('nom');
//        if ($nom) {
//            $campus = new Campus();
//            $campus->setNom($nom);
//            $em->persist($campus);
//            $em->flush();
//            $this->addFlash('success', 'Campus créé avec succès.');
//        }
//
//        return $this->redirectToRoute('admin_campus');
//    }
//
//    #[Route('/campus/{id}/edit', name: 'admin_edit_campus', methods: ['GET', 'POST'])]
//    public function editCampus(Request $request, Campus $campus, EntityManagerInterface $em): Response
//    {
//        $this->checkAdmin();
//
//        $form = $this->createForm(CampusType::class, $campus);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em->flush();
//            $this->addFlash('success', 'Campus modifié avec succès.');
//            return $this->redirectToRoute('admin_campus');
//        }
//
//        return $this->render('admin/edit_utilisateur.html.twig', [
//            'form' => $form,
//            'campus' => $campus,
//            'edit' => true,
//            'entityName' => 'Campus'
//        ]);
//    }
//
//    #[Route('/campus/{id}/delete', name: 'admin_delete_campus', methods: ['GET', 'POST'])]
//    public function deleteCampus(Campus $campus, EntityManagerInterface $em): Response
//    {
//        $this->checkAdmin();
//
//        $em->remove($campus);
//        $em->flush();
//        $this->addFlash('success', 'Campus supprimé avec succès.');
//
//        return $this->redirectToRoute('admin_campus');
//    }
//
//
//    #[Route('/villes', name: 'admin_villes', methods: ['GET', 'POST'])]
//    public function villes(Request $request, VilleRepository $villeRepo): Response
//    {
//        $filterForm = $this->createForm(FiltreAdminType::class, null, ['villes' => true]);
//        $filterForm->handleRequest($request);
//
//        $villes = $villeRepo->findAll();
//        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
//            $data = $filterForm->getData();
//            if (!empty($data['nomVille'])) {
//                $villes = $villeRepo->createQueryBuilder('v')
//                    ->where('v.nom LIKE :nom')
//                    ->setParameter('nom', '%' . $data['nomVille'] . '%')
//                    ->getQuery()
//                    ->getResult();
//            }
//        }
//
//        return $this->render('admin/admin_list.html.twig', [
//            'filterForm' => $filterForm,
//            'items' => $villes,
//            'type' => 'villes',
//        ]);
//    }
//
//    #[Route('/ville/create', name: 'admin_create_ville', methods: ['POST'])]
//    public function createVille(Request $request, EntityManagerInterface $em): Response
//    {
//        $this->checkAdmin();
//
//        $nom = $request->request->get('nom');
//        $codePostal = $request->request->get('codePostal');
//
//        if ($nom && $codePostal) {
//            $ville = new Ville();
//            $ville->setNom($nom);
//            $ville->setCodePostal($codePostal);
//            $em->persist($ville);
//            $em->flush();
//            $this->addFlash('success', 'Ville créée avec succès.');
//        }
//
//        return $this->redirectToRoute('admin_villes');
//    }
//
//    #[Route('/ville/{id}/edit', name: 'admin_edit_ville', methods: ['GET', 'POST'])]
//    public function editVille(Request $request, Ville $ville, EntityManagerInterface $em): Response
//    {
//        $this->checkAdmin();
//
//        $form = $this->createForm(VilleType::class, $ville);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em->flush();
//            $this->addFlash('success', 'Ville modifiée avec succès.');
//            return $this->redirectToRoute('admin_villes');
//        }
//
//        return $this->render('admin/edit_utilisateur.html.twig', [
//            'form' => $form,
//            'ville' => $ville,
//            'edit' => true,
//            'entityName' => 'Ville'
//        ]);
//    }
//
//    #[Route('/ville/{id}/delete', name: 'admin_delete_ville', methods: ['GET', 'POST'])]
//    public function deleteVille(Ville $ville, EntityManagerInterface $em): Response
//    {
//        $this->checkAdmin();
//
//        $em->remove($ville);
//        $em->flush();
//        $this->addFlash('success', 'Ville supprimée avec succès.');
//
//        return $this->redirectToRoute('admin_villes');
//    }
//
//}
