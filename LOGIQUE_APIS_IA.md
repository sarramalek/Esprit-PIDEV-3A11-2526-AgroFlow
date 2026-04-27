# 📚 Logique des APIs et de l'IA - AgroFlow

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture générale](#architecture-générale)
3. [Services API](#services-api)
4. [Logique d'IA](#logique-dia)
5. [Flux de travail](#flux-de-travail)
6. [Utilisation dans l'application](#utilisation-dans-lapplication)

---

## 🎯 Vue d'ensemble

**AgroFlow** intègre plusieurs APIs externes et une logique d'IA simple pour fournir :
- ✅ Suggestions alimentaires personnalisées par espèce
- ✅ Informations médicales et vétérinaires
- ✅ Suggestions intelligentes d'articles en fonction des diagnostics
- ✅ Données encyclopédiques sur les espèces d'animaux

---

## 🏗️ Architecture générale

```
┌─────────────────────────────────────────────────────────────┐
│                    AgroFlow Application                      │
├─────────────────────────────────────────────────────────────┤
│  Controllers                                                 │
│  ├─ AnimauxController (gestion des animaux)                │
│  └─ ExamensController (gestion des examens vétérinaires)   │
├─────────────────────────────────────────────────────────────┤
│  Services (API Wrappers)                                    │
│  ├─ OpenFoodFactsService (API OpenFoodFacts)              │
│  ├─ WikipediaService (API Wikipedia + Logique IA)         │
│  ├─ RescueGroupsService (Données locales)                 │
│  └─ PdfService (Génération PDF)                            │
├─────────────────────────────────────────────────────────────┤
│  External APIs & Data Sources                               │
│  ├─ OpenFoodFacts (https://world.openfoodfacts.org)       │
│  ├─ Wikipedia FR (https://fr.wikipedia.org/api)           │
│  └─ Data locales (base de données Symfony)                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔌 Services API

### 1️⃣ OpenFoodFactsService

**Objectif :** Récupérer des suggestions alimentaires personnalisées pour chaque espèce d'animal.

#### 📍 Emplacement
```
src/Service/OpenFoodFactsService.php
```

#### 🎯 Fonctionnalités principales

##### A. `getFoodSuggestionsForSpecies($espece, $pageSize = 3)`
Récupère jusqu'à 3 suggestions alimentaires pour une espèce donnée.

```php
// Exemple d'utilisation
$suggestions = $openFoodFactsService->getFoodSuggestionsForSpecies('Vache');

// Résultat
[
    [
        'name' => 'Aliment Bovin Premium Maïs & Luzerne',
        'brand' => 'FarmGold',
        'image' => 'https://images.openfoodfacts.org/images/products/...'
    ],
    // ... jusqu'à 3 produits
]
```

##### B. `fetchProductsFromApi($searchTerms, $pageSize, $espece)`
Effectue l'appel HTTP à l'API OpenFoodFacts.

```
GET https://world.openfoodfacts.org/cgi/search.pl?
  search_terms=aliments bovins cattle feed&
  json=1&
  page_size=10
```

**Paramètres :**
- `search_terms` : Termes de recherche mappés par espèce
- `json=1` : Format de réponse JSON
- `page_size=10` : Nombre de résultats à récupérer
- `timeout=10s` : Délai maximal d'attente

##### C. `filterProductData($product)`
Filtre les données du produit pour ne garder que les champs essentiels.

```php
// Entrée API brute
[
    'product_name' => 'Aliment Bovin Premium',
    'brands' => 'FarmGold',
    'image_front_url' => '...',
    'images' => [...],
    // ... autres champs non pertinents
]

// Sortie filtrée
[
    'name' => 'Aliment Bovin Premium',
    'brand' => 'FarmGold',
    'image' => 'https://...'
]
```

#### 🗺️ Mapping des espèces

```php
SPECIES_SEARCH_TERMS = [
    'Vache'   => 'aliments bovins cattle feed',
    'Chèvre'  => 'aliments chèvres goat feed',
    'Mouton'  => 'aliments moutons sheep feed',
    'Cheval'  => 'aliments chevaux horse feed',
    'Chien'   => 'croquettes chien dog food kibble',
    'Chat'    => 'croquettes chat cat food kibble',
]
```

#### 💾 Gestion du cache

**Cache TTL : 24 heures (86400 secondes)**

```
Cache Key Format: openfoodfacts_[espece]
Exemple: openfoodfacts_vache
```

**Avantages :**
- ✅ Réduit les appels répétés à l'API
- ✅ Améliore les performances du site
- ✅ Évite les blocages de rate-limiting

#### 🔄 Fallback (Données de secours)

Si l'API OpenFoodFacts n'est pas accessible, le service retourne des données fictives prédéfinies :

```php
FALLBACK_PRODUCTS = [
    'Vache' => [
        ['name' => 'Aliment Bovin Premium...', ...],
        // ... 3 produits par espèce
    ],
    // ... pour chaque espèce
]
```

#### ⚠️ Gestion des erreurs

```
API Indisponible → Fallback Data ✓
Pas de résultats → Fallback Data ✓
Timeout (10s) → Fallback Data ✓
Espèce non reconnue → Tableau vide []
```

---

### 2️⃣ WikipediaService

**Objectif :** Fournir des informations médicales/vétérinaires et suggérer des articles Wikipedia pertinents basés sur le diagnostic.

#### 📍 Emplacement
```
src/Service/WikipediaService.php
```

#### 🎯 Fonctionnalités principales

##### A. `searchPage($query)`
Recherche une page Wikipedia et retourne un résumé.

```php
// Exemple d'utilisation
$page = $wikipediaService->searchPage('antibiothérapie');

// Résultat
[
    'title' => 'Antibiothérapie',
    'extract' => 'L\'antibiothérapie est le traitement...',
    'image' => 'https://upload.wikimedia.org/...',
    'url' => 'https://fr.wikipedia.org/wiki/...'
]
```

**Endpoint API :**
```
GET https://fr.wikipedia.org/api/rest_v1/page/summary/[query]
```

**Paramètres :**
- `query` : Terme à rechercher (encodé en URL)
- `timeout=5s` : Délai maximal

#### 💾 Cache Wikipedia

**Cache TTL : 7 jours (604800 secondes)**

```
Cache Key Format: wikipedia_[md5(query)]
Exemple: wikipedia_8a2a8f7c3e9b1d4f6a8b2c5e
```

##### B. `getQuickSuggestions($diagnostic = '', $traitement = '')`

🤖 **C'EST LA LOGIQUE D'IA DU SYSTÈME**

Analyse le texte du diagnostic et du traitement pour suggérer des articles Wikipedia pertinents.

**Algorithme :**

```
┌─────────────────────────────────────────────────────┐
│ Entrée: diagnostic + traitement                     │
├─────────────────────────────────────────────────────┤
│ Étape 1: Convertir en minuscules                    │
│ Étape 2: Boucle sur les MEDICAL_KEYWORDS           │
│ Étape 3: Tester si keyword ⊆ text                  │
│ Étape 4: Si trouvé → ajouter à suggestions         │
│ Étape 5: Limiter à 4 suggestions max               │
│ Étape 6: Si aucune trouvée → suggestions par défaut│
└─────────────────────────────────────────────────────┘
```

**Base de termes médicaux reconnus :**

```php
MEDICAL_KEYWORDS = [
    'infection' => 'Infection',
    'antibiotique' => 'Antibiotique',
    'vaccin' => 'Vaccin',
    'allergie' => 'Allergie',
    'blessure' => 'Blessure',
    'fracture' => 'Fracture',
    'arthrite' => 'Arthrite',
    'diarrhée' => 'Diarrhée',
    'vomissement' => 'Vomissement',
    'pneumonie' => 'Pneumonie',
    'gastrite' => 'Gastrite',
    'parasites' => 'Parasites',
    'dermatite' => 'Dermatite',
    'otite' => 'Otite',
    'cataracte' => 'Cataracte',
    'tumeur' => 'Tumeur',
    'anesthésie' => 'Anesthésie',
    'chirurgie' => 'Chirurgie',
    'radiographie' => 'Radiographie',
    'échographie' => 'Échographie',
]
```

**Exemple de détection :**

```
Diagnostic : "Le chien souffre d'une infection bactérienne"
Traitement : "Antibiotique amoxicilline pendant 10 jours"

Analyse:
├─ 'infection' trouvé dans "Le chien souffre d'une infection"
│  → Ajouter: ['label' => 'Infection', 'keyword' => 'infection']
└─ 'antibiotique' trouvé dans "Antibiotique amoxicilline"
   → Ajouter: ['label' => 'Antibiotique', 'keyword' => 'antibiotique']

Résultat:
[
    ['label' => 'Infection', 'keyword' => 'infection'],
    ['label' => 'Antibiotique', 'keyword' => 'antibiotique'],
]
```

**Suggestions par défaut (si aucun match):**

```php
[
    ['label' => 'Santé animale', 'keyword' => 'santé animale'],
    ['label' => 'Médecine vétérinaire', 'keyword' => 'médecine vétérinaire'],
    ['label' => 'Antibiotique', 'keyword' => 'antibiotique'],
    ['label' => 'Vaccination', 'keyword' => 'vaccination'],
]
```

---

### 3️⃣ RescueGroupsService

**Objectif :** Fournir des données encyclopédiques statiques sur chaque espèce d'animal.

#### 📍 Emplacement
```
src/Service/RescueGroupsService.php
```

#### 🎯 Fonctionnalités principales

##### A. `getEncyclopediaForSpecies($espece)`
Retourne les données encyclopédiques d'une espèce donnée.

```php
// Exemple
$data = $rescueGroupsService->getEncyclopediaForSpecies('Chien');

// Résultat
[
    'title' => '🐕 Chien',
    'description' => 'Loyal, affectueux et intelligent...',
    'icon' => 'fa-dog',
    'color' => '#8B4513',
    'temperament' => 'Loyal, affectueux et intelligent',
    'lifespan' => '10-13 ans',
    'needs' => [
        'Exercice physique régulier (30-60 min/jour)',
        'Alimentation équilibrée adaptée à son âge',
        'Socialisation et interaction sociale',
        'Soins vétérinaires réguliers',
        'Jeux et stimulation mentale',
        'Hygiène et toilettage réguliers'
    ]
]
```

##### B. `getAllEncyclopedias()`
Retourne toutes les encyclopédies pour toutes les espèces.

##### C. `getEnrichedEncyclopedia($espece)`
Retourne les données encyclopédiques avec cache (7 jours).

#### 📊 Structure des données

Chaque espèce contient :
- **title** : Nom avec emoji
- **description** : Description générale
- **icon** : Icône FontAwesome
- **color** : Couleur hexadécimale
- **temperament** : Tempérament/caractère
- **lifespan** : Durée de vie moyenne
- **needs** : Liste des besoins spécifiques

**Espèces supportées :**
- 🐕 Chien
- 🐈 Chat
- 🐄 Vache
- 🐎 Cheval
- 🐐 Chèvre
- 🐑 Mouton

---

### 4️⃣ PdfService

**Objectif :** Générer des documents PDF pour l'export de fiches animales.

#### 📍 Emplacement
```
src/Service/PdfService.php
```

(Détails non fournis dans le code actuel)

---

## 🤖 Logique d'IA

### 🧠 Qu'est-ce que c'est ?

La "logique d'IA" dans AgroFlow est basée sur :
1. **Détection de mots-clés** (KeyWord Matching)
2. **Analyse de texte simple** (Text Analysis)
3. **Suggestions intelligentes** (Intelligent Recommendations)

Ce n'est **pas** du machine learning profond (pas de neural networks, pas de modèles pré-entraînés).

### 📍 Où se trouve-t-elle ?

**Principalement dans `WikipediaService::getQuickSuggestions()`**

### 🔬 Détails de l'algorithme

```php
// Pseudocode de la logique d'IA
function getQuickSuggestions($diagnostic, $traitement) {
    $suggestions = [];
    $text = strtolower($diagnostic . ' ' . $traitement);
    
    // Détection de mots-clés
    foreach (MEDICAL_KEYWORDS as $keyword => $label) {
        if (strpos($text, $keyword) !== false) {
            suggestions[] = [
                'label' => $label,
                'keyword' => $keyword
            ];
        }
    }
    
    // Fallback si rien trouvé
    if (empty($suggestions)) {
        $suggestions = DEFAULT_SUGGESTIONS;
    }
    
    // Limiter à 4 résultats
    return array_slice($suggestions, 0, 4);
}
```

### 💡 Type d'IA

**Classification : Rule-Based AI (IA basée sur des règles)**

```
Entrée (Texte) 
    ↓
[Règles de détection de mots-clés]
    ↓
Suggestions pertinentes
    ↓
Sortie (JSON)
```

### 📈 Améliorations possibles

Pour transformer cela en vrai AI/ML :

1. **NLP (Natural Language Processing)**
   - Tokenization
   - Lemmatization
   - Semantic analysis

2. **Machine Learning**
   - Classification supervisée
   - Training sur des datasets médicaux
   - Modèles pré-entraînés (BERT, FastText)

3. **Deep Learning**
   - Neural Networks pour les recommandations
   - Transfer Learning depuis des modèles pré-entraînés

**Exemple (sklearn en Python) :**
```python
from sklearn.naive_bayes import MultinomialNB
from sklearn.feature_extraction.text import TfidfVectorizer

vectorizer = TfidfVectorizer()
X = vectorizer.fit_transform(training_texts)
classifier = MultinomialNB()
classifier.fit(X, training_labels)

predictions = classifier.predict(test_texts)
```

---

## 🔄 Flux de travail

### Scénario 1 : Affichage d'une fiche animale

```
1. Utilisateur clique sur un animal
   ↓
2. AnimauxController::show() est appelé
   ↓
3. Injection de OpenFoodFactsService
   ↓
4. Appel: $openFoodFactsService->getFoodSuggestionsForSpecies('Vache')
   ↓
5. Vérification du cache
   ├─ Si trouvé (< 24h) → Retourner les données cachées
   └─ Si not found → Appel API OpenFoodFacts
       ├─ Si succès → Filtrer, cacher, retourner
       └─ Si erreur → Utiliser FALLBACK_PRODUCTS
   ↓
6. Passage au template Twig
   ↓
7. Affichage des 3 suggestions alimentaires
```

### Scénario 2 : Création d'un examen vétérinaire

```
1. Agriculteur crée un examen (diagnostic + traitement)
   ↓
2. ExamensController::show() affiche le formulaire
   ↓
3. Utilisateur remplit le formulaire et clique sur recherche
   ↓
4. Appel AJAX à ExamensController::searchWikipedia()
   ↓
5. Paramètres reçus:
   ├─ query (terme recherche)
   ├─ diagnostic
   └─ traitement
   ↓
6. Injection de WikipediaService
   ↓
7. Appels en parallèle:
   ├─ searchPage($query) → Recherche Wikipedia
   └─ getQuickSuggestions($diagnostic, $traitement) → IA
   ↓
8. Réponse JSON:
   {
       'result': { 'title': '...', 'extract': '...', ... },
       'suggestions': [ { 'label': '...', 'keyword': '...' }, ... ],
       'query': '...'
   }
   ↓
9. Affichage dynamique dans la page
```

---

## 📱 Utilisation dans l'application

### 1. AnimauxController

**Fichier :** `src/Controller/Animals/AnimauxController.php`

```php
#[Route('/{id}', name: 'app_animaux_show', methods: ['GET'])]
public function show(Animaux $animaux, OpenFoodFactsService $openFoodFactsService): Response
{
    // Vérification des permissions
    if (!$this->isGranted('ROLE_ADMIN') && $animaux->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException(...);
    }

    // Récupérer les suggestions alimentaires
    $foodSuggestions = $openFoodFactsService->getFoodSuggestionsForSpecies(
        $animaux->getEspece()
    );

    // Passer au template
    return $this->render('Animals/animaux/show.html.twig', [
        'animaux' => $animaux,
        'foodSuggestions' => $foodSuggestions,
    ]);
}
```

**Dans le template Twig :**
```twig
{% if foodSuggestions %}
    <div class="food-suggestions">
        <h3>🍽️ Suggestions de nourriture</h3>
        {% for suggestion in foodSuggestions %}
            <div class="card">
                <img src="{{ suggestion.image }}" alt="{{ suggestion.name }}">
                <h4>{{ suggestion.name }}</h4>
                <p>{{ suggestion.brand }}</p>
            </div>
        {% endfor %}
    </div>
{% endif %}
```

### 2. ExamensController

**Fichier :** `src/Controller/Animals/ExamensController.php`

```php
#[Route('/search/wikipedia', name: 'app_examens_wikipedia_search', methods: ['POST'])]
#[IsGranted('ROLE_AGRICULTEUR')]
public function searchWikipedia(Request $request, WikipediaService $wikipediaService): JsonResponse
{
    $query = $request->request->get('query', '');
    $diagnostic = $request->request->get('diagnostic', '');
    $traitement = $request->request->get('traitement', '');

    if (empty(trim($query))) {
        return new JsonResponse(['error' => 'Requête vide'], 400);
    }

    // Recherche Wikipedia
    $result = $wikipediaService->searchPage($query);

    // Suggestions rapides (IA) basées sur le diagnostic/traitement
    $suggestions = $wikipediaService->getQuickSuggestions($diagnostic, $traitement);

    return new JsonResponse([
        'result' => $result,
        'suggestions' => $suggestions,
        'query' => $query,
    ]);
}
```

### 3. RescueGroupsService dans index()

```php
#[Route('', name: 'app_animaux_index', methods: ['GET'])]
public function index(
    Request $request,
    AnimauxRepository $animauxRepository,
    RescueGroupsService $rescueGroupsService,  // Injection
    PaginatorInterface $paginator
): Response {
    // ... pagination ...

    // Récupérer toutes les encyclopédies
    $encyclopedias = $rescueGroupsService->getAllEncyclopedias();

    return $this->render('Animals/animaux/index.html.twig', [
        'animaux' => $animaux,
        'encyclopedias' => $encyclopedias,
    ]);
}
```

---

## 📊 Résumé des APIs

| Service | API Externe | Données | Cache | Fallback |
|---------|------------|---------|-------|----------|
| **OpenFoodFactsService** | ✅ OpenFoodFacts REST | Produits | 24h | Données fictives |
| **WikipediaService** | ✅ Wikipedia REST v1 | Articles + IA | 7j | Aucun |
| **RescueGroupsService** | ❌ Données locales | Encyclopédies | 7j | N/A |
| **PdfService** | ❌ Données locales | PDF | N/A | N/A |

---

## 🔐 Sécurité et Permissions

Toutes les actions nécessitent une authentification :

```
AnimauxController::show() → Permission: ROLE_ADMIN ou propriétaire
ExamensController::searchWikipedia() → Permission: ROLE_AGRICULTEUR
```

---

## 🚀 Configuration requise

### Symfony
```yaml
# config/packages/cache.yaml
framework:
    cache:
        default: cache.app
        pools:
            cache.app:
                adapter: cache.app
```

### HttpClient (inclus par défaut)
```php
Symfony\Contracts\HttpClient\HttpClientInterface
```

### Cache (inclus par défaut)
```php
Symfony\Contracts\Cache\CacheInterface
```

---

## 📝 Notes finales

1. **L'IA est simple** : Basée sur la détection de mots-clés, pas sur du ML profond
2. **Extensibilité** : Facile d'ajouter de nouveaux termes médicaux
3. **Performance** : Cache à plusieurs niveaux (24h pour OpenFoodFacts, 7j pour Wikipedia)
4. **Robustesse** : Fallback gracieux en cas d'erreur API
5. **Sécurité** : Vérification des permissions sur tous les endpoints sensibles

---

**Document créé le : 16 avril 2026**
**Framework : Symfony 6.x**
**PHP : 8.1+**
