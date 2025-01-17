# Modifications de la Base de Données UBeer

## Structure Actuelle
La base de données contient déjà :
- Table `user` : Gestion des utilisateurs et administrateurs
- Table `beer` : Informations sur les bières

### Table `user`
```sql
CREATE TABLE `user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(180) NOT NULL,
    `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
    `password` varchar(255) NOT NULL,
    `firstname` varchar(255) NOT NULL,
    `lastname` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Solution Simple Proposée

### Nouvelle Table : `user_beers`
Une simple table de liaison entre utilisateurs et bières :
```sql
CREATE TABLE `user_beers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `beer_id` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_beer` (`user_id`, `beer_id`),
    CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Étapes de Mise en Œuvre

1. **Création de la Table**
   ```sql
   -- Créer la table de liaison
   CREATE TABLE `user_beers` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `user_id` int(11) NOT NULL,
       `beer_id` int(11) NOT NULL,
       PRIMARY KEY (`id`),
       UNIQUE KEY `unique_user_beer` (`user_id`, `beer_id`),
       CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

2. **Modifications du Backend**
   - Créer une entité simple :
     ```php
     // src/Entity/UserBeer.php
     class UserBeer
     {
         private $id;
         private $userId;
         private $beerId;
     }
     ```

3. **Modifications du Frontend**
   - Ajouter un simple bouton "J'aime" sur chaque bière
   - Ajouter une page "Mes bières" dans le profil utilisateur

## Avantages de cette Approche

1. **Simplicité**
   - Une seule table à gérer
   - Relations simples et directes
   - Facile à maintenir

2. **Pas de Modification de l'Existant**
   - Garde les tables actuelles intactes
   - Ajoute juste une table de liaison

3. **Facilité d'Implémentation**
   - Requêtes SQL simples
   - Peu de code à écrire
   - Risque minimal d'erreurs
