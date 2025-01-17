# Guide des Autorisations UBeer

## Rôles Disponibles

- `ROLE_USER` : Utilisateur standard (attribué automatiquement)
- `ROLE_ADMIN` : Administrateur (accès au dashboard et fonctionnalités d'administration)

## Configuration des Rôles

### 1. Attribution du Rôle Admin

Pour attribuer le rôle admin à un utilisateur, utilisez l'une des méthodes suivantes :

#### Via SQL
```sql
UPDATE user 
SET roles = '[\"ROLE_ADMIN\"]' 
WHERE email = 'superadmin@superadmin.net';
```

#### Via EasyAdmin
1. Connectez-vous avec un compte admin existant
2. Accédez au dashboard (/admin)
3. Allez dans la section "Utilisateurs"
4. Éditez l'utilisateur souhaité
5. Ajoutez le rôle ["ROLE_ADMIN"]

## Sécurisation des Routes et Actions

### 1. Sécurisation des Contrôleurs

Pour restreindre l'accès à un contrôleur entier :
```php
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    // Toutes les actions nécessitent ROLE_ADMIN
}
```

Pour une action spécifique :
```php
#[IsGranted('ROLE_ADMIN')]
public function edit(): Response
{
    // Action réservée aux admins
}
```

### 2. Sécurisation des Templates

Pour afficher/masquer des éléments selon le rôle :
```twig
{% if is_granted('ROLE_ADMIN') %}
    <a href="{{ path('admin_edit') }}">Modifier</a>
{% endif %}
```

### 3. Sécurisation des Routes (config/packages/security.yaml)

```yaml
security:
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/compte, roles: ROLE_USER }
```

## Bonnes Pratiques

1. **Vérification des Permissions**
   - Toujours vérifier les permissions dans le contrôleur
   - Ne pas se fier uniquement à l'interface utilisateur

2. **Messages d'Erreur**
   - Personnaliser les messages d'accès refusé
   - Rediriger vers une page appropriée

3. **Hiérarchie des Rôles**
   ```yaml
   security:
       role_hierarchy:
           ROLE_ADMIN: [ROLE_USER]
   ```

## Actions Protégées par Défaut

Les actions suivantes nécessitent ROLE_ADMIN :
- Accès au dashboard EasyAdmin (/admin)
- Création/Modification/Suppression des bières
- Gestion des utilisateurs
- Configuration du site

## Exemple d'Implémentation

### Contrôleur
```php
#[Route('/beer')]
class BeerController extends AbstractController
{
    #[Route('/new', name: 'app_beer_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(): Response
    {
        // Seuls les admins peuvent créer
    }

    #[Route('/{id}/edit', name: 'app_beer_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(): Response
    {
        // Seuls les admins peuvent modifier
    }

    #[Route('/{id}', name: 'app_beer_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(): Response
    {
        // Seuls les admins peuvent supprimer
    }
}
```

### Template
```twig
{# Afficher les boutons d'action uniquement aux admins #}
{% if is_granted('ROLE_ADMIN') %}
    <div class="admin-actions">
        <a href="{{ path('app_beer_edit', {'id': beer.id}) }}" class="btn btn-primary">Modifier</a>
        <form method="post" action="{{ path('app_beer_delete', {'id': beer.id}) }}">
            <button class="btn btn-danger">Supprimer</button>
        </form>
    </div>
{% endif %}
```
