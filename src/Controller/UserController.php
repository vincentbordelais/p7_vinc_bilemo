<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\DeserializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{
    /**
     * Permet de récupérer l'ensemble des utilisateurs.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des utilisateurs",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Users")
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[Route('/api/users', name: 'users', methods: ['GET'])]  // https://127.0.0.1:8000/api/users?page=3&limit=2
    public function getUsersList(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        // Récupération à partir de la requête des paramètres de pagination  :
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllUsers-" . $page . "-" . $limit;

        // Récupération de la liste des utilisateurs depuis le cache ou la base de données
        $usersWithProducts = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $serializer, $page, $limit) {

            echo("L'élément n'est pas encore en cache");

            // Récupération de la liste des utilisateurs depuis le repository
            $userList = $userRepository->findAllWithPagination($page, $limit);

            $item->tag("usersCache"); // ce tag sera utilisé pour rafraîchir ce cache après un deleteUser()

            // Parcours de la liste des utilisateurs
            foreach ($userList as $user) {
                // Sérialisation de l'utilisateur en JSON
                $context = SerializationContext::create()->setGroups(['getUsers']);
                $jsonUser = $serializer->serialize($user, 'json', $context);

                // Décodage du JSON en tableau associatif
                $userArray = json_decode($jsonUser, true);

                // Récupération du client associé à l'utilisateur
                $client = $user->getClient();

                // Récupération des produits associés au client
                $products = $client ? $client->getProducts()->toArray() : [];

                // Sérialisation des produits en JSON
                $jsonProducts = $serializer->serialize($products, 'json');

                // Décodage du JSON des produits en tableau associatif
                $productsArray = json_decode($jsonProducts, true);

                // Ajout des produits au tableau de l'utilisateur
                $userArray['client']['products'] = $productsArray;

                // Ajout de l'utilisateur modifié au tableau final
                $usersWithProducts[] = $userArray;
            }

            return $usersWithProducts;
        });

        // Sérialisation du tableau modifié en JSON et retour de la réponse
        return new JsonResponse(json_encode($usersWithProducts), Response::HTTP_OK, [], true);
    }
    
    /**
     * Permet de récupérer un utilisateur en particulier en fonction de son id. 
     *
     * @OA\Parameter(
     *    name="id",
     *    in="path",
     *    description="L'id de l'utilisateur à retourner",
     *    @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Users")
     * 
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])] // Plus simple avec ParamConverter :
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse 
    {
        // Récupération de l'utilisateur et sérialisation en JSON
        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        // Décodage du JSON en tableau associatif
        $userArray = json_decode($jsonUser, true);

        // Récupération des produits associés au client
        $products = $user->getClient()->getProducts()->toArray();

        // Sérialisation des produits en JSON
        $jsonProducts = $serializer->serialize($products, 'json');

        // Décodage du JSON des produits en tableau associatif
        $productsArray = json_decode($jsonProducts, true);

        // Ajout des produits au tableau de l'utilisateur
        $userArray['client']['products'] = $productsArray;

        // Sérialisation du tableau modifié en JSON et retour de la réponse
        return new JsonResponse(json_encode($userArray), Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Permet de supprimer un utilisateur par rapport à son id.
     * 
     * @OA\Parameter(
     *    name="id",
     *    in="path",
     *    description="L'id de l'utilisateur à supprimer",
     *    @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Users")
     * 
     * @param User $user
     * @param EntityManagerInterface $em
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse 
     */
    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse 
    {
        $cache->invalidateTags(["usersCache"]); // les données changent, on rafraîchie le cache
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Permet de créer un nouvel utilisateur.
     * 
     * Le paramètre idClient est géré "à la main", pour créer l'association entre un utilisateur et un client. 
     * S'il ne correspond pas à un client valide, alors l'utilisateur sera considéré comme sans client.
     * 
     *    
     * @OA\RequestBody(
     *     required=true,
     *     description="Saisir les données de l'utilisateur à créer :",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="email", type="string", example="pierre.hrenry@free.fr"),
     *         @OA\Property(property="password", type="string", example="azeaze"),
     *         @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"ROLE_USER"}),
     *         @OA\Property(property="idClient", type="string", example="3")
     *         )
     *     )
     * @OA\Response(
     *     response=201,
     *     description="Utilisateur créé avec succès",
     *     @OA\JsonContent(
     *         @OA\Schema(ref="#/components/schemas/User")
     *     )
     * ),
     * @OA\Response(
     *         response=400,
     *         description="Données non valides"
     *     )
     * @OA\Tag(name="Users") 
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ClientRepository $clientRepository
     * @param ValidatorInterface $validator
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur')]
    #[Route('/api/users', name: 'createUser', methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ClientRepository $clientRepository, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    { 
        $userData = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($userData['email']);
        
        // Hachage du mot de passe
        $hashedPassword = $userPasswordHasher->hashPassword($user, $userData['password']);
        $user->setPassword($hashedPassword);

        $roles = $userData['roles'] ?? [];
        $user->setRoles($roles);

        // Récupération du client et assignation à l'utilisateur
        $idClient = $userData['idClient'] ?? -1;
        $client = $clientRepository->find($idClient);
        $user->setClient($client);

        // On vérifie les erreurs :
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorMessages, JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush();
        
        // Sérialisation de l'utilisateur avec les données complètes du client et des produits
        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        
        // Récupération des produits associés au client
        $products = $client ? $client->getProducts()->toArray() : [];
        
        // Sérialisation complète des produits avec toutes les informations
        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = $serializer->serialize($product, 'json');
        }

        // Décodage des données sérialisées des produits
        $decodedProductsData = [];
        foreach ($productsData as $productData) {
            $decodedProductsData[] = json_decode($productData, true);
        }

        // Ajout des produits sérialisés aux données du client
        $jsonUserArray = json_decode($jsonUser, true);
        $jsonUserArray['client']['products'] = $decodedProductsData;

        // Régénération de la réponse JSON avec les données mises à jour
        $jsonUser = json_encode($jsonUserArray);

        // Génération de l'URL de détail de l'utilisateur
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
    } 

    /**
    * Permet de mettre à jour un utilisateur en fonction de son id.
    *
    * @OA\Parameter(
    *    name="id",
    *    in="path",
    *    description="L'id de l'utilisateur à mettre à jour",
    *    @OA\Schema(type="string")
    * )
    *    
    * @OA\RequestBody(
    *     required=true,
    *     description="Saisir la mise à jour de l'utilisateur :",
    *     @OA\JsonContent(
    *         type="object",
    *         @OA\Property(property="email", type="string", example="test@free.fr"),
    *         @OA\Property(property="password", type="string", example="azeaze"),
    *         @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"ROLE_USER"}),
    *         @OA\Property(property="idClient", type="string", example="3")
    *         )
    *     )
    *     @OA\Response(
    *         response=201,
    *         description="Utilisateur mis à jour avec succès"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Données non valides"
    *     )
    * @OA\Tag(name="Users")
    *
    * @param Request $request
    * @param SerializerInterface $serializer
    * @param User $currentUser
    * @param EntityManagerInterface $em
    * @param ClientRepository $clientRepository
    * @param ValidatorInterface $validator
    * @param UserPasswordHasherInterface $userPasswordHasher
    * @param TagAwareCacheInterface $cache
    * @return JsonResponse
    */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un utilisateur')]
    #[Route('/api/users/{id}', name:"updateUser", methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em, ClientRepository $clientRepository, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher, TagAwareCacheInterface $cache): JsonResponse 
    // $currentUser va contenir le user correspondant à l'{id} avant update
    {
        $context = DeserializationContext::create()->setAttribute('object_to_populate', $currentUser);
        $updatedUser = $serializer->deserialize($request->getContent(), User::class, 'json', $context);
        
        $content = $request->toArray();
        $updatedUser
            ->setEmail($content['email'])
            ->setPassword($userPasswordHasher->hashPassword($updatedUser, $content['password']))
            ->setRoles([$content['roles']]);
        $idClient = $content['idClient'] ?? -1;
        $updatedUser->setClient($clientRepository->find($idClient));

        // On vérifie les erreurs :
        $errors = $validator->validate($updatedUser); // On demande au validator de valider l'entité user et le résultat va dans error
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorMessages, JsonResponse::HTTP_BAD_REQUEST);
        }

        $cache->invalidateTags(["usersCache"]);
        
        $em->persist($updatedUser);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
}