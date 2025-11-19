<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


final class MainController extends AbstractController
{
    #[Route("/", name:"main_home", methods: ["GET"])]
    public function home(SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $campus = $campusRepository->findAll();
        $idUser=$user->getCampus()->getId();

        //$sorties = $sortieRepository->findBy(['siteOrganisateur'=>$idUser], ['dateLimiteInscription'=>'DESC']);
        $sorties = $sortieRepository->findByCampusWithRelations($idUser);
        //dd($sorties);

        $parameters = ["sorties"=>$sorties];
        $parameters["campus"] = $campus;
        $parameters["user"] = $user->getPseudo();

       //dd($parameters);



        return $this->render('main/home.html.twig',$parameters);
    }
}
