# 🎨 Guide de Personnalisation des APIs - AgroFlow

## 📋 Table des matières
1. [Personnalisation OpenFoodFactsService](#1-personnalisation-openfoodfactsservice)
2. [Personnalisation WikipediaService](#2-personnalisation-wikipediaservice)
3. [Personnalisation RescueGroupsService](#3-personnalisation-rescuegroupsservice)
4. [Configuration globale](#4-configuration-globale)
5. [Exemples avancés](#5-exemples-avancés)

---

## 1️⃣ Personnalisation OpenFoodFactsService

### A. Modifier les termes de recherche par espèce

**Emplacement :** `src/Service/OpenFoodFactsService.php` (ligne 14-21)

```php
// ❌ AVANT (par défaut)
private const SPECIES_SEARCH_TERMS = [
    'Vache' => 'aliments bovins cattle feed',
    'Chèvre' => 'aliments chèvres goat feed',
    'Chien' => 'croquettes chien dog food kibble',
];

// ✅ APRÈS (personnalisé)
private const SPECIES_SEARCH_TERMS = [
    'Vache' => 'foin herbe aliments bovins premium', // ← Changé
    'Chèvre' => 'luzerne chèvre alimentation', // ← Changé
    'Chien' => 'croquettes naturelles chien bio', // ← Changé
    'Lapin' => 'luzerne lapin herbe', // ← NOUVEAU
    'Poule' => 'aliments volailles grains', // ← NOUVEAU
];
```

**Impact :** Change les résultats de recherche API OpenFoodFacts pour chaque espèce.

---

### B. Modifier le nombre de résultats

**Méthode 1 : Paramètre de la fonction**

```php
// Appel par défaut (3 produits)
$suggestions = $openFoodFactsService->getFoodSuggestionsForSpecies('Vache');

// ✅ Personnalisé (5 produits)
$suggestions = $openFoodFactsService->getFoodSuggestionsForSpecies('Vache', 5);

// ✅ Personnalisé (10 produits)
$suggestions = $openFoodFactsService->getFoodSuggestionsForSpecies('Vache', 10);
```

**Code de la fonction :**
```php
public function getFoodSuggestionsForSpecies(string $espece, int $pageSize = 3): array
//                                                            ↑
//                                           Valeur par défaut modifiable
{
    // ...
}
```

**Méthode 2 : Modifier la constante**

```php
// Dans fetchProductsFromApi(), modifier:
'page_size' => 10, // ← Augmente le nombre à récupérer depuis l'API
```

---

### C. Modifier le cache (TTL)

**Emplacement :** `src/Service/OpenFoodFactsService.php` (ligne 12)

```php
// ❌ AVANT (24 heures)
private const CACHE_TTL = 86400;

// ✅ APRÈS (48 heures)
private const CACHE_TTL = 172800; // 48 * 3600

// ✅ APRÈS (1 heure - mise à jour fréquente)
private const CACHE_TTL = 3600;

// ✅ APRÈS (7 jours - cache long terme)
private const CACHE_TTL = 604800; // 7 * 24 * 3600
```

**Conversions utiles :**
- 1 heure = 3600
- 6 heures = 21600
- 24 heures = 86400
- 7 jours = 604800
- 30 jours = 2592000

---

### D. Modifier les données de fallback

**Emplacement :** `src/Service/OpenFoodFactsService.php` (ligne 24-69)

**Exemple : Ajouter une nouvelle espèce**

```php
private const FALLBACK_PRODUCTS = [
    'Vache' => [ /* ... */ ],
    'Chien' => [ /* ... */ ],
    // ✅ NOUVELLE ESPÈCE
    'Poule' => [
        [
            'name' => 'Aliment Volaille Premium',
            'brand' => 'PoultryPro',
            'image' => 'https://images.openfoodfacts.org/images/products/xxx.jpg'
        ],
        [
            'name' => 'Grains & Céréales Volaille',
            'brand' => 'FarmGrain',
            'image' => 'https://images.openfoodfacts.org/images/products/yyy.jpg'
        ],
        [
            'name' => 'Complément Calcium Poules',
            'brand' => 'NutriChick',
            'image' => 'https://images.openfoodfacts.org/images/products/zzz.jpg'
        ]
    ]
];
```

**Exemple : Modifier un produit existant**

```php
'Vache' => [
    [
        'name' => '✅ Aliment Premium Bio pour Vaches',  // ← Changé
        'brand' => 'OrganicFarm',  // ← Changé
        'image' => 'https://new-url.jpg'  // ← Changé
    ],
    // ...
]
```

---

### E. Modifier le timeout API

**Emplacement :** `src/Service/OpenFoodFactsService.php` (ligne 129-130)

```php
$response = $this->httpClient->request('GET', self::API_BASE_URL, [
    'query' => [ /* ... */ ],
    'timeout' => 10,  // ← Valeur en secondes
]);

// ✅ Augmenter le timeout (pour connexions lentes)
'timeout' => 30,  // 30 secondes

// ✅ Diminuer le timeout (pour réponse rapide)
'timeout' => 5,   // 5 secondes
```

---

### F. Changer l'API OpenFoodFacts vers une autre langue

**Emplacement :** `src/Service/OpenFoodFactsService.php` (ligne 11)

```php
// ❌ AVANT (monde entier)
private const API_BASE_URL = 'https://world.openfoodfacts.org/cgi/search.pl';

// ✅ APRÈS (France uniquement)
private const API_BASE_URL = 'https://fr.openfoodfacts.org/cgi/search.pl';

// ✅ APRÈS (USA)
private const API_BASE_URL = 'https://us.openfoodfacts.org/cgi/search.pl';

// ✅ APRÈS (Europe)
private const API_BASE_URL = 'https://eu.openfoodfacts.org/cgi/search.pl';
```

---

### G. Ajouter des filtres de qualité

**Créer une nouvelle méthode :**

```php
/**
 * Filtre avancé : ne retourner que les produits avec score nutritionnel A-B
 */
private function filterByNutritionalScore(array $product): ?array
{
    $filteredProduct = $this->filterProductData($product);
    
    if (!$filteredProduct) {
        return null;
    }

    // Vérifier le score nutritionnel
    $nutScore = $product['nutrition_grades'] ?? null;
    
    // Accepter uniquement A et B
    if ($nutScore && in_array($nutScore, ['a', 'b', 'A', 'B'])) {
        return $filteredProduct;
    }

    return null;
}

// Utiliser dans fetchProductsFromApi():
foreach ($data['products'] as $product) {
    $filteredProduct = $this->filterByNutritionalScore($product); // ← Nouveau filtre
    if ($filteredProduct) {
        $products[] = $filteredProduct;
        if (count($products) >= $pageSize) {
            break;
        }
    }
}
```

---

### H. Ajouter des informations supplémentaires

**Modifier `filterProductData()`:**

```php
private function filterProductData(array $product): ?array
{
    if (!isset($product['product_name'])) {
        return null;
    }

    $filteredProduct = [
        'name' => $product['product_name'] ?? 'Produit sans nom',
        'brand' => $product['brands'] ?? 'Marque non spécifiée',
        'image' => null,
        // ✅ AJOUTS
        'nutrition_grade' => $product['nutrition_grades'] ?? 'N/A',
        'energy_kcal' => $product['energy_kcal'] ?? null,
        'protein' => $product['nutriments']['proteins'] ?? null,
        'fat' => $product['nutriments']['fat'] ?? null,
        'carbohydrates' => $product['nutriments']['carbohydrates'] ?? null,
        'barcode' => $product['code'] ?? null,
    ];

    // Image...
    if (isset($product['image_front_url']) && !empty($product['image_front_url'])) {
        $filteredProduct['image'] = $product['image_front_url'];
    }

    return $filteredProduct;
}
```

---

## 2️⃣ Personnalisation WikipediaService

### A. Modifier les termes médicaux reconnus

**Emplacement :** `src/Service/WikipediaService.php` (ligne 65-84)

```php
// ❌ AVANT
$medicalKeywords = [
    'infection' => 'Infection',
    'antibiotique' => 'Antibiotique',
    'vaccin' => 'Vaccin',
];

// ✅ APRÈS (ajout/modification)
$medicalKeywords = [
    // Existants
    'infection' => 'Infection bactérienne',  // ← Texte affiché changé
    'antibiotique' => 'Traitement Antibiotique',  // ← Changé
    
    // Nouveaux termes
    'arthrose' => 'Arthrose',  // ← NOUVEAU
    'hernie' => 'Hernie discale',  // ← NOUVEAU
    'colique' => 'Coliques équines',  // ← NOUVEAU
    'boiterie' => 'Boiterie',  // ← NOUVEAU
    'mastite' => 'Mastite',  // ← NOUVEAU
    'diarrhée noire' => 'Diarrhée noire grave',  // ← NOUVEAU
];
```

---

### B. Modifier le cache Wikipedia

**Emplacement :** `src/Service/WikipediaService.php` (ligne 13)

```php
// ❌ AVANT (7 jours)
private const CACHE_TTL = 604800;

// ✅ APRÈS (30 jours)
private const CACHE_TTL = 2592000;

// ✅ APRÈS (1 jour - mise à jour quotidienne)
private const CACHE_TTL = 86400;
```

---

### C. Modifier le nombre de suggestions

**Emplacement :** `src/Service/WikipediaService.php` (ligne 112)

```php
// ❌ AVANT (4 suggestions max)
return array_slice($suggestions, 0, 4);

// ✅ APRÈS (8 suggestions)
return array_slice($suggestions, 0, 8);

// ✅ APRÈS (2 suggestions seulement)
return array_slice($suggestions, 0, 2);
```

---

### D. Modifier les suggestions par défaut

**Emplacement :** `src/Service/WikipediaService.php` (ligne 104-109)

```php
// ❌ AVANT
$suggestions = [
    ['label' => 'Santé animale', 'keyword' => 'santé animale'],
    ['label' => 'Médecine vétérinaire', 'keyword' => 'médecine vétérinaire'],
    ['label' => 'Antibiotique', 'keyword' => 'antibiotique'],
    ['label' => 'Vaccination', 'keyword' => 'vaccination'],
];

// ✅ APRÈS (suggestions personnalisées)
$suggestions = [
    ['label' => 'Guide d\'élevage', 'keyword' => 'élevage'],
    ['label' => 'Nutrition animale', 'keyword' => 'nutrition'],
    ['label' => 'Parasites', 'keyword' => 'parasites'],
    ['label' => 'Premiers soins', 'keyword' => 'premiers secours'],
];
```

---

### E. Changer l'URL Wikipedia

**Emplacement :** `src/Service/WikipediaService.php` (ligne 12)

```php
// ❌ AVANT (Français)
private const API_BASE_URL = 'https://fr.wikipedia.org/api/rest_v1/page/summary/';

// ✅ APRÈS (Anglais)
private const API_BASE_URL = 'https://en.wikipedia.org/api/rest_v1/page/summary/';

// ✅ APRÈS (Allemand)
private const API_BASE_URL = 'https://de.wikipedia.org/api/rest_v1/page/summary/';

// ✅ APRÈS (Espagnol)
private const API_BASE_URL = 'https://es.wikipedia.org/api/rest_v1/page/summary/';
```

---

### F. Ajouter une logique IA personnalisée

**Créer une nouvelle méthode :**

```php
/**
 * IA avancée : suggestions basées sur le diagnostic
 */
public function getSmartSuggestions(string $diagnostic): array
{
    $suggestions = [];
    $text = strtolower($diagnostic);

    // Score de sévérité
    $severity = 0;
    if (strpos($text, 'grave') !== false) $severity = 3;
    else if (strpos($text, 'sévère') !== false) $severity = 3;
    else if (strpos($text, 'urgent') !== false) $severity = 3;
    else if (strpos($text, 'important') !== false) $severity = 2;
    else if (strpos($text, 'léger') !== false) $severity = 1;

    // Suggestions selon la sévérité
    if ($severity >= 2) {
        $suggestions[] = ['label' => '🚨 Consultation vétérinaire urgente', 'keyword' => 'urgence'];
    }

    // Détection de mots-clés
    foreach ($this->getMedicalKeywords() as $keyword => $label) {
        if (strpos($text, $keyword) !== false) {
            $suggestions[] = ['label' => $label, 'keyword' => $keyword];
        }
    }

    return array_slice($suggestions, 0, 4);
}

private function getMedicalKeywords(): array
{
    return [
        'infection' => '🦠 Infection',
        'antibiotique' => '💊 Antibiotique',
        'vaccin' => '💉 Vaccin',
        'allergie' => '🤧 Allergie',
    ];
}
```

---

## 3️⃣ Personnalisation RescueGroupsService

### A. Modifier les données encyclopédiques

**Emplacement :** `src/Service/RescueGroupsService.php` (ligne 15-105)

**Exemple : Ajouter/modifier une espèce**

```php
private const SPECIES_ENCYCLOPEDIA = [
    'Vache' => [
        'title' => '🐄 Vache Laitière',  // ← Changé
        'description' => 'Animal grégaire nécessitant des soins vétérinaires réguliers...',
        'icon' => 'fa-cow',
        'color' => '#8B4513',
        'temperament' => 'Calme, docile et sociable',
        'lifespan' => '20-25 ans',  // ← Changé
        'needs' => [
            'Pâturages ou fourrage de qualité supérieure',
            'Eau fraîche et minéralisée',
            'Abri thermorégulé pour l\'hiver',
            // ... etc
        ]
    ],
    // ✅ NOUVELLE ESPÈCE
    'Âne' => [
        'title' => '🫏 Âne',
        'description' => 'Animal robuste et intelligent, l\'âne est peu exigeant en ressources.',
        'icon' => 'fa-horse',
        'color' => '#D3D3D3',
        'temperament' => 'Têtu mais affectueux',
        'lifespan' => '25-30 ans',
        'needs' => [
            'Pâturages et foin',
            'Compagnie (animal social)',
            'Parage régulier des sabots',
            'Protection contre les prédateurs',
        ]
    ]
];
```

---

### B. Ajouter des données enrichies

**Créer une nouvelle méthode :**

```php
/**
 * Retourner des données enrichies avec vaccinations
 */
public function getVaccinationSchedule(string $espece): array
{
    $schedules = [
        'Chien' => [
            ['nom' => 'Rage', 'age' => '12 semaines'],
            ['nom' => 'DHPPV', 'age' => '8 semaines'],
            ['nom' => 'Rappel', 'age' => 'annuel'],
        ],
        'Chat' => [
            ['nom' => 'Rage', 'age' => '12 semaines'],
            ['nom' => 'Typhus', 'age' => '8 semaines'],
            ['nom' => 'Rappel', 'age' => 'annuel'],
        ],
        'Vache' => [
            ['nom' => 'Tuberculose', 'age' => '4-6 semaines'],
            ['nom' => 'Brucellose', 'age' => 'avant la première gestation'],
            ['nom' => 'Leptospirose', 'age' => 'annuel'],
        ],
    ];

    return $schedules[$espece] ?? [];
}

/**
 * Retourner le poids moyen par espèce
 */
public function getAverageWeight(string $espece): array
{
    $weights = [
        'Chien' => ['min' => 5, 'max' => 80, 'unit' => 'kg'],
        'Chat' => ['min' => 2, 'max' => 5, 'unit' => 'kg'],
        'Vache' => ['min' => 400, 'max' => 900, 'unit' => 'kg'],
        'Cheval' => ['min' => 380, 'max' => 550, 'unit' => 'kg'],
    ];

    return $weights[$espece] ?? [];
}
```

---

## 4️⃣ Configuration globale

### A. Configuration du cache (fichier yaml)

**Fichier :** `config/packages/cache.yaml`

```yaml
# ❌ AVANT
framework:
    cache:
        default: cache.app

# ✅ APRÈS (cache personnalisé par pool)
framework:
    cache:
        default: cache.app
        pools:
            cache.app:
                adapter: cache.app
            cache.openfoodfacts:
                adapter: cache.app
                default_lifetime: 86400  # 24h
            cache.wikipedia:
                adapter: cache.app
                default_lifetime: 604800  # 7j
            cache.database:
                adapter: redis
                provider: 'redis://localhost'
```

---

### B. Configuration HttpClient

**Fichier :** `config/packages/framework.yaml`

```yaml
# Ajouter/modifier :
framework:
    http_client:
        default_options:
            timeout: 10
            headers:
                'User-Agent': 'AgroFlow/1.0'
    
    # Clients nommés pour différentes APIs
    http_client:
        scoped_clients:
            openfoodfacts_client:
                base_uri: 'https://world.openfoodfacts.org'
                timeout: 15
                
            wikipedia_client:
                base_uri: 'https://fr.wikipedia.org'
                timeout: 5
```

---

## 5️⃣ Exemples avancés

### A. Créer une nouvelle méthode de recherche

**Dans OpenFoodFactsService :**

```php
/**
 * Recherche avancée avec filtres nutritionnels
 */
public function searchWithNutritionFilters(
    string $espece,
    float $minProtein = 0,
    float $maxFat = 100,
    string $nutritionGrade = 'A'
): array {
    $baseResults = $this->getFoodSuggestionsForSpecies($espece);
    
    // Filtrer selon les critères
    return array_filter($baseResults, function ($product) use ($minProtein, $maxFat, $nutritionGrade) {
        $protein = $product['protein'] ?? 0;
        $fat = $product['fat'] ?? 100;
        
        return $protein >= $minProtein && $fat <= $maxFat;
    });
}

// Utilisation :
$results = $openFoodFactsService->searchWithNutritionFilters(
    'Chien',
    minProtein: 25,
    maxFat: 15,
    nutritionGrade: 'A'
);
```

---

### B. Combiner plusieurs APIs

**Dans un contrôleur :**

```php
#[Route('/animal/{id}/full-profile', name: 'app_animal_full_profile')]
public function fullProfile(
    Animaux $animaux,
    OpenFoodFactsService $foodService,
    WikipediaService $wikiService,
    RescueGroupsService $rescueService
): Response {
    $espece = $animaux->getEspece();
    
    $profile = [
        'animal' => $animaux,
        'food_suggestions' => $foodService->getFoodSuggestionsForSpecies($espece),
        'encyclopedia' => $rescueService->getEncyclopediaForSpecies($espece),
        'medical_keywords' => $wikiService->getQuickSuggestions(),
    ];
    
    return $this->render('animal_full_profile.html.twig', $profile);
}
```

---

### C. Cacher les résultats avec Redis

**Injection dans le service :**

```php
use Symfony\Component\Cache\Adapter\RedisAdapter;

class OpenFoodFactsService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private RedisAdapter $redisCache = null  // ← Optionnel
    ) {}

    public function getFoodSuggestionsForSpecies(string $espece, int $pageSize = 3): array
    {
        // Utiliser Redis si disponible
        $cache = $this->redisCache ?? $this->cache;
        
        $cacheKey = 'openfoodfacts_' . strtolower(str_replace(' ', '_', $espece));
        
        return $cache->get($cacheKey, function (CacheItemInterface $item) use ($searchTerms, $pageSize, $espece) {
            $item->expiresAfter(self::CACHE_TTL);
            return $this->fetchProductsFromApi($searchTerms, $pageSize, $espece);
        });
    }
}
```

---

### D. Ajouter des logs personnalisés

**Dans n'importe quel service :**

```php
use Psr\Log\LoggerInterface;

class OpenFoodFactsService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private LoggerInterface $logger  // ← Injection
    ) {}

    public function getFoodSuggestionsForSpecies(string $espece, int $pageSize = 3): array
    {
        $this->logger->info("Recherche suggestions alimentaires", [
            'espece' => $espece,
            'pageSize' => $pageSize
        ]);
        
        try {
            $products = $this->cache->get($cacheKey, function (CacheItemInterface $item) use ($searchTerms, $pageSize, $espece) {
                $item->expiresAfter(self::CACHE_TTL);
                $this->logger->info("Cache miss pour {$espece}");
                return $this->fetchProductsFromApi($searchTerms, $pageSize, $espece);
            });
            
            $this->logger->info("Résultats trouvés", ['count' => count($products)]);
            return $products;
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la recherche", ['error' => $e->getMessage()]);
            return [];
        }
    }
}
```

---

## 📊 Tableau récapitulatif

| Service | Paramètre | Par défaut | Modifiable |
|---------|-----------|-----------|-----------|
| OpenFoodFacts | Termes recherche | Species mapping | Oui (constante) |
| OpenFoodFacts | Nombre résultats | 3 | Oui (paramètre) |
| OpenFoodFacts | Cache TTL | 24h | Oui (constante) |
| OpenFoodFacts | Timeout | 10s | Oui (constante) |
| OpenFoodFacts | Fallback | Données fictives | Oui (constante) |
| Wikipedia | Termes médicaux | 20 keywords | Oui (constante) |
| Wikipedia | Cache TTL | 7j | Oui (constante) |
| Wikipedia | Suggestions max | 4 | Oui (paramètre) |
| RescueGroups | Données espèces | Statiques | Oui (constante) |
| RescueGroups | Cache TTL | 7j | Oui (constante) |

---

## 🚀 Résumé des méthodes

### Méthode 1 : Modifier les constantes
✅ Le plus simple et sûr
```php
private const CACHE_TTL = 86400;  // Modifier directement
```

### Méthode 2 : Modifier les paramètres
✅ Flexible, pas de modification de code
```php
$service->getFoodSuggestionsForSpecies('Vache', 5);  // Paramètre
```

### Méthode 3 : Créer des méthodes
✅ Le plus puissant et réutilisable
```php
public function searchWithFilters($espece, $filters) { ... }
```

### Méthode 4 : Configuration YAML
✅ Séparation code/configuration
```yaml
framework:
    cache:
        pools:
            cache.openfoodfacts:
                default_lifetime: 86400
```

---

**Document créé le : 16 avril 2026**
