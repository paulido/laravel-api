# Guide de contribution PHP/Laravel

Ce guide vise à maintenir un code uniforme et facilement maintenable. Merci de bien vouloir respecter ces recommendations.

## Standards de Nomenclature en PHP

### Noms de Classes
- Utiliser le **PascalCase** (UpperCamelCase) pour les noms de classes.  
  Exemple : `UserManager`, `DatabaseConnection`, `OrderService`.

### Noms de Fonctions et de Méthodes
- Utiliser le **camelCase** pour les fonctions et méthodes.  
  Exemple : `getUserData()`, `saveOrder()`, `findAllProducts()`.

### Noms de Variables
- Utiliser le **camelCase**.  
  Exemple : `$userName`, `$productCount`, `$orderTotal`.

### Noms de Constantes
- Utiliser **UPPER_CASE_SNAKE_CASE**.  
  Exemple : `PI`, `DEFAULT_USER_ROLE`, `MAX_FILE_SIZE`.

### Fichiers
- Les fichiers de classes doivent porter le nom de la classe avec l’extension `.php`.  
  Exemple : `UserManager.php`, `OrderService.php`.

### Namespaces
- Utiliser le **PascalCase** pour les namespaces.  
  Exemple : `App\Models`, `App\Controllers`, `Utils`.

### Routes Laravel
- Utiliser **snake_case** pour nommer les routes et définir les noms.  
  Exemple :
  ```
  Route::get('/user/profile', [UserProfileController::class, 'show'])->name('user_profile');
  Route::post('/forget-password', [OrderController::class, 'store'])->name('forget_password');

  ```

  ### Format de reponse API
  - Exemple 1:

```
  {
  "status": 200,
  "message": "Request successful",
  "data": {
    "user": {
      "id": 12345,
      "name": "John Doe",
      "email": "john.doe@example.com"
    }
  },
}
```

- Exemple 2:

```
{
  "status": 400,
  "message": "Bad Request",
  "errors": [
    {
      "code": "INVALID_INPUT",
      "detail": "The provided email address is not valid."
    }
  ],
}
```

- Exemple 3:
```
{
  "status": 200,
  "message": "Operation successful",
  "data": true,
}

```
