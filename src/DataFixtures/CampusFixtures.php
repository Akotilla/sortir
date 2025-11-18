<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $campus = new Campus();
            $campus->setNom('Campus de '.$faker->city().' ENI');
            $this->addReference('campus_'.$i, $campus);

            $manager->persist($campus);

        }



        $manager->flush();
    }
}
