<?php

namespace App\Service\Animals;

use Symfony\Contracts\Cache\CacheInterface;
use Psr\Cache\CacheItemInterface;

class RescueGroupsService
{
    private const CACHE_TTL = 604800; // 7 jours en secondes
    
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Données d'encyclopédie pour chaque espèce
     */
    private const SPECIES_ENCYCLOPEDIA = [
        'Chien' => [
            'title' => '🐕 Chien',
            'description' => 'Loyal, affectueux et intelligent, le chien est un animal de compagnie idéal qui a besoin d\'exercice régulier et de socialisation.',
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
        ],
        'Chat' => [
            'title' => '🐈 Chat',
            'description' => 'Indépendant mais affectueux, le chat est un compagnon agile et intelligent qui aime explorer son territoire.',
            'icon' => 'fa-cat',
            'color' => '#FF6347',
            'temperament' => 'Indépendant mais affectueux',
            'lifespan' => '12-18 ans',
            'needs' => [
                'Territoire sécurisé avec cachettes',
                'Litière propre et accessible',
                'Alimentation de qualité adaptée au chat',
                'Jeux et griffoirs',
                'Contrôles vétérinaires annuels',
                'Attention et affection variable'
            ]
        ],
        'Vache' => [
            'title' => '🐄 Vache',
            'description' => 'Calme, grégaire et docile, la vache est un animal d\'élevage qui a besoin d\'espace, de fourrage de qualité et d\'une bonne gestion sanitaire.',
            'icon' => 'fa-cow',
            'color' => '#8B4513',
            'temperament' => 'Calme, grégaire et docile',
            'lifespan' => '18-22 ans',
            'needs' => [
                'Pâturages ou fourrage de qualité',
                'Accès constant à l\'eau fraîche',
                'Abri pour protection contre les intempéries',
                'Soins vétérinaires réguliers',
                'Vie en groupe (animal grégaire)',
                'Hygiène et nettoyage des installations'
            ]
        ],
        'Cheval' => [
            'title' => '🐎 Cheval',
            'description' => 'Intelligent, sensible et athlétique, le cheval est un animal noble qui nécessite de l\'espace, du dressage régulier et une excellente nutrition.',
            'icon' => 'fa-horse',
            'color' => '#D2B48C',
            'temperament' => 'Intelligent, sensible et athlétique',
            'lifespan' => '25-30 ans',
            'needs' => [
                'Pâturages spacieux et clôturés',
                'Fourrage et grains adaptés à l\'activité',
                'Exercice et travail régulier',
                'Maréchalerie (sabots) tous les 6-8 semaines',
                'Soins dentaires et vétérinaires',
                'Abri couvert et litière propre'
            ]
        ],
        'Chèvre' => [
            'title' => '🐐 Chèvre',
            'description' => 'Curieuse, ludique et intelligente, la chèvre est un animal d\'élevage actif qui aime explorer et sauter, idéale pour les petites fermes.',
            'icon' => 'fa-goat',
            'color' => '#A9A9A9',
            'temperament' => 'Curieuse, ludique et intelligente',
            'lifespan' => '15-18 ans',
            'needs' => [
                'Espace pour grimper et explorer',
                'Herbe fraîche et foin de qualité',
                'Eau fraîche toujours disponible',
                'Abri contre les intempéries',
                'Compagnie (animal grégaire)',
                'Soins vétérinaires et vaccination'
            ]
        ],
        'Mouton' => [
            'title' => '🐑 Mouton',
            'description' => 'Paisible, grégaire et facile à gérer, le mouton est un animal d\'élevage docile qui s\'adapte bien à diverses conditions environnementales.',
            'icon' => 'fa-sheep',
            'color' => '#F5F5F5',
            'temperament' => 'Paisible, grégaire et facile',
            'lifespan' => '11-12 ans',
            'needs' => [
                'Pâturages bien entretenus',
                'Herbe fraîche en saison, foin en hiver',
                'Abri simple contre les conditions extrêmes',
                'Compagnie du troupeau',
                'Tonte annuelle de la laine',
                'Vaccinations et contrôles vétérinaires'
            ]
        ]
    ];

    /**
     * Obtenir les données encyclopédie pour une espèce spécifique
     *
     * @return array{title: string, description: string, icon: string, color: string, temperament: string, lifespan: string, needs: array<int, string>}|null
     */
    public function getEncyclopediaForSpecies(string $espece): ?array
    {
        return self::SPECIES_ENCYCLOPEDIA[$espece] ?? null;
    }

    /**
     * Obtenir toutes les encyclopédies
     *
     * @return array<string, array{title: string, description: string, icon: string, color: string, temperament: string, lifespan: string, needs: array<int, string>}>
     */
    public function getAllEncyclopedias(): array
    {
        return self::SPECIES_ENCYCLOPEDIA;
    }

    /**
     * Obtenir données enrichies avec cache (placeholder pour future intégration API)
     *
     * @return array{title: string, description: string, icon: string, color: string, temperament: string, lifespan: string, needs: array<int, string>}|null
     */
    public function getEnrichedEncyclopedia(string $espece): ?array
    {
        $cacheKey = 'encyclopedia_' . strtolower($espece);
        
        return $this->cache->get($cacheKey, function (CacheItemInterface $item) use ($espece) {
            $item->expiresAfter(self::CACHE_TTL);
            
            // Pour l'instant, retourne les données locales
            return $this->getEncyclopediaForSpecies($espece);
        });
    }
}
