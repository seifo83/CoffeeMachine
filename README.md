# ☕ Machine à Café Connectée

Ce projet simule une machine à café connectée. Il permet aux utilisateurs de :

- Commander un café depuis une interface web
- Suivre la préparation en temps réel (préparation, prêt, annulation)
- Annuler une commande si elle est encore en cours
- Visualiser les statuts sur un écran dédié

Le projet utilise une architecture moderne basée sur Symfony (DDD, CQRS, Event), un front-end Next.js et de la communication en temps réel via Mercure.

---

## 🧰 Stack technique

| 🧩 Composant     | 🛠️ Technologie                          | 📌 Usage                                        |
|------------------|------------------------------------------|------------------------------------------------|
| Back-end API     | Symfony 6.4 (DDD, CQRS, Messenger)       | Traitement métier, API REST                    |
| Asynchrone       | RabbitMQ + Symfony Messenger             | Gestion des commandes différées (workers)      |
| Front-end        | Next.js (React)                          | Interface utilisateur interactive              |
| Temps réel       | Mercure                                  | Affichage des statuts de commande en direct    |
| Authentification | JWT (LexikJWTAuthenticationBundle)       | Sécurisation des routes API                    |
| Environnement    | Docker                                   | Environnement de dev homogène et portable      |
| Tests            | PHPUnit                                  | Tests fonctionnels avec base isolée            |
| Qualité code     | PHPStan (niveau 9)                       | Analyse statique stricte                       |
| Formatage code   | PHP-CS-Fixer                             | Respect des conventions PSR & mise en forme    |

---

## 🚀 Lancer le projet en une seule commande

Copiez-collez cette ligne dans le terminal :

```bash
composer require --dev jolicode/castor --no-interaction && vendor/bin/castor quickstart
```

🔧 Cette commande :

Installe l’outil Castor localement (dev)

Lance automatiquement le démarrage complet du projet:

Prépare Docker, installe les dépendances, initialise la base de données et les données de démo

🟢 Backend : http://localhost:8080/api/machines


🟢 Frontend : http://localhost:3010

---

## 🧪 Lancer les tests

```bash
castor test
```

---

## 🔒 Authentification

Certaines routes de l’API nécessitent un token JWT. Pour générer un token :

```bash
POST /api/login_check
{
  "username": "admin",
  "password": "admin"
}
```

Utilisez ensuite le token pour les requêtes protégées via l’en-tête :

```http
Authorization: Bearer <votre_token>
```

---

## 🔍 Architecture

Le projet suit une architecture orientée DDD et CQRS avec séparation claire :

- `Domain` : logique métier
- `Application` : cas d’usage (commands / queries)
- `Infrastructure` : contrôleurs, repos, events, etc.

> 📘 Pour les détails complets de l’architecture :  
👉 [Voir ARCHITECTURE.md](./ARCHITECTURE.md)

---
