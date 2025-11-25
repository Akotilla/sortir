<?php

namespace App\EventListener;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Cache\CacheInterface;

class DailyListener
{

    private const CACHE_KEY = 'daily';

    public function __construct(
        private CacheInterface         $cache,
        private SortieRepository       $sortiesRepository,
        private EtatRepository         $etatRepository,
        private EntityManagerInterface $em,)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $lastRun = $this->cache->get(self::CACHE_KEY, fn() => null);

        if ($today == $lastRun) {
            return;
        }

        $this->updateSorties();
        $this->cache->delete(self::CACHE_KEY);
        $this->cache->get(self::CACHE_KEY, fn() => $today);

    }

    private function updateSorties()
    {
        $now = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $sorties = $this->sortiesRepository->findAll();
        $etats = $this->etatRepository->findAll();
        $etatsByLibelle = [];

        foreach ($etats as $etat) {
            $etatsByLibelle[$etat->getLibelle()] = $etat;
        }

        foreach ($sorties as $sortie) {

            if ($now > $sortie->getDateLimiteInscription() && $sortie->getEtat()->getLibelle() === 'Ouverte') {
                $sortie->setEtat($etatsByLibelle['Clôturée']);
            }
            if ($now > $sortie->getDateHeureDebut() && $sortie->getEtat()->getLibelle() === 'Clôturée') {
                $sortie->setEtat($etatsByLibelle['En cours']);
            }
            $fin = (clone $sortie->getDateHeureDebut())->modify('+'.$sortie->getDuree().' minutes');
            $lendemain = (clone $fin)->modify('+1 day');
            if ($now > $lendemain && $sortie->getEtat()->getLibelle() === 'En cours') {
                $sortie->setEtat($etatsByLibelle['Terminée']);
            }
            $archive = (clone $fin)->modify('+31 day');
            if ($now > $archive && in_array($sortie->getEtat()->getLibelle(), ['Terminée', 'Annulée'])) {
                $sortie->setEtat($etatsByLibelle['Historisée']);
            }
        }
        $this->em->flush();
    }
}