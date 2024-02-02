<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixtures extends Fixture
{
    private $productData = 
    [
        [
            'name' => 'Samsung',
            'brand' => 'Galaxy S24 Ultra',
            'description' => 'Description du Samsung Galaxy S24 Ultra.',
            'price' => 1469,
        ],
        [
            'name' => 'iPhone',
            'brand' => '15 Pro',
            'description' => 'Description de l\'iPhone 15 Pro.',
            'price' => 1399,
        ],
        [
            'name' => 'Xiaomi',
            'brand' => '13 Ultra',
            'description' => 'Description du Xiaomi 13 Ultra.',
            'price' => 1299,
        ],
        [
            'name' => 'Sony',
            'brand' => 'Xperia 5 V',
            'description' => 'Description du Sony Xperia 5 V.',
            'price' => 999,
        ],
        [
            'name' => 'Google',
            'brand' => 'Pixel 8 Pro',
            'description' => 'Description du Google Pixel 8 Pro.',
            'price' => 879,
        ],
        
    ];

    public function getDependencies()
    {
        return [];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < count($this->productData); $i++) {
            $product = new Product();
            $product->setName($this->productData[$i]['name'])
                ->setBrand($this->productData[$i]['brand'])
                ->setDescription($this->productData[$i]['description'])
                ->setPrice($this->productData[$i]['price']);;
            $manager->persist($product);
        };

        $manager->flush();
    }
}
