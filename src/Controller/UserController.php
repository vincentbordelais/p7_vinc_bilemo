<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getUsersList(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findAll();
        $jsonUserList = $serializer->serialize($userList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])] // Plus simple avec ParamConverter :
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse 
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users', name: 'createUser', methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ClientRepository $clientRepository): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $em->persist($user);
        $em->flush();

        $content = $request->toArray();
        $idClient = $content['idClient'] ?? -1;

        $user->setClient($clientRepository->find($idClient));

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        // L'usage veut qu'on affiche dans le header l'url qu'on vient de créer. On calcule l'url (avec UrlGeneratorInterface) qu'on retourne dans la réponse :
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
    }

    #[Route('/api/users/{id}', name:"updateUser", methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em, ClientRepository $clientRepository): JsonResponse 
    // $currentUser va contenir le user correspondant à l'{id} avant update
    {
        $updatedUser = $serializer->deserialize($request->getContent(), 
                User::class, 
                'json', 
                // Bon, là il n'y a rien à comprendre, grâce à AbstractNormalizer::OBJECT_TO_POPULATE, on peut écrire à l'intérieur de $currentUser :
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);
        // $updatedUser est le user mis à jour (à partir des données de Postman)
        
        $content = $request->toArray();
        $updatedUser->setUsername($content['username'])
            ->setEmail($content['email'])
            ->setPassword($content['password'])
            ->setRoles([$content['role']]);
        $idClient = $content['idClient'] ?? -1;
        $updatedUser->setClient($clientRepository->find($idClient));
        
        $em->persist($updatedUser);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
}