<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $arrayEtat = $manager->getRepository(Etat::class)->findAll();

        for ($i = 0; $i < 20; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->word);

            $date1 = $faker->dateTimeBetween('now -30 day', 'now -1 days');
            $sortie->setDateHeureDebut(\DateTimeImmutable::createFromMutable($date1));
            $date2 = $faker->dateTimeBetween('now +10 days', '+5 months');
            $sortie->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($date2));

            $sortie->setEtat($faker->randomElement($arrayEtat));
            $min = 1;
            $max = 48;
            $h = $faker->numberBetween($min, $max);
            $min = $faker->numberBetween(0, 59);
            $duree = \DateTime::createFromFormat('H:i:s', sprintf('%02d:%02d:%02d', $h, $min, 00));
            $sortie->setDurée($duree);

            $sortie->setNbInscriptionMax($faker->numberBetween(10, 50));
            $sortie->setInfosSortie($faker->paragraph());

            $sortie->setLieu($this->getReference('lieu_'.$faker->numberBetween(0, 39), Lieu::class));
            $sortie->setSiteOrganisateur($this->getReference('campus_'.$faker->numberBetween(0, 9), Campus::class));
            $sortie->setOrganisePar($this->getReference('participant_'.$faker->numberBetween(0, 39), Utilisateur::class));







            $manager->persist($sortie);


        }



        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CampusFixtures::class, EtatFixtures::class, VilleFixtures::class, LieuFixtures::class, ParticipantFixtures::class];
    }
}
