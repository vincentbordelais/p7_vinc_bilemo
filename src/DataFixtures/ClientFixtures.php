<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use App\DataFixtures\ProductFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ClientFixtures extends Fixture
{
    public function getDependencies()
    {
        return [ProductFixtures::class];
    }

    private $nameData = ['Orange', 'Free', 'SFR', 'Bouygues'];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->nameData as $name) {
            $client = new Client();
            $client->setName($name);

            // $productRepository = $manager->getRepository(Product::class);
            // $products = $productRepository->findAll();
            // // Associer aléatoirement des produits aux clients
            // shuffle($products);
            // $randomProducts = array_slice($products, 0, 3);
            // foreach ($randomProducts as $product) {
            //     $client->addProduct($product);
            // }

            $manager->persist($client);
        }
        $manager->flush();
    }
}
