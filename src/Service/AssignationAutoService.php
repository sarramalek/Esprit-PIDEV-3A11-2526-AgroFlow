<?php

namespace App\Service;

use App\Entity\User\Tache;
use App\Entity\User\User;
use App\Repository\User\TacheRepository;
use App\Repository\Terrain\TerrainRepository;

class AssignationAutoService
{
    public function __construct(
        private TacheRepository  $tacheRepo,
        private TerrainRepository $terrainRepo,
    ) {}

    /**
     * Choisit le meilleur ouvrier parmi ceux de l'agriculteur.
     * Priorité :
     *   1. Ouvriers disponibles à la date (pas de tâche ce jour-là)
     *      → celui qui a le moins de tâches actives
     *   2. Si tous occupés → celui qui a le moins de tâches actives (tous confondus)
     */
    public function choisirOuvrier(User $agriculteur, ?\DateTime $date): ?User
    {
        // Récupère tous les ouvriers de l'agriculteur
        $ouvriers = $this->terrainRepo->findOuvriersDeAgriculteur($agriculteur->getCin());

        if (empty($ouvriers)) {
            return null;
        }

        // Calcule le score de chaque ouvrier
        $scores = [];
        foreach ($ouvriers as $ouvrier) {
            $nbTaches     = $this->tacheRepo->countTachesActives($ouvrier);
            $conflit      = $date && $this->tacheRepo->hasConflitDate($ouvrier, $date);
            $scores[] = [
                'ouvrier'  => $ouvrier,
                'nbTaches' => $nbTaches,
                'conflit'  => $conflit,
            ];
        }

        // Sépare disponibles / occupés
        $disponibles = array_filter($scores, fn($s) => !$s['conflit']);
        $occupes     = array_filter($scores, fn($s) =>  $s['conflit']);

        // Trie par nombre de tâches croissant
        $trier = function (array &$liste) {
            usort($liste, fn($a, $b) => $a['nbTaches'] <=> $b['nbTaches']);
        };

        if (!empty($disponibles)) {
            $disponibles = array_values($disponibles);
            $trier($disponibles);
            return $disponibles[0]['ouvrier'];
        }

        // Aucun disponible → minimum de tâches parmi tous
        $tous = array_values($scores);
        $trier($tous);
        return $tous[0]['ouvrier'];
    }
}