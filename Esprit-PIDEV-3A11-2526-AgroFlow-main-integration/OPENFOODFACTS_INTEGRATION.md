# Intégration OpenFoodFacts - Module Suggestions Alimentaires

## 📋 Vue d'ensemble

Ce module intègre l'API OpenFoodFacts à AgroFlow pour fournir des suggestions alimentaires personnalisées basées sur l'espèce de chaque animal. Les suggestions sont affichées dans la fiche détaillée de chaque animal, dans la section **"🍽️ Suggestions de nourriture"**.

## 🎯 Objectifs atteints

✅ **1. Suggestions personnalisées par espèce**
- Les suggestions sont adaptées à l'espèce de l'animal (Vache, Chèvre, Mouton, Cheval, Chien, Chat)
- Mapping automatique espèce → termes de recherche optimisés

✅ **2. Intégration API OpenFoodFacts**
- Récupération de produits réels via l'API REST
- Affichage de : Nom du produit, Marque, Image (si disponible)
- Maximum 3 produits par espèce

✅ **3. Interface utilisateur élégante**
- Cards horizontales avec image à gauche et infos à droite
- Design responsive avec grid automatique
- Lien vers OpenFoodFacts pour plus d'infos
- Message d'avertissement recommandant de consulter un vétérinaire

✅ **4. Optimisation - Cache 24h**
- Mise en cache des résultats API pour 24 heures
- Évite les appels répétés à l'API
- Améliore les performances du site
- Clé de cache : `openfoodfacts_[espece]`

✅ **5. Gestion d'erreurs**
- Fallback gracieux en cas d'erreur API
- Les suggestions n'apparaissent pas si indisponibles (pas de blocage)
- Logs des erreurs pour débogage

## 📁 Fichiers modifiés/créés

### 1. **Service OpenFoodFacts** (créé)
**Fichier:** `src/Service/OpenFoodFactsService.php`

**Responsabilités:**
- `getFoodSuggestionsForSpecies($espece, $pageSize)` : Récupère les suggestions avec cache
- `fetchProductsFromApi($searchTerms, $pageSize)` : Appel à l'API externe
- `filterProductData($product)` : Filtre les données pour ne garder que l'essentiel
- `testConnection()` : Teste la connectivité avec l'API

**Mapping des espèces:**
```php
'Vache' => 'aliments bovins cattle feed',
'Chèvre' => 'aliments chèvres goat feed',
'Mouton' => 'aliments moutons sheep feed',
'Cheval' => 'aliments chevaux horse feed',
'Chien' => 'croquettes chien dog food kibble',
'Chat' => 'croquettes chat cat food kibble',
```

### 2. **Contrôleur AnimauxController** (modifié)
**Fichier:** `src/Controller/Animals/AnimauxController.php`

**Changements:**
- Import du service `OpenFoodFactsService`
- Modification de la méthode `show()` pour injecter le service
- Passage des suggestions alimentaires au template Twig

**Code modifié:**
```php
#[Route('/{id}', name: 'app_animaux_show', methods: ['GET'])]
public function show(Animaux $animaux, OpenFoodFactsService $openFoodFactsService): Response
{
    // ... sécurité ...
    $foodSuggestions = $openFoodFactsService->getFoodSuggestionsForSpecies($animaux->getEspece());
    
    return $this->render('Animals/animaux/show.html.twig', [
        'animaux' => $animaux,
        'foodSuggestions' => $foodSuggestions,
    ]);
}
```

### 3. **Template Twig** (modifié)
**Fichier:** `templates/Animals/animaux/show.html.twig`

**Nouvelles sections:**
- Section "🍽️ Suggestions de nourriture" ajoutée en bas du template
- Affichage conditionnel : 3 cards si suggestions disponibles, message par défaut sinon
- Chaque card affiche : Image, Nom du produit, Marque, Lien OpenFoodFacts
- Design responsive avec grid `grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))`
- Animations au survol (translateY, box-shadow)
- Message d'avertissement sanitaire

## 🔧 Configuration requise

### Symfony HttpClient
Le service utilise `Symfony\Contracts\HttpClient\HttpClientInterface` qui est inclus par défaut dans les installations Symfony modernes.

### Cache
Le service utilise `Symfony\Contracts\Cache\CacheInterface` avec une durée de cache TTL de **24 heures** (86400 secondes).

Configuration par défaut (dans `config/packages/cache.yaml`) :
```yaml
framework:
    cache:
        default: cache.app
        pools:
            cache.app:
                adapter: cache.app
```

## 🚀 Comment ça marche

### Flow utilisateur
1. L'utilisateur accède à la fiche détaillée d'un animal (`/animaux/{id}`)
2. Le contrôleur `AnimauxController::show()` est appelé
3. Le service `OpenFoodFactsService` est injecté automatiquement
4. Pour la première requête : appel à l'API OpenFoodFacts
5. Résultats filtrés et mis en cache
6. Pour les requêtes suivantes (24h) : résultats récupérés du cache
7. Les suggestions sont passées au template Twig
8. L'interface affiche les 3 meilleurs produits avec images

### Structure des données
```php
// Exemple de suggestion retournée
$foodSuggestions = [
    [
        'name' => 'Aliment bovin Premium',
        'brand' => 'FarmGood',
        'image' => 'https://example.com/image.jpg'
    ],
    // ... jusqu'à 3 produits
];
```

## 🧪 Tests

### Test de connectivité API
```php
// Dans un contrôleur ou commande
public function __construct(private OpenFoodFactsService $service)
{
    $isConnected = $this->service->testConnection();
    // true si l'API est accessible
}
```

### Vérifier le cache
```bash
# Afficher le contenu du cache
php bin/console cache:pool:clear cache.app

# Vérifier les entrées de cache (dev tools)
# Dans la barre de débogage Symfony
```

### Tester manuellement
1. Aller sur la page de fiche animal : `http://localhost/animaux/{id}`
2. Vérifier que la section "🍽️ Suggestions de nourriture" apparaît
3. Vérifier que les images se chargent correctement
4. Cliquer sur "Voir plus" pour valider le lien OpenFoodFacts
5. Recharger la page → résultats du cache (plus rapide)

## 📊 Cas d'usage

- ✅ L'utilisateur voit les suggestions au premier chargement
- ✅ L'API est lente mais le cache évite le rechargement
- ✅ Pas d'image ? Un placeholder gris s'affiche
- ✅ Espèce non reconnue ? La section n'apparaît pas
- ✅ Erreur API ? Les suggestions ne bloquent pas l'affichage

## ⚠️ Limitations actuelles

1. **Disponibilité API** : L'API OpenFoodFacts est publique et gratuite, mais peut être lente
2. **Langue des données** : Les résultats sont en fonction de la base de données mondiale
3. **Qualité des données** : Dépend de la qualité des données dans OpenFoodFacts
4. **Cache global** : Le cache est partagé entre tous les utilisateurs (pas de cache par utilisateur)

## 🔄 Amélioration futures

- [ ] Filtrer par pays/région pour des résultats plus pertinents
- [ ] Ajouter des filtres nutritionnels (prix, calories, etc.)
- [ ] Implémenter un système de favoris utilisateur
- [ ] Ajouter des commentaires/avis sur les produits
- [ ] Intégrer avec des fournisseurs locaux
- [ ] Passer à un cache par animal (TTL court, invalider au besoin)

## 🐛 Débogage

### Logs
Les erreurs API sont loguées dans `var/log/dev.log` :
```
[ERROR] OpenFoodFacts API Error: ...
[ERROR] OpenFoodFacts API Request Error: ...
```

### Profiler Symfony
Activez le Web Debug Toolbar pour voir :
- Temps de réponse du service
- Hits/misses du cache
- Requêtes HTTP effectuées

### Exemples d'erreur

**Pas de suggestions :**
- Espèce non supportée
- API indisponible (voir logs)
- Aucun produit trouvé pour la recherche

**Images ne s'affichent pas :**
- HTTPS requis (check le lien dans le profiler)
- CORS peut être un problème
- Image supprimée sur OpenFoodFacts

## 📝 Notes techniques

- **Timeout API** : 10 secondes
- **Page size** : 3 produits max
- **Cache TTL** : 86400 secondes (24h)
- **Stratégie d'erreur** : Fail-safe (retourner tableau vide)

---

**Date de création :** 16 Avril 2026  
**Version :** 1.0  
**Statut :** ✅ Opérationnel et testé
