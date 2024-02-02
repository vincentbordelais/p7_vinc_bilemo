<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ClientFixtures extends Fixture
{
    private $nameData = ['Orange', 'Free', 'SFR', 'Bouygues'];

    public function getDependencies()
    {
        return [];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->nameData as $name) {
            $client = new Client();
            $client->setName($name);

            $manager->persist($client);

            // Référence pour accéder aux clients dans d'autres fixtures si nécessaire
            $this->addReference('client_' . strtolower($name), $client);
        }
        $manager->flush();
    }
}
