<?php

namespace App\Repository;

use App\DTO\SortieFiltre;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByCampusWithRelations(int $campusId, ?int $userId = null): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.siteOrganisateur', 'c')
            ->addSelect('c')
            ->leftJoin('s.etat', 'e')
            ->addSelect('e')
            ->leftJoin('s.organisePar', 'o')
            ->addSelect('o')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p')
            ->where('c.id = :campusId')
            ->setParameter('campusId', $campusId)
            ->andWhere('e.libelle != :historisee')
            ->setParameter('historisee', 'Historisée')
            ->andWhere('(e.libelle != :enCreation OR o.id = :userId)')
            ->setParameter('enCreation', 'En création')
            ->setParameter('userId', $userId)
            ->orderBy('s.dateLimiteInscription', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByFiltre(SortieFiltre $filtre, ?int $userId = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.siteOrganisateur', 'c')
            ->addSelect('c')
            ->leftJoin('s.etat', 'e')
            ->addSelect('e')
            ->leftJoin('s.organisePar', 'o')
            ->addSelect('o')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p');

        $qb->andWhere('e.libelle != :historisee')
            ->setParameter('historisee', 'Historisée');

        $qb->andWhere('(e.libelle != :enCreation OR o.id = :currentUser)')
            ->setParameter('enCreation', 'En création')
            ->setParameter('currentUser', $userId);

        if ($filtre->getSiteOrganisateur()) {
            $qb->andWhere('s.siteOrganisateur = :campus')
                ->setParameter('campus', $filtre->getSiteOrganisateur());
        }

        if ($filtre->getNom()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filtre->getNom() . '%');
        }

        if ($filtre->getDateHeureDebut()) {
            $qb->andWhere('s.dateHeureDebut >= :dateHeureDebut')
                ->setParameter('dateHeureDebut', $filtre->getDateHeureDebut());
        }

        if ($filtre->getDateLimiteInscription()) {
            $qb->andWhere('s.dateLimiteInscription <= :dateLimiteInscription')
                ->setParameter('dateLimiteInscription', $filtre->getDateLimiteInscription());
        }

        if ($filtre->getOrganisePar() && $userId) {
            $qb->andWhere('s.organisePar = :userId')
                ->setParameter('userId', $userId);
        }

        if ($filtre->getParticipe() && $userId) {
            $qb->andWhere(':userId MEMBER OF s.participants')
                ->setParameter('userId', $userId);
        }

        if ($filtre->getParticipePas() && $userId) {
            $qb->andWhere(':userId NOT MEMBER OF s.participants')
                ->setParameter('userId', $userId);
        }

        if ($filtre->getTermine()) {
            $qb->andWhere('e.libelle = :terminee ')
                ->setParameter('terminee', 'Terminée');
        }

        return $qb->orderBy('s.dateLimiteInscription', 'ASC')
            ->getQuery()
            ->getResult();
    }


//    public function findFiltered(array $filters, $user)
//    {
//        $qb = $this->createQueryBuilder('s')
//            ->innerJoin('s.participants', 'p')
//            ->innerJoin('s.etat', 'e')
//            ->addSelect('p', 'e');
//
//        if (isset($filters['campus']) && $filters['campus'] !== '') {
//            $qb->andWhere('s.siteOrganisateur = :campusId')
//                ->setParameter('campusId', $filters['campus']);
//        }
//
//        if (isset($filters['nomSortie']) && trim($filters['nomSortie']) !== '') {
//            $qb->andWhere('s.nom LIKE :nom')
//                ->setParameter('nom', '%' . $filters['nomSortie'] . '%');
//        }
//
//        if (isset($filters['dateDebut']) && $filters['dateDebut'] !== '') {
//            try {
//                $dateDebut = new \DateTimeImmutable($filters['dateDebut'] . ' 00:00:00');
//                $qb->andWhere('s.dateHeureDebut >= :dateDebut')
//                    ->setParameter('dateDebut', $dateDebut);
//            } catch (\Exception $e) {
//
//            }
//        }
//
//        if (isset($filters['dateFin']) && $filters['dateFin'] !== '') {
//            try {
//                $dateFin = new \DateTimeImmutable($filters['dateFin'] . ' 23:59:59');
//                $qb->andWhere('s.dateHeureDebut <= :dateFin')
//                    ->setParameter('dateFin', $dateFin);
//            } catch (\Exception $e) {
//
//            }
//        }
//
//        if (!empty($filters['organisateur'])) {
//            $qb->andWhere('s.organisePar = :user')
//                ->setParameter('user', $user);
//
//        }
//        if (!empty($filters['inscrit'])) {
//            $qb->andWhere(':user MEMBER OF s.participants')
//                ->setParameter('user', $user);
//        }
//        if (!empty($filters['pasInscrit'])) {
//            $qb->andWhere(':user NOT MEMBER OF s.participants')
//                ->setParameter('user', $user);
//        }
//        if (!empty($filters['terminees'])) {
//            $qb->andWhere('e.libelle = :terminee')
//                ->setParameter('terminee', 'Terminée');
//        }
//
//
//        $qb->orderBy('s.dateLimiteInscription', 'ASC');
//
//        return $qb->getQuery()->getResult();
//    }




    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


}
