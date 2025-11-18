<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {

    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $participant = new Utilisateur();
        $participant->setNom($faker->lastName());
        $participant->setPrenom($faker->firstName());
        $participant->setTelephone($faker->phoneNumber());
        $participant->setEmail('admin@admin.com');
        $participant->setPassword($this->hasher->hashPassword($participant, '123456'));
        $participant->setAdministrator(true);
        $participant->setCampus($this->getReference("campus_0", Campus::class));
        $participant->setRoles(['ROLE_ADMIN']);
        $manager->persist($participant);

        for ($i = 0; $i < 100; $i++) {

            $participant = new Utilisateur();
            $participant->setNom($faker->lastName());
            $participant->setPrenom($faker->firstName());
            $participant->setTelephone($faker->phoneNumber());
            $participant->setEmail('user' . $i . '@gmail.com');
            $participant->setPassword($this->hasher->hashPassword($participant, '123456'));
            $this->addReference('participant_'.$i, $participant);


            $participant->setCampus($this->getReference("campus_" . $faker->numberBetween(0, 9), Campus::class));


            $manager->persist($participant);

        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CampusFixtures::class, EtatFixtures::class, VilleFixtures::class, LieuFixtures::class];
    }
}
