# OC_p7_vincApi_bilemo

## Technologie utilisée

- Symfony 6
- PHP8
- JWT
- Nelmio
  
## Clonage du projet

    - git clone https://github.com/vincentbordelais/p7_vinc_bilemo.git

##  Récupération de l'ensemble des packages nécessaires

    - composer install

##  Création de vos clefs publiques et privées pour JWT

Dans le dossier config, créer le répertoire "jwt"  
Pour pour créer la clef privée :  
    - openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096  
Pour créer la clef publique :  
    - openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem-pubout      

## Création d'un fichier .env.local

Ce fichier doit contenir :  
 vos identifiants de connexion à la base de données :  
    - DATABASE_URL="mysql://root:@127.0.0.1:3306/p7_vinc_bilemo"  
 le chemin vers vos clefs privées et publiques :  
    - JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem  
    - JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem  
 votre passphrase de création de clef :  
    - JWT_PASSPHRASE=password  

## Création de la base de données

    - php bin/console doctrine:database:create

## Création des tables

    - php bin/console doctrine:migrations:migrate    
    
## Lancement du serveur

    - php bin/console server:run
    
## Chargement des fixtures

    - php bin/console doctrine:fixtures:load
    
## Test des routes de l'API via Postman

Methode POST https://127.0.0.1:8000/api/login_check : pour se logger.  
Methode GET https://127.0.0.1:8000/api/products : pour récupérer l'ensemble des produits.  
Methode GET https://127.0.0.1:8000/api/products/{id} : pour récupérer un produit en particulier en fonction de son id.  
Methode GET https://127.0.0.1:8000/api/users : pour récupérer l'ensemble des utilisateurs.  
Methode POST https://127.0.0.1:8000/api/users : pour créer un nouvel utilisateur.  
Methode GET https://127.0.0.1:8000/api/users/{id} : pour récupérer un utilisateur en particulier en fonction de son id.  
Methode PUT https://127.0.0.1:8000/api/users/{id} : pour mettre à jour un utilisateur en fonction de son id.  
Methode DELETE https://127.0.0.1:8000/api/users/{id} : pour supprimer un utilisateur par rapport à son id.  
    
## Documentation de l'API via Nelmio

http://127.0.0.1:8000/api/doc
    



# Description du projet

## Contexte

BileMo est une entreprise offrant toute une sélection de téléphones mobiles haut de gamme.

Vous êtes en charge du développement de la vitrine de téléphones mobiles de l’entreprise BileMo. Le business modèle de BileMo n’est pas de vendre directement ses produits sur le site web, mais de fournir à toutes les plateformes qui le souhaitent l’accès au catalogue via une API (Application Programming Interface). Il s’agit donc de vente exclusivement en B2B (business to business).

Il va falloir que vous exposiez un certain nombre d’API pour que les applications des autres plateformes web puissent effectuer des opérations.

## Besoin client

Le premier client a enfin signé un contrat de partenariat avec BileMo ! C’est le branle-bas de combat pour répondre aux besoins de ce premier client qui va permettre de mettre en place l’ensemble des API et de les éprouver tout de suite.

 Après une réunion dense avec le client, il a été identifié un certain nombre d’informations. Il doit être possible de :

    - consulter la liste des produits BileMo ;
    - consulter les détails d’un produit BileMo ;
    - consulter la liste des utilisateurs inscrits liés à un client sur le site web ;
    - consulter le détail d’un utilisateur inscrit lié à un client ;
    - ajouter un nouvel utilisateur lié à un client ;
    - supprimer un utilisateur ajouté par un client.

Seuls les clients référencés peuvent accéder aux API. Les clients de l’API doivent être authentifiés via OAuth ou JWT.

Vous avez le choix entre mettre en place un serveur OAuth et y faire appel (en utilisant le FOSOAuthServerBundle), et utiliser Facebook, Google ou LinkedIn. Si vous décidez d’utiliser JWT, il vous faudra vérifier la validité du token ; l’usage d’une librairie est autorisé.

## Présentation des données

Le premier partenaire de BileMo est très exigeant : il requiert que vous exposiez vos données en suivant les règles des niveaux 1, 2 et 3 du modèle de Richardson. Il a demandé à ce que vous serviez les données en JSON. Si possible, le client souhaite que les réponses soient mises en cache afin d’optimiser les performances des requêtes en direction de l’API.
