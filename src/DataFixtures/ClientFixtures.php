<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use App\DataFixtures\ProductFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ClientFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [ProductFixtures::class];
    }

    private $nameData = ['Orange', 'Free', 'SFR', 'Bouygues'];

    public function load(ObjectManager $manager): void
    {
        $products = $manager->getRepository(Product::class)->findAll();

        foreach ($this->nameData as $name) {
            $client = new Client();
            $client->setName($name);

            // On assigne aléatoirement 2 products à chaque client :
            shuffle($products);
            $randomProducts = array_slice($products, 0, 2);
            foreach ($randomProducts as $product) {
                $client->addProduct($product);
            }

            $manager->persist($client);
        }
        $manager->flush();
    }
}
