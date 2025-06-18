<?php

namespace docker {
    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Démarre les conteneurs Docker', aliases: ['s', 'up'])]
    function start(): void
    {
        run('cd infrastructure/dev && docker-compose up -d');
        io()->success('Conteneurs démarrés avec succès! 🚀');
    }

    #[AsTask(description: 'Arrête les conteneurs Docker', aliases: ['down'])]
    function stop(): void
    {
        run('cd infrastructure/dev && docker-compose down');
        io()->success('Conteneurs arrêtés avec succès! 🛑');
    }

    #[AsTask(description: 'Liste les conteneurs Docker', aliases: ['ps'])]
    function list_containers(): void
    {
        run('cd infrastructure/dev && docker ps');
        io()->success('Conteneurs listés avec succès! 📋');
    }

    #[AsTask(description: 'Construit les conteneurs Docker', aliases: ['b'])]
    function build(): void
    {
        run('cd infrastructure/dev && docker-compose build');
        io()->success('Conteneurs construits avec succès! 🏗️');
    }

    #[AsTask(description: 'Ouvre un shell dans le conteneur PHP', aliases: ['bash', 'sh'])]
    function shell(): void
    {
        run('docker exec -it coffreo-php bash');
    }

    #[AsTask(description: 'Affiche les logs des conteneurs', aliases: ['l'])]
    function logs(string $service = ''): void
    {
        run('cd infrastructure/dev && docker-compose logs -f ' . $service);
    }
}


namespace symfony {
    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Vide le cache Symfony', aliases: ['cc'])]
    function cache_clear(): void
    {
        run('docker exec -w /var/www/app coffreo-php php bin/console cache:clear');
        io()->success('Cache vidé avec succès! 🧹');
    }

    #[AsTask(description: 'Affiche les routes Symfony', aliases: ['routes'])]
    function debug_router(): void
    {
        run('docker exec -w /var/www/app coffreo-php php bin/console debug:router');
    }

    #[AsTask(description: 'Met à jour les dépendances', aliases: ['u'])]
    function update_dependencies(): void
    {
        run('docker exec -w /var/www/app coffreo-php composer update');
        io()->success('Dépendances mises à jour avec succès! 📦');
    }
}


namespace worker {
    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Lancer le worker Messenger (command.bus)', aliases: ['start'])]
    function start_worker(): void
    {
        run('cd infrastructure/dev && docker-compose up -d worker_messenger');
        io()->success('Worker démarré avec succès 🚀');
    }

    #[AsTask(description: 'Stopper le worker Messenger', aliases: ['stop'])]
    function stop_worker(): void
    {
        run('cd infrastructure/dev && docker-compose stop worker_messenger');
        io()->success('Worker arrêté. 🛑');
    }

    #[AsTask(description: 'Lance le worker Messenger dans le container PHP (debug)', aliases: ['debug'])]
    function messenger_consume(): void
    {
        run('docker exec -it coffreo-php bash -c "cd /var/www/app && php bin/console messenger:consume async --bus=command.bus -vv"');
    }

    #[AsTask(description: 'Voir les logs en direct du worker Messenger', aliases: ['log'])]
    function worker_log(): void
    {
        run('docker logs -f coffreo-worker');
    }
}


namespace db {
    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Crée la base de données', aliases: ['create'])]
    function create(): void
    {
        run('docker exec -w /var/www/app coffreo-php php bin/console doctrine:database:create');
        io()->success('Base de données créée avec succès! 🗄️');
    }

    #[AsTask(description: 'Supprimer la base de données', aliases: ['delete', 'drop'])]
    function delete(): void
    {
        run('docker exec -w /var/www/app coffreo-php php bin/console doctrine:database:drop --force');
        io()->success('Base de données supprimée avec succès! 🗑️');
    }

    #[AsTask(description: 'Met à jour le schéma de la base de données', aliases: ['up', 'update'])]
    function update_schema(): void
    {
        run('docker exec -w /var/www/app coffreo-php php bin/console doctrine:schema:update --force');
        io()->success('Schéma de base de données mis à jour avec succès! 🔄');
    }

    #[AsTask(description: 'Met à jour le schéma de la base de données', aliases: ['start'])]
    function start(): void
    {
        run('docker exec -w /var/www/app coffreo-php php bin/console doctrine:database:create --env=dev --if-not-exists');

        run('docker exec -w /var/www/app coffreo-php php bin/console doctrine:migrations:migrate --no-interaction');

        run('docker exec -w /var/www/app coffreo-php php bin/console app:create-test-data --target-env=dev --init-db');

    }
}


namespace quality {
    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Lance le linting du code PHP', aliases: ['cs'])]
    function php_cs_fixer(): void
    {
        run('docker exec -w /var/www/app coffreo-php vendor/bin/php-cs-fixer fix --dry-run');
    }

    #[AsTask(description: 'Corrige automatiquement le code PHP', aliases: ['fix'])]
    function fix(): void
    {
        run('docker exec -w /var/www/app coffreo-php vendor/bin/php-cs-fixer fix');
        io()->success('Code PHP corrigé avec succès! ✨');
    }

    #[AsTask(description: 'Lance PHPStan pour analyser le code', aliases: ['stan'])]
    function phpstan(int $level = 9): void
    {
        io()->title('Analyse du code avec PHPStan (niveau ' . $level . ')');
        run('docker exec -w /var/www/app coffreo-php vendor/bin/phpstan analyse src tests --level=' . $level);
    }

    #[AsTask(description: 'Vérifie la qualité du code avec plusieurs outils', aliases: ['all'])]
    function check_all(): void
    {
        io()->title('Vérification complète de la qualité du code');

        io()->section('Vide le cache Symfony');
        run('docker exec -w /var/www/app coffreo-php php bin/console cache:clear');

        io()->section('Linting avec PHP-CS-Fixer');
        run('docker exec -w /var/www/app coffreo-php vendor/bin/php-cs-fixer fix --dry-run');

        io()->section('Analyse avec PHPStan');
        run('docker exec -w /var/www/app coffreo-php vendor/bin/phpstan analyse src tests --level=9');

        io()->title('Initialisation de la base de données de test');

        io()->section('Crée ou met à jour la base de données de test');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test doctrine:database:create --if-not-exists');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test doctrine:schema:update --force');

        io()->section('Tests unitaires avec PHPUnit');
        run('docker exec -w /var/www/app coffreo-php bin/phpunit');

        io()->success('Toutes les vérifications sont terminées! 🎉');
    }
}

namespace test {
    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Lance tous les tests', aliases: ['all', 'a'])]
    function all(): void
    {
        io()->title('Initialisation de la base de données de test');

        io()->section('Crée ou met à jour la base de données de test');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test doctrine:database:create --if-not-exists');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test doctrine:schema:update --force');

        io()->title('Exécution de tous les tests');
        run('docker exec -w /var/www/app coffreo-php php bin/phpunit');
        io()->success('Tests terminés avec succès! ✅');
    }

    #[AsTask(description: 'Lance les tests avec un filtre spécifique', aliases: ['f'])]
    function filter(string $filter): void
    {
        io()->title("Exécution des tests avec le filtre: $filter");
        run('docker exec -w /var/www/app coffreo-php php bin/phpunit --filter="' . $filter . '"');
        io()->success('Tests filtrés terminés! 🔍');
    }

    #[AsTask(description: 'Lance uniquement les tests unitaires', aliases: ['u', 'unit'])]
    function unit(): void
    {
        io()->title('Exécution des tests unitaires');
        run('docker exec -w /var/www/app coffreo-php php bin/phpunit tests/CoffeeMachine/Unit');
        io()->success('Tests unitaires terminés! 🧪');
    }

    #[AsTask(description: 'Initialise la base de données de test', aliases: ['init-db'])]
    function init_db(): void
    {
        io()->title('Initialisation de la base de données de test');

        io()->section('Crée ou met à jour la base de données de test');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test doctrine:database:create --if-not-exists');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test doctrine:schema:update --force');

        io()->success('Base de données de test initialisée avec succès! 🗄️');
    }

    #[AsTask(description: 'Recherche les tests fonctionnels disponibles', aliases: ['find-func'])]
    function find_functional(): void
    {
        io()->title('Recherche des tests fonctionnels disponibles');
        $result = run('docker exec -w /var/www/app coffreo-php find tests/ -name "*Functional*" -type d -o -name "*Functional*Test.php"', allowFailure: true);
        io()->text($result->getOutput());
    }

    #[AsTask(description: 'Prépare et lance les tests fonctionnels', aliases: ['func'])]
    function functional(): void
    {
        io()->title('Exécution des tests fonctionnels');

        // 1. S'assurer que l'environnement de test est correctement configuré
        io()->section('Configuration de l\'environnement de test');

        // Vérifier que le .env.test.local contient la bonne configuration
        $envContent = "DATABASE_URL=\"mysql://root:password@database:3306/coffee_machine_test?serverVersion=8.0&charset=utf8mb4\"\n";
        run('docker exec -w /var/www/app coffreo-php bash -c "echo \'' . $envContent . '\' > .env.test.local"');

        // 2. Préparer la base de données de test
        io()->section('Préparation de la base de données de test');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test doctrine:database:create --if-not-exists');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test doctrine:schema:update --force');
        run('docker exec -w /var/www/app coffreo-php php bin/console --env=test app:create-test-data --target-env=test --init-db --fixtures', allowFailure: true);

        // 3. Exécuter les tests fonctionnels
        io()->section('Exécution des tests');
        $result = run('docker exec -w /var/www/app coffreo-php php bin/phpunit tests/CoffeeMachine/Functional', allowFailure: true);

        if ($result->isSuccessful()) {
            io()->success('Tests fonctionnels réussis! ✅');
        } else {
            io()->error('Des tests ont échoué. Voici les détails:');
            io()->text($result->getOutput());
        }
    }

    #[AsTask(description: 'Lance la suite complète de tests fonctionnels', aliases: ['run-func'])]
    function run_functional(): void
    {
        io()->title('Préparation et exécution des tests fonctionnels');

        // Initialiser la base de données de test
        init_db();

        // Exécuter les tests fonctionnels
        functional();

        io()->success('Suite de tests fonctionnels terminée! 🎯');
    }
}


namespace dev {
    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Initialise la base de données de développement', aliases: ['init-db'])]
    function init_db(): void
    {
        io()->title('Initialisation de la base de données de développement');
        run('docker exec -w /var/www/app coffreo-php php bin/console app:create-test-data --target-env=dev --init-db');
        io()->success('Base de données de développement initialisée avec succès! 🗄️');
    }

    #[AsTask(description: 'Réinitialise complètement l\'environnement de développement', aliases: ['reset'])]
    function reset(): void
    {
        io()->title('Réinitialisation de l\'environnement de développement');

        io()->section('Suppression de la base de données');
        \db\delete();

        io()->section('Création de la base de données');
        \db\create();

        io()->section('Mise à jour du schéma');
        \db\update_schema();

        io()->section('Initialisation avec les données de démo');
        init_db();

        io()->section('Nettoyage du cache');
        \symfony\cache_clear();

        io()->success('Environnement de développement réinitialisé avec succès! 🔄');
    }
}


namespace front {

    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Nettoie le cache', aliases: ['front-cc'])]
    function cache(): void
    {
        run('npm cache clean --force');

        io()->section('✅ Nettoyage du cache front terminé');
    }

    #[AsTask(name: 'reset-docker', description: 'Nettoie le cache du front dans Docker et relance le service', aliases: ['front-docker'])]
    function reset_docker(): void
    {
        io()->section('🧼 Suppression du cache et des modules dans le conteneur frontend...');

        run('docker-compose exec frontend rm -rf .next node_modules package-lock.json');

        io()->section('📦 Réinstallation des dépendances...');
        run('docker-compose exec frontend npm install');

        io()->section('🚀 Relance du serveur Next.js (npm run dev)...');
        run('docker-compose exec -T frontend npm run dev');

        io()->success('✅ Frontend réinitialisé et relancé dans Docker');
    }

    }


// Raccourcis depuis le namespace global
namespace {
    use Castor\Attribute\AsTask;
    use function Castor\run;
    use function Castor\io;

    #[AsTask(description: 'Lance tous les tests', aliases: ['t'])]
    function test(): void
    {
        \test\all();
    }

    #[AsTask(description: 'Lance les tests avec un filtre spécifique', aliases: ['filter'])]
    function filter(string $filter = ''): void
    {
        if (empty($filter)) {
            io()->error('Veuillez spécifier un filtre');
            return;
        }
        \test\filter($filter);
    }

    #[AsTask(description: 'Ouvre un shell dans le conteneur PHP', aliases: ['bash', 'sh'])]
    function shell(): void
    {
        \docker\shell();
    }

    #[AsTask(description: 'Reconstruit l\'environnement complet depuis zéro', aliases: ['rebuild'])]
    function rebuild(): void
    {
        io()->title('Reconstruction complète de l\'environnement');

        io()->section('Arrêt des conteneurs');
        \docker\stop();

        io()->section('Construction des conteneurs');
        \docker\build();

        io()->section('Démarrage des conteneurs');
        \docker\start();

        io()->section('Réinitialisation de l\'environnement de développement');
        \dev\reset();

        io()->success('Environnement complètement reconstruit avec succès! 🎉');
    }

    #[AsTask(description: 'Démarre le projet complet en une seule commande', aliases: ['start-project', 'boot'])]
    function quickstart(): void
    {
        io()->title('Démarrage rapide du projet Coffee Machine');

        io()->section('1/5 - Démarrage des conteneurs Docker');
        \docker\start();

        // Attendre que les conteneurs soient prêts
        io()->text('Attente de 5 secondes pour l\'initialisation des conteneurs...');
        sleep(5);

        io()->section('2/5 - Gestion de la base de données');

        // Supprimer la base si elle existe
        io()->text('Suppression de l\'ancienne base de données si elle existe...');
        run('docker exec -w /var/www/app coffreo-php php bin/console doctrine:database:drop --force --if-exists', allowFailure: true);

        // Créer une nouvelle base
        io()->text('Création d\'une nouvelle base de données...');
        run('docker exec -w /var/www/app coffreo-php php bin/console doctrine:database:create');


        io()->section('3/5 - Mise à jour du schéma de la base de données');
        \db\update_schema();

        io()->section('4/5 - Chargement des données de démo');
        \dev\init_db();

        io()->section('5/5 - Démarrage du worker Messenger');
        \worker\start_worker();

        // Afficher des informations utiles
        io()->newLine();
        io()->success('🚀 Projet démarré avec succès en une seule commande!');
        io()->newLine();

        // Liste des URLs et informations utiles
        io()->text([
            '<info>Informations utiles:</info>',
            '• API: <href=http://localhost:8080/api/machines>http://localhost:8080/api/machines</>',
            '• Base de données: mysql://root:password@localhost:3306/coffee_machine',
            '• Pour interagir avec le projet: <comment>castor help</comment>',
            '• Pour voir les logs: <comment>castor docker:logs</comment>',
            '• Pour arrêter le projet: <comment>castor docker:stop</comment>',
        ]);
    }

    #[AsTask(description: 'Commande par défaut exécutée sans arguments', aliases: ['default'])]
    function default_task(): void
    {
        io()->title('🎯 Coffee Machine Project');
        io()->text([
            'Bienvenue dans le projet Coffee Machine! Que souhaitez-vous faire?',
            '',
            '<info>Démarrage rapide:</info>',
            '• <comment>castor quickstart</comment> - Démarre le projet complet en une seule commande',
            '',
            '<info>Commandes principales:</info>',
            '• <comment>castor docker:start</comment> - Démarre les conteneurs Docker',
            '• <comment>castor docker:stop</comment> - Arrête les conteneurs Docker',
            '• <comment>castor dev:reset</comment> - Réinitialise l\'environnement de développement',
            '• <comment>castor test:all</comment> - Lance tous les tests',
            '• <comment>castor test:run-func</comment> - Lance les tests fonctionnels',
            '• <comment>castor quality:all</comment> - Vérifie la qualité du code',
            '',
            '<info>Pour voir toutes les commandes disponibles:</info>',
            '• <comment>castor list</comment>',
        ]);
    }
}