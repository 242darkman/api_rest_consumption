# Consommation et Création d'une API

## Installation de dépendances
```bash
composer install
npm install
```

## Compiler les assets avec Webpack Encore
```bash
npm run watch
```

## Démarrer le projet
```bash
symfony server:start # symfony server:start -d (pour le lancer en arrière plan)
```
## Authentification HTTP Basic dans Postman

Pour configurer l'authentification HTTP Basic dans Postman, suivez ces étapes :

1. **Ouvrir une nouvelle requête** : Cliquez sur l'onglet `New` puis `Request`.

2. **Sélectionner l'onglet Authorization** : Dans la nouvelle requête, allez à l'onglet `Authorization`.

3. **Choisir le Type d'Authentification** : Dans la liste déroulante `Type`, choisissez `Basic Auth`.

4. **Entrer les Crédentials** :
    - **Username** : apiuser
    - **Password** : apipassword

5. **Envoyer la Requête** : Après avoir rentré vos informations, envoyez votre requête pour tester l'authentification.

## Formatage du Corps des Requêtes `PATCH` et `DELETE` dans Postman

### Requête `PATCH`
Pour effectuer une requête `PATCH` dans Postman, pour modifier le nom d'une entreprise vous devez formatter le corps de la requête en JSON comme suit :

```json
{
    "siren": 779834795,
    "Raison_sociale": "CARREFOUR VINCENT"
}
```

### Requête `DELETE`
Pour une requête `DELETE` dans Postman, le corps de la requête doit être formaté en JSON de la manière suivante :

```json
{
    "siren": 779834795
}
```