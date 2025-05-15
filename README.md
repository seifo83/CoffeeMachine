# API Machine à Café


API Machine à Café
Application Symfony avec API pour gérer des machines à café.
Installation

Cloner le dépôt

git clone https://github.com/ton-nom/cafe-projet.git
cd cafe-projet

Installer les dépendances

composer install

Configurer la base de données

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Charger les données de test (optionnel)

php bin/console doctrine:fixtures:load

Lancer le serveur

symfony serve
Fonctionnalités

Gestion des machines à café
Création et annulation de commandes
Types de café configurables
Niveaux d'intensité et de sucre réglables

Structure du projet

/src/CoffeeMachine/Domain/Entity: Entités principales
/src/CoffeeMachine/Domain/ValueObject: Objets de valeur
/src/CoffeeMachine/Infrastructure: Implémentations techniques
/src/CoffeeMachine/Application: Services d'application
/tests: Tests fonctionnels et unitaires

Tests
Lancer les tests PHPUnit:
php bin/phpunit
Vérifier la qualité du code:
php vendor/bin/phpstan analyse -l 9 src tests
Documentation API
Endpoints principaux:

GET /api/machines/{id}: Récupérer une machine
POST /api/machines/{id}/start: Démarrer une machine
POST /api/machines/{id}/stop: Arrêter une machine
GET /api/machines/{id}/orders: Lister les commandes
POST /api/machines/{id}/orders: Créer une commande
DELETE /api/machines/{id}/orders/last: Annuler la dernière commande

Authentification
Utilise JWT pour l'authentification:
POST /api/login_check
{
"username": "admin",
"password": "admin"
}RéessayerClaude peut faire des erreurs. Assurez-vous de vérifier ses réponses. 3.7 Sonnet









# Domain Events de l'agrégat CoffeeMachine

| Event                     | Transition de statut     | Utilisation front    |
|--------------------------|--------------------------|-----------------------|
| MachineOrderCreated       | null ➜ PENDING           | Afficher "Commande reçue" |
| OrderStarted              | PENDING ➜ PREPARING      | Afficher "Préparation" |
| OrderCompleted            | PREPARING ➜ COMPLETED    | Afficher "Café prêt" |
| MachineOrderCancelled     | * ➜ CANCELLED            | Afficher "Commande annulée" |
