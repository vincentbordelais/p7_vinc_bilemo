<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Repository\ClientRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private $passwordData = 'aze';
    private $userPasswordHasher;

    private $clientRepository;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher,ClientRepository $clientRepository)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->clientRepository = $clientRepository;
    }

    public function getDependencies()
    {
        return [ClientFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupération des clients créés par la fixture ClientFixtures
        $clients = $this->clientRepository->findAll(); // ['Orange', 'Free', 'SFR', 'Bouygues']

        for ($i = 0; $i < 20; $i++){
            $user = new User();
            $user
                ->setUsername($faker->username)
                ->setEmail($faker->email)
                ->setPassword($this->userPasswordHasher->hashPassword($user, $this->passwordData))
                ->setRoles(['ROLE_USER'])
                ->setClient($clients[array_rand($clients)]);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
