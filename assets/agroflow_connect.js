/**
 * AgroFlow Connect - Partners Database & AI Chat
 * Données des vétérinaires et acheteurs avec coordonnées GPS
 */

const AGROFLOW_CONNECT = {
    // Coordonnées par défaut de Tunisie (Tunis)
    DEFAULT_LAT: 36.8065,
    DEFAULT_LNG: 10.1615,

    // Liste des vétérinaires
    vetecinaires: [
        {
            id: 'vet_1',
            nom: 'Dr. Ali Zouhair',
            specialite: ['Cheval', 'Chèvre', 'Mouton'],
            adresse: 'Rue Ahmed Ben Salem, Tunis',
            telephone: '+216 71 245 891',
            lat: 36.8100,
            lng: 10.1700,
            rating: 4.8,
            urgence: true,
            horaires: '9h-19h (24h urgence)',
            couleur: '#27ae60'
        },
        {
            id: 'vet_2',
            nom: 'Clinique Vétérinaire El-Amal',
            specialite: ['Chien', 'Chat', 'Cheval'],
            adresse: 'Avenue Bourguiba, Sfax',
            telephone: '+216 74 221 350',
            lat: 34.7405,
            lng: 10.7603,
            rating: 4.5,
            urgence: true,
            horaires: '8h-20h (urgence 24h)',
            couleur: '#3498db'
        },
        {
            id: 'vet_3',
            nom: 'Dr. Fatima Khalil',
            specialite: ['Vache', 'Chèvre', 'Mouton'],
            adresse: 'Zone Agricole, Béja',
            telephone: '+216 78 450 123',
            lat: 36.7372,
            lng: 9.1844,
            rating: 4.6,
            urgence: false,
            horaires: '7h-17h (lun-ven)',
            couleur: '#e74c3c'
        },
        {
            id: 'vet_4',
            nom: 'Clinique Moderne Vétérinaire',
            specialite: ['Chien', 'Chat', 'Oiseau'],
            adresse: 'Centre Commercial, Sousse',
            telephone: '+216 73 189 450',
            lat: 35.8256,
            lng: 10.6369,
            rating: 4.7,
            urgence: true,
            horaires: '10h-21h (24h urgence)',
            couleur: '#9b59b6'
        },
        {
            id: 'vet_5',
            nom: 'Cabinet Vétérinaire Ezzahra',
            specialite: ['Vache', 'Cheval', 'Chèvre'],
            adresse: 'Route de Carthage, Ariana',
            telephone: '+216 71 678 234',
            lat: 36.8535,
            lng: 10.1897,
            rating: 4.4,
            urgence: false,
            horaires: '8h-17h (lun-sam)',
            couleur: '#f39c12'
        }
    ],

    // Liste des acheteurs/partenaires
    acheteurs: [
        {
            id: 'buyer_1',
            nom: 'Coopérative Agricole Skhira',
            type: 'Coopérative',
            specialite: ['Cheval', 'Âne'],
            adresse: 'Zone Portuaire, Sfax',
            telephone: '+216 74 567 890',
            lat: 34.7350,
            lng: 10.7500,
            rating: 4.3,
            horaires: 'Lun-ven 8h-16h',
            couleur: '#16a085'
        },
        {
            id: 'buyer_2',
            nom: 'Ferme Moderne SARL',
            type: 'Ferme Élevage',
            specialite: ['Vache', 'Chèvre', 'Mouton'],
            adresse: 'Gouvernorat de Bizerte',
            telephone: '+216 72 234 567',
            lat: 37.2744,
            lng: 9.8739,
            rating: 4.6,
            horaires: 'Lun-ven 7h-17h',
            couleur: '#27ae60'
        },
        {
            id: 'buyer_3',
            nom: 'Import-Export Viande TN',
            type: 'Acheteur Gros',
            specialite: ['Chèvre', 'Mouton', 'Vache'],
            adresse: 'Port de Tunis',
            telephone: '+216 71 890 123',
            lat: 36.8100,
            lng: 10.1800,
            rating: 4.4,
            horaires: 'Lun-ven 9h-17h',
            couleur: '#e67e22'
        },
        {
            id: 'buyer_4',
            nom: 'Élevage Premium Dkhila',
            type: 'Reproducteur',
            specialite: ['Cheval Pur-Sang', 'Cheval Arabe'],
            adresse: 'Gouvernorat de Kairouan',
            telephone: '+216 76 345 789',
            lat: 35.6711,
            lng: 10.1035,
            rating: 4.9,
            horaires: 'Tous les jours',
            couleur: '#8e44ad'
        },
        {
            id: 'buyer_5',
            nom: 'Marché Central des Animaux',
            type: 'Marché',
            specialite: ['Tous types d\'animaux'],
            adresse: 'Grand Marché, Gafsa',
            telephone: '+216 75 223 456',
            lat: 34.4265,
            lng: 8.7841,
            rating: 4.2,
            horaires: 'Jeu-dim 6h-12h',
            couleur: '#c0392b'
        }
    ],

    /**
     * Filtrer les vétérinaires par espèce animale
     */
    getVeterinairesForAnimal(espece) {
        return this.vetecinaires.filter(v => 
            v.specialite.some(s => s.toLowerCase() === espece.toLowerCase())
        );
    },

    /**
     * Filtrer les acheteurs par espèce animale
     */
    getAcheteursForAnimal(espece) {
        return this.acheteurs.filter(a => 
            a.specialite.some(s => s.toLowerCase().includes(espece.toLowerCase()))
        );
    },

    /**
     * Calculer la distance entre deux points GPS (formule Haversine)
     */
    calculerDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Rayon de la Terre en km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = 
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return (R * c).toFixed(1);
    },

    /**
     * Trier les partenaires par distance
     */
    trierParDistance(partenaires, userLat, userLng) {
        return partenaires.map(p => ({
            ...p,
            distance: this.calculerDistance(userLat, userLng, p.lat, p.lng)
        })).sort((a, b) => parseFloat(a.distance) - parseFloat(b.distance));
    },

    /**
     * Générer une réponse IA intelligente basée sur l'animal et le contexte
     */
    generateAIResponse(question, animalNom, animalEspece, userLat, userLng) {
        const lowerQuestion = question.toLowerCase();
        let response = '';
        let partenaires = [];
        let type = 'veterinaire';

        // Détecter l'intention (vétérinaire ou acheteur)
        if (lowerQuestion.includes('vétérinaire') || lowerQuestion.includes('vet') || 
            lowerQuestion.includes('infection') || lowerQuestion.includes('maladie') ||
            lowerQuestion.includes('blessure') || lowerQuestion.includes('urgence') ||
            lowerQuestion.includes('soin') || lowerQuestion.includes('consultation')) {
            type = 'veterinaire';
            partenaires = this.getVeterinairesForAnimal(animalEspece);
        } else if (lowerQuestion.includes('acheteur') || lowerQuestion.includes('vendre') ||
                   lowerQuestion.includes('vente') || lowerQuestion.includes('partenaire') ||
                   lowerQuestion.includes('coopérative') || lowerQuestion.includes('ferme')) {
            type = 'acheteur';
            partenaires = this.getAcheteursForAnimal(animalEspece);
        } else {
            // Par défaut, proposer les deux
            partenaires = [...this.getVeterinairesForAnimal(animalEspece), 
                          ...this.getAcheteursForAnimal(animalEspece)];
        }

        // Trier par distance
        if (userLat && userLng) {
            partenaires = this.trierParDistance(partenaires, userLat, userLng);
        }

        // Générer la réponse
        if (lowerQuestion.includes('urgence') || lowerQuestion.includes('urgent')) {
            response = `🚨 <strong>Alerte Urgence pour ${animalNom} (${animalEspece})</strong><br>`;
            const vetsUrgence = partenaires.filter(p => p.urgence && p.telephone);
            if (vetsUrgence.length > 0) {
                response += `J'ai identifié <strong>${vetsUrgence.length} vétérinaires d'urgence</strong> disponibles 24h/24. `;
                response += `Le plus proche est à <strong>${vetsUrgence[0].distance} km</strong>. Appelez immédiatement au <strong>${vetsUrgence[0].telephone}</strong>!`;
            }
        } else {
            response = `✅ <strong>Pour ${animalNom} (${animalEspece})</strong><br>`;
            if (partenaires.length > 0) {
                const top3 = partenaires.slice(0, 3);
                response += `J'ai trouvé <strong>${top3.length} spécialistes ${animalEspece.toLowerCase()}</strong> près de vous:<br><br>`;
                top3.forEach((p, idx) => {
                    response += `<strong>${idx + 1}. ${p.nom}</strong><br>`;
                    response += `📍 À ${p.distance} km • ⭐ ${p.rating}/5<br>`;
                    response += `📞 ${p.telephone}<br>`;
                    if (p.horaires) response += `🕐 ${p.horaires}<br><br>`;
                });
            } else {
                response += `Aucun spécialiste ${animalEspece.toLowerCase()} trouvé dans la base. Veuillez élargir votre recherche.`;
            }
        }

        return {
            response: response,
            partenaires: partenaires.slice(0, 3),
            type: type
        };
    }
};

// Export pour utilisation
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AGROFLOW_CONNECT;
}
