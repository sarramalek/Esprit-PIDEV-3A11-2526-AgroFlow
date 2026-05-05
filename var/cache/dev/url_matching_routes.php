<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/2fa' => [[['_route' => '2fa_login', '_controller' => 'scheb_two_factor.form_controller::form'], null, null, null, false, false, null]],
        '/2fa_check' => [[['_route' => '2fa_login_check'], null, null, null, false, false, null]],
        '/_profiler' => [[['_route' => '_profiler_home', '_controller' => 'web_profiler.controller.profiler::homeAction'], null, null, null, true, false, null]],
        '/_profiler/search' => [[['_route' => '_profiler_search', '_controller' => 'web_profiler.controller.profiler::searchAction'], null, null, null, false, false, null]],
        '/_profiler/search_bar' => [[['_route' => '_profiler_search_bar', '_controller' => 'web_profiler.controller.profiler::searchBarAction'], null, null, null, false, false, null]],
        '/_profiler/phpinfo' => [[['_route' => '_profiler_phpinfo', '_controller' => 'web_profiler.controller.profiler::phpinfoAction'], null, null, null, false, false, null]],
        '/_profiler/xdebug' => [[['_route' => '_profiler_xdebug', '_controller' => 'web_profiler.controller.profiler::xdebugAction'], null, null, null, false, false, null]],
        '/_profiler/open' => [[['_route' => '_profiler_open_file', '_controller' => 'web_profiler.controller.profiler::openAction'], null, null, null, false, false, null]],
        '/about' => [[['_route' => 'app_about', '_controller' => 'App\\Controller\\AboutController::index'], null, null, null, false, false, null]],
        '/DashboardAdmin' => [[['_route' => 'admin_dashboard', '_controller' => 'App\\Controller\\AdminDashboardController::index'], null, null, null, false, false, null]],
        '/profile/update' => [[['_route' => 'profile_update', '_controller' => 'App\\Controller\\AdminDashboardController::profileUpdate'], null, ['POST' => 0], null, false, false, null]],
        '/admin/machines' => [[['_route' => 'admin_machines_index', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/machines/new' => [[['_route' => 'admin_machines_new', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/admin/materiels/maintenances' => [[['_route' => 'admin_maintenances_index', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/statistiques/bar' => [[['_route' => 'admin_maintenances_stats_bar', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::statsBar'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/export/pdf' => [[['_route' => 'admin_maintenances_export_pdf', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/new' => [[['_route' => 'admin_maintenances_new', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur' => [[['_route' => 'agri_home', '_controller' => 'App\\Controller\\AgriculteurDashboardController::index'], null, null, null, true, false, null]],
        '/agriculteur/animaux' => [[['_route' => 'agri_animaux', '_controller' => 'App\\Controller\\AgriculteurDashboardController::animaux'], null, null, null, false, false, null]],
        '/agriculteur/animaux/examens' => [[['_route' => 'agri_examens', '_controller' => 'App\\Controller\\AgriculteurDashboardController::examens'], null, null, null, false, false, null]],
        '/agriculteur/materiels/machines' => [
            [['_route' => 'agri_machine_index', '_controller' => 'App\\Controller\\AgriculteurDashboardController::machineIndex'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'agri_machines', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineIndex'], null, ['GET' => 0], null, false, false, null],
        ],
        '/agriculteur/evenements/participations' => [[['_route' => 'agri_participation_index', '_controller' => 'App\\Controller\\AgriculteurDashboardController::participations'], null, null, null, false, false, null]],
        '/agriculteur/evenements/index' => [[['_route' => 'agri_evenement_index', '_controller' => 'App\\Controller\\AgriculteurDashboardController::evenementIndex'], null, null, null, false, false, null]],
        '/agriculteur/abonnements/offres' => [[['_route' => 'agri_offre_front', '_controller' => 'App\\Controller\\AgriculteurDashboardController::offres'], null, null, null, false, false, null]],
        '/agriculteur/abonnements' => [[['_route' => 'agri_abonnement_front', '_controller' => 'App\\Controller\\AgriculteurDashboardController::abonnements'], null, null, null, false, false, null]],
        '/agriculteur/abonnements/pdf' => [[['_route' => 'agri_abonnement_pdf', '_controller' => 'App\\Controller\\AgriculteurDashboardController::abonnementPdf'], null, null, null, false, false, null]],
        '/agriculteur/taches' => [[['_route' => 'agri_taches', '_controller' => 'App\\Controller\\AgriculteurDashboardController::taches'], null, null, null, false, false, null]],
        '/agriculteur/ouvriers' => [
            [['_route' => 'agri_ouvriers', '_controller' => 'App\\Controller\\AgriculteurDashboardController::ouvriers'], null, null, null, false, false, null],
            [['_route' => 'app_ouvrier_index', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::index'], null, ['GET' => 0], null, false, false, null],
        ],
        '/agriculteur/profile/update' => [[['_route' => 'agri_profile_update', '_controller' => 'App\\Controller\\AgriculteurDashboardController::profileUpdate'], null, ['POST' => 0], null, false, false, null]],
        '/admin/animaux' => [[['_route' => 'admin_animaux_index', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminAnimauxController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/animaux/new' => [[['_route' => 'admin_animaux_new', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminAnimauxController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/admin/examens' => [[['_route' => 'admin_examens_index', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminExamenController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/examens/new' => [[['_route' => 'admin_examens_new', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminExamenController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/animaux' => [[['_route' => 'app_animaux_index', '_controller' => 'App\\Controller\\Animals\\AnimauxController::index'], null, ['GET' => 0], null, false, false, null]],
        '/animaux/stats' => [[['_route' => 'app_animaux_stats', '_controller' => 'App\\Controller\\Animals\\AnimauxController::stats'], null, ['GET' => 0], null, false, false, null]],
        '/animaux/new' => [[['_route' => 'app_animaux_new', '_controller' => 'App\\Controller\\Animals\\AnimauxController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/examen' => [[['_route' => 'app_examens_index', '_controller' => 'App\\Controller\\Animals\\ExamensController::index'], null, ['GET' => 0], null, false, false, null]],
        '/examen/stats' => [[['_route' => 'app_examens_stats', '_controller' => 'App\\Controller\\Animals\\ExamensController::stats'], null, ['GET' => 0], null, false, false, null]],
        '/examen/new' => [[['_route' => 'app_examens_new', '_controller' => 'App\\Controller\\Animals\\ExamensController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/reminders' => [[['_route' => 'app_reminders_dashboard', '_controller' => 'App\\Controller\\Animals\\ReminderController::dashboard'], null, ['GET' => 0], null, false, false, null]],
        '/login' => [[['_route' => 'app_login', '_controller' => 'App\\Controller\\AuthController::login'], null, null, null, false, false, null]],
        '/register' => [[['_route' => 'app_register', '_controller' => 'App\\Controller\\AuthController::register'], null, null, null, false, false, null]],
        '/check-session' => [[['_route' => 'app_check_session', '_controller' => 'App\\Controller\\AuthController::checkSession'], null, null, null, false, false, null]],
        '/logout' => [[['_route' => 'app_logout', '_controller' => 'App\\Controller\\AuthController::logout'], null, null, null, false, false, null]],
        '/chat' => [[['_route' => 'chat_index', '_controller' => 'App\\Controller\\ChatController::index'], null, ['GET' => 0], null, false, false, null]],
        '/chat/ask' => [[['_route' => 'chat_ask', '_controller' => 'App\\Controller\\ChatController::ask'], null, ['POST' => 0], null, false, false, null]],
        '/chat/clear' => [[['_route' => 'chat_clear', '_controller' => 'App\\Controller\\ChatController::clear'], null, ['POST' => 0], null, false, false, null]],
        '/chat/health' => [[['_route' => 'chat_chat_health', '_controller' => 'App\\Controller\\ChatController::health'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/evenements' => [[['_route' => 'agriculteur_evenement_index', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\EvenementController::index'], null, null, null, false, false, null]],
        '/agriculteur/participations' => [[['_route' => 'agriculteur_participation_index', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\ParticipationController::index'], null, null, null, true, false, null]],
        '/CategoriesEvenement' => [[['_route' => 'categorie_evenement_index', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::index'], null, null, null, false, false, null]],
        '/ajouterCategorieEvenement' => [[['_route' => 'categorie_evenement_ajouter', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::ajouter'], null, null, null, false, false, null]],
        '/ouvrier/evenements' => [
            [['_route' => 'ouvrier_evenement_index', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\EvenementController::index'], null, null, null, false, false, null],
            [['_route' => 'ouvrier_evenements', '_controller' => 'App\\Controller\\User\\OuvrierController::evenements'], null, null, null, false, false, null],
        ],
        '/evenements' => [[['_route' => 'evenement_index', '_controller' => 'App\\Controller\\Evenements\\EvenementController::index'], null, null, null, false, false, null]],
        '/evenements/ajouter' => [[['_route' => 'evenement_ajouter', '_controller' => 'App\\Controller\\Evenements\\EvenementController::ajouter'], null, null, null, false, false, null]],
        '/participations' => [[['_route' => 'participation_index', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::index'], null, null, null, false, false, null]],
        '/participations/ajouter' => [[['_route' => 'participation_ajouter', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::ajouter'], null, null, null, false, false, null]],
        '/' => [[['_route' => 'app_home', '_controller' => 'App\\Controller\\HomeController::index'], null, null, null, false, false, null]],
        '/agriculteur/materiels/machines/statistiques' => [[['_route' => 'agri_machine_statistiques', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineStatistiques'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/materiels/machines/new' => [[['_route' => 'agri_machine_new', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineNew'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/maintenances' => [[['_route' => 'agri_maintenances_index', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/export/excel' => [[['_route' => 'agri_maintenances_export_excel', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::exportExcel'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/export/pdf' => [[['_route' => 'agri_maintenances_export_pdf', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/new' => [[['_route' => 'agri_maintenances_new', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/ouvrier/stocks/categories' => [[['_route' => 'ouvrier_categories', '_controller' => 'App\\Controller\\OuvrierDashboardController::categories'], null, null, null, false, false, null]],
        '/ouvrier/stocks/produits' => [[['_route' => 'ouvrier_produits', '_controller' => 'App\\Controller\\OuvrierDashboardController::produits'], null, null, null, false, false, null]],
        '/ouvrier/profile/update' => [[['_route' => 'ouvrier_profile_update', '_controller' => 'App\\Controller\\OuvrierDashboardController::profileUpdate'], null, ['POST' => 0], null, false, false, null]],
        '/ouvrier/participations' => [[['_route' => 'ouvrier_participation_index', '_controller' => 'App\\Controller\\OuvrierDashboardController::participations'], null, null, null, false, false, null]],
        '/agri/plantes' => [[['_route' => 'agri_plantes', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agri/plantes/new' => [[['_route' => 'agri_plantes_new', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agri/rotations' => [[['_route' => 'agri_rotations', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agri/rotations/new' => [[['_route' => 'agri_rotations_new', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agri/terrains' => [[['_route' => 'agri_terrains', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agri/terrains/new' => [[['_route' => 'agri_terrains_new', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/api/ai-suggestion' => [[['_route' => 'api_ai_suggestion', '_controller' => 'App\\Controller\\Terrain\\AiSuggestionController::suggest'], null, ['POST' => 0], null, false, false, null]],
        '/ouvrier/plantes' => [[['_route' => 'ouvrier_plantes', '_controller' => 'App\\Controller\\Terrain\\OuvrierPlanteViewController::index'], null, ['GET' => 0], null, false, false, null]],
        '/ouvrier/plantes/conseils' => [[['_route' => 'ouvrier_plantes_conseils', '_controller' => 'App\\Controller\\Terrain\\OuvrierPlanteViewController::conseils'], null, ['POST' => 0], null, false, false, null]],
        '/plantes' => [[['_route' => 'admin_plantes', '_controller' => 'App\\Controller\\Terrain\\PlanteController::index'], null, ['GET' => 0], null, false, false, null]],
        '/plantes/new' => [[['_route' => 'admin_plantes_new', '_controller' => 'App\\Controller\\Terrain\\PlanteController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/rotations' => [[['_route' => 'admin_rotations', '_controller' => 'App\\Controller\\Terrain\\RotationController::index'], null, ['GET' => 0], null, false, false, null]],
        '/rotations/new' => [[['_route' => 'admin_rotations_new', '_controller' => 'App\\Controller\\Terrain\\RotationController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/rotations/agri/rotations' => [[['_route' => 'admin_rotationsagri_rotations', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriIndex'], null, ['GET' => 0], null, false, false, null]],
        '/rotations/agri/rotations/new' => [[['_route' => 'admin_rotationsagri_rotations_new', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriNew'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/terrains' => [[['_route' => 'admin_terrains', '_controller' => 'App\\Controller\\Terrain\\TerrainController::index'], null, ['GET' => 0], null, false, false, null]],
        '/terrains/new' => [[['_route' => 'admin_terrains_new', '_controller' => 'App\\Controller\\Terrain\\TerrainController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/api/workflow-plante' => [[['_route' => 'api_workflow_plante', '_controller' => 'App\\Controller\\Terrain\\WorkflowPlanteController::workflow'], null, ['POST' => 0], null, false, false, null]],
        '/admin/abonnements' => [[['_route' => 'admin_abonnements_index', '_controller' => 'App\\Controller\\User\\AbonnementController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/abonnements/export/pdf' => [[['_route' => 'admin_abonnements_export_pdf', '_controller' => 'App\\Controller\\User\\AbonnementController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/admin/abonnements/new' => [[['_route' => 'admin_abonnements_new', '_controller' => 'App\\Controller\\User\\AbonnementController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/abonnement/front' => [[['_route' => 'app_abonnement_front', '_controller' => 'App\\Controller\\User\\AbonnementFrontController::front'], null, ['GET' => 0], null, false, false, null]],
        '/admin/users' => [[['_route' => 'admin_users_list', '_controller' => 'App\\Controller\\User\\AdminUserController::list'], null, null, null, false, false, null]],
        '/admin/users/new' => [[['_route' => 'admin_users_new', '_controller' => 'App\\Controller\\User\\AdminUserController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/admin/users/export/excel' => [[['_route' => 'admin_users_excel', '_controller' => 'App\\Controller\\User\\AdminUserController::excel'], null, ['GET' => 0], null, false, false, null]],
        '/offre' => [[['_route' => 'app_offre_list', '_controller' => 'App\\Controller\\User\\OffreController::list'], null, ['GET' => 0], null, true, false, null]],
        '/offre/export/pdf' => [[['_route' => 'app_offre_export_pdf', '_controller' => 'App\\Controller\\User\\OffreController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/offre/new' => [[['_route' => 'app_offre_new', '_controller' => 'App\\Controller\\User\\OffreController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/offre/front' => [[['_route' => 'app_offre_front', '_controller' => 'App\\Controller\\User\\OffreFrontController::front'], null, ['GET' => 0], null, false, false, null]],
        '/ouvrier' => [[['_route' => 'ouvrier_home', '_controller' => 'App\\Controller\\User\\OuvrierController::home'], null, null, null, true, false, null]],
        '/ouvrier/taches' => [[['_route' => 'ouvrier_taches', '_controller' => 'App\\Controller\\User\\OuvrierController::taches'], null, null, null, false, false, null]],
        '/agriculteur/ouvriers/nouveau' => [[['_route' => 'app_ouvrier_new', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/tache' => [[['_route' => 'app_tache_index', '_controller' => 'App\\Controller\\User\\TacheController::index'], null, ['GET' => 0], null, true, false, null]],
        '/tache/export/pdf' => [[['_route' => 'app_tache_export_pdf', '_controller' => 'App\\Controller\\User\\TacheController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/tache/new' => [[['_route' => 'app_tache_new', '_controller' => 'App\\Controller\\User\\TacheController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/tache/front' => [[['_route' => 'app_tache_front', '_controller' => 'App\\Controller\\User\\TacheFrontController::front'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/tache/auto-assigner' => [[['_route' => 'app_tache_auto_assigner', '_controller' => 'App\\Controller\\User\\TacheFrontController::autoAssigner'], null, ['POST' => 0], null, false, false, null]],
        '/admin/gestion-stocks' => [[['_route' => 'admin_stock_index', '_controller' => 'App\\Controller\\stocks\\AdminStockController::index'], null, ['GET' => 0], null, true, false, null]],
        '/admin/gestion-stocks/ajouter-produit' => [[['_route' => 'admin_stock_new', '_controller' => 'App\\Controller\\stocks\\AdminStockController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/admin/gestion-stocks/export/pdf' => [[['_route' => 'admin_stock_pdf', '_controller' => 'App\\Controller\\stocks\\AdminStockController::exportPdf'], null, null, null, false, false, null]],
        '/admin/gestion-stocks/mouvements' => [[['_route' => 'admin_stock_mouvements', '_controller' => 'App\\Controller\\stocks\\AdminStockController::mouvements'], null, ['GET' => 0], null, false, false, null]],
        '/admin/gestion-stocks/categories' => [[['_route' => 'admin_categories_index', '_controller' => 'App\\Controller\\stocks\\Admin_CategorieController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/gestion-stocks/ajouter-categorie' => [[['_route' => 'admin_categorie_new', '_controller' => 'App\\Controller\\stocks\\Admin_CategorieController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/stocks' => [[['_route' => 'agri_produits', '_controller' => 'App\\Controller\\stocks\\ArticleController::index'], null, ['GET' => 0, 'POST' => 1], null, true, false, null]],
        '/agriculteur/stocks/new' => [[['_route' => 'app_article_new', '_controller' => 'App\\Controller\\stocks\\ArticleController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/stocks/categories/new' => [[['_route' => 'agri_categories_new', '_controller' => 'App\\Controller\\stocks\\CategorieController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/mouvements' => [[['_route' => 'app_mouvement_index', '_controller' => 'App\\Controller\\stocks\\MouvementController::historique'], null, null, null, false, false, null]],
        '/agriculteur/mouvements/rotation' => [[['_route' => 'app_mouvement_rotation', '_controller' => 'App\\Controller\\stocks\\MouvementController::rotation'], null, null, null, false, false, null]],
        '/agriculteur/mouvements/pdf' => [[['_route' => 'app_mouvement_pdf', '_controller' => 'App\\Controller\\stocks\\MouvementController::exportPdf'], null, null, null, false, false, null]],
        '/agriculteur/api/vision/analyze' => [[['_route' => 'api_vision_analyze', '_controller' => 'App\\Controller\\stocks\\VisionController::analyze'], null, ['POST' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_(?'
                    .'|error/(\\d+)(?:\\.([^/]++))?(*:38)'
                    .'|wdt/([^/]++)(*:57)'
                    .'|profiler/(?'
                        .'|font/([^/\\.]++)\\.woff2(*:98)'
                        .'|([^/]++)(?'
                            .'|/(?'
                                .'|search/results(*:134)'
                                .'|router(*:148)'
                                .'|exception(?'
                                    .'|(*:168)'
                                    .'|\\.css(*:181)'
                                .')'
                            .')'
                            .'|(*:191)'
                        .')'
                    .')'
                .')'
                .'|/a(?'
                    .'|dmin/(?'
                        .'|ma(?'
                            .'|chines/([^/]++)(?'
                                .'|(*:238)'
                                .'|/(?'
                                    .'|edit(*:254)'
                                    .'|delete(?'
                                        .'|(*:271)'
                                        .'|\\-confirm(*:288)'
                                    .')'
                                .')'
                            .')'
                            .'|teriels/maintenances/(?'
                                .'|(\\d+)(*:328)'
                                .'|(\\d+)/edit(*:346)'
                                .'|(\\d+)/delete(*:366)'
                            .')'
                        .')'
                        .'|a(?'
                            .'|nimaux/([^/]++)/(?'
                                .'|edit(*:403)'
                                .'|delete(*:417)'
                            .')'
                            .'|bonnements/([^/]++)(?'
                                .'|(*:448)'
                                .'|/(?'
                                    .'|pdf(*:463)'
                                    .'|edit(*:475)'
                                    .'|delete(*:489)'
                                .')'
                            .')'
                        .')'
                        .'|examens/([^/]++)/(?'
                            .'|edit(*:524)'
                            .'|delete(*:538)'
                        .')'
                        .'|users/(?'
                            .'|(\\d+)(*:561)'
                            .'|(\\d+)/ban(*:578)'
                            .'|(\\d+)/unban(*:597)'
                            .'|(\\d+)/delete(*:617)'
                            .'|(\\d+)/pdf(*:634)'
                            .'|(\\d+)/edit(*:652)'
                        .')'
                        .'|gestion\\-stocks/(?'
                            .'|([^/]++)/(?'
                                .'|edit(*:696)'
                                .'|delete(*:710)'
                            .')'
                            .'|mouvement/([^/]++)(*:737)'
                            .'|categorie/([^/]++)/(?'
                                .'|edit(*:771)'
                                .'|delete(*:785)'
                            .')'
                        .')'
                    .')'
                    .'|nimaux/([^/]++)(?'
                        .'|(*:814)'
                        .'|/(?'
                            .'|e(?'
                                .'|xport/(?'
                                    .'|card(*:843)'
                                    .'|medical(*:858)'
                                .')'
                                .'|dit(*:870)'
                            .')'
                            .'|match(*:884)'
                        .')'
                        .'|(*:893)'
                    .')'
                    .'|gri(?'
                        .'|culteur/(?'
                            .'|evenements/inscription/([^/]++)(*:950)'
                            .'|participations/(?'
                                .'|modifier/([^/]++)(*:993)'
                                .'|annuler/([^/]++)(*:1017)'
                            .')'
                            .'|m(?'
                                .'|a(?'
                                    .'|teriels/machines/(?'
                                        .'|(\\d+)(*:1060)'
                                        .'|(\\d+)/edit(*:1079)'
                                        .'|(\\d+)/delete(*:1100)'
                                    .')'
                                    .'|intenances/([^/]++)(?'
                                        .'|(*:1132)'
                                        .'|/(?'
                                            .'|edit(*:1149)'
                                            .'|delete(*:1164)'
                                        .')'
                                    .')'
                                .')'
                                .'|ouvements/new/([^/]++)(*:1198)'
                            .')'
                            .'|abonnement/front/pdf/([^/]++)(*:1237)'
                            .'|o(?'
                                .'|ffre/souscrire/([^/]++)(*:1273)'
                                .'|uvriers/(?'
                                    .'|([^/]++)/(?'
                                        .'|modifier(*:1313)'
                                        .'|supprimer(*:1331)'
                                        .'|taches(?'
                                            .'|(*:1349)'
                                            .'|/ajouter(*:1366)'
                                        .')'
                                    .')'
                                    .'|tache/([^/]++)/(?'
                                        .'|etat(*:1399)'
                                        .'|supprimer(*:1417)'
                                    .')'
                                .')'
                            .')'
                            .'|tache/(?'
                                .'|front/agriculteur/([^/]++)(*:1464)'
                                .'|terrain/([^/]++)/(?'
                                    .'|ouvriers(*:1501)'
                                    .'|assigner\\-ouvrier(*:1527)'
                                .')'
                                .'|ouvrier/([^/]++)/desassigner(*:1565)'
                            .')'
                            .'|stocks/(?'
                                .'|([^/]++)(?'
                                    .'|/edit(*:1601)'
                                    .'|(*:1610)'
                                .')'
                                .'|mouvements/new/([^/]++)(*:1643)'
                                .'|([^/]++)/(?'
                                    .'|qr\\-code(?'
                                        .'|(*:1675)'
                                        .'|/(?'
                                            .'|download(*:1696)'
                                            .'|view(*:1709)'
                                        .')'
                                    .')'
                                    .'|details(*:1727)'
                                    .'|scan\\-redirect(*:1750)'
                                .')'
                                .'|categories(?'
                                    .'|(*:1773)'
                                    .'|/([^/]++)(?'
                                        .'|/edit(*:1799)'
                                        .'|(*:1808)'
                                    .')'
                                .')'
                            .')'
                        .')'
                        .'|/(?'
                            .'|plantes/([^/]++)(?'
                                .'|/(?'
                                    .'|edit(*:1852)'
                                    .'|delete(*:1867)'
                                .')'
                                .'|(*:1877)'
                            .')'
                            .'|rotations/([^/]++)(?'
                                .'|/(?'
                                    .'|edit(*:1916)'
                                    .'|delete(*:1931)'
                                .')'
                                .'|(*:1941)'
                            .')'
                            .'|terrains/([^/]++)(?'
                                .'|/(?'
                                    .'|edit(*:1979)'
                                    .'|delete(*:1994)'
                                    .'|certificat\\-propriete(*:2024)'
                                .')'
                                .'|(*:2034)'
                            .')'
                        .')'
                    .')'
                .')'
                .'|/e(?'
                    .'|xamen/([^/]++)(?'
                        .'|(*:2069)'
                        .'|/edit(*:2083)'
                        .'|(*:2092)'
                    .')'
                    .'|venements/(?'
                        .'|modifier/([^/]++)(*:2132)'
                        .'|supprimer/([^/]++)(*:2159)'
                    .')'
                .')'
                .'|/modifierCategorieEvenement/([^/]++)(*:2206)'
                .'|/s(?'
                    .'|upprimerCategorieEvenement/([^/]++)(*:2255)'
                    .'|et\\-locale/([^/]++)(*:2283)'
                .')'
                .'|/o(?'
                    .'|uvrier/(?'
                        .'|evenements/inscription/([^/]++)(*:2339)'
                        .'|participations/(?'
                            .'|modifier/([^/]++)(*:2383)'
                            .'|annuler/([^/]++)(*:2408)'
                        .')'
                        .'|stocks/(?'
                            .'|produits/(?'
                                .'|(\\d+)(*:2445)'
                                .'|(\\d+)/sortie(*:2466)'
                                .'|(\\d+)/mouvements(*:2491)'
                            .')'
                            .'|mouvements/(\\d+)/modifier(*:2526)'
                        .')'
                        .'|tache/([^/]++)/statut/([^/]++)(*:2566)'
                    .')'
                    .'|ffre/([^/]++)(?'
                        .'|(*:2592)'
                        .'|/(?'
                            .'|pdf(*:2608)'
                            .'|edit(*:2621)'
                            .'|delete(*:2636)'
                        .')'
                    .')'
                .')'
                .'|/p(?'
                    .'|articipations/(?'
                        .'|modifier/([^/]++)(*:2687)'
                        .'|supprimer/([^/]++)(*:2714)'
                    .')'
                    .'|lantes/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:2750)'
                            .'|delete(*:2765)'
                        .')'
                        .'|(*:2775)'
                    .')'
                .')'
                .'|/rotations/(?'
                    .'|([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:2819)'
                            .'|delete(*:2834)'
                        .')'
                        .'|(*:2844)'
                    .')'
                    .'|agri/rotations/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:2888)'
                            .'|delete(*:2903)'
                        .')'
                        .'|(*:2913)'
                    .')'
                .')'
                .'|/t(?'
                    .'|errains/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:2956)'
                            .'|delete(*:2971)'
                        .')'
                        .'|(*:2981)'
                    .')'
                    .'|ache/([^/]++)(?'
                        .'|(*:3007)'
                        .'|/(?'
                            .'|pdf(*:3023)'
                            .'|edit(*:3036)'
                            .'|delete(*:3051)'
                        .')'
                    .')'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        38 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        57 => [[['_route' => '_wdt', '_controller' => 'web_profiler.controller.profiler::toolbarAction'], ['token'], null, null, false, true, null]],
        98 => [[['_route' => '_profiler_font', '_controller' => 'web_profiler.controller.profiler::fontAction'], ['fontName'], null, null, false, false, null]],
        134 => [[['_route' => '_profiler_search_results', '_controller' => 'web_profiler.controller.profiler::searchResultsAction'], ['token'], null, null, false, false, null]],
        148 => [[['_route' => '_profiler_router', '_controller' => 'web_profiler.controller.router::panelAction'], ['token'], null, null, false, false, null]],
        168 => [[['_route' => '_profiler_exception', '_controller' => 'web_profiler.controller.exception_panel::body'], ['token'], null, null, false, false, null]],
        181 => [[['_route' => '_profiler_exception_css', '_controller' => 'web_profiler.controller.exception_panel::stylesheet'], ['token'], null, null, false, false, null]],
        191 => [[['_route' => '_profiler', '_controller' => 'web_profiler.controller.profiler::panelAction'], ['token'], null, null, false, true, null]],
        238 => [[['_route' => 'admin_machines_show', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        254 => [[['_route' => 'admin_machines_edit', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        271 => [[['_route' => 'admin_machines_delete', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        288 => [[['_route' => 'admin_machines_delete_confirm', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::deleteConfirm'], ['id'], ['GET' => 0], null, false, false, null]],
        328 => [[['_route' => 'admin_maintenances_show', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        346 => [[['_route' => 'admin_maintenances_edit', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        366 => [[['_route' => 'admin_maintenances_delete', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        403 => [[['_route' => 'admin_animaux_edit', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminAnimauxController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        417 => [[['_route' => 'admin_animaux_delete', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminAnimauxController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        448 => [[['_route' => 'admin_abonnements_show', '_controller' => 'App\\Controller\\User\\AbonnementController::show'], ['idAbonn'], ['GET' => 0], null, false, true, null]],
        463 => [[['_route' => 'admin_abonnements_pdf', '_controller' => 'App\\Controller\\User\\AbonnementController::pdf'], ['idAbonn'], ['GET' => 0], null, false, false, null]],
        475 => [[['_route' => 'admin_abonnements_edit', '_controller' => 'App\\Controller\\User\\AbonnementController::edit'], ['idAbonn'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        489 => [[['_route' => 'admin_abonnements_delete', '_controller' => 'App\\Controller\\User\\AbonnementController::delete'], ['idAbonn'], ['POST' => 0], null, false, false, null]],
        524 => [[['_route' => 'admin_examens_edit', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminExamenController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        538 => [[['_route' => 'admin_examens_delete', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminExamenController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        561 => [[['_route' => 'admin_users_show', '_controller' => 'App\\Controller\\User\\AdminUserController::show'], ['cin'], ['GET' => 0], null, false, true, null]],
        578 => [[['_route' => 'admin_users_ban', '_controller' => 'App\\Controller\\User\\AdminUserController::ban'], ['cin'], ['POST' => 0], null, false, false, null]],
        597 => [[['_route' => 'admin_users_unban', '_controller' => 'App\\Controller\\User\\AdminUserController::unban'], ['cin'], ['POST' => 0], null, false, false, null]],
        617 => [[['_route' => 'admin_users_delete', '_controller' => 'App\\Controller\\User\\AdminUserController::delete'], ['cin'], ['POST' => 0], null, false, false, null]],
        634 => [[['_route' => 'admin_users_pdf', '_controller' => 'App\\Controller\\User\\AdminUserController::pdf'], ['cin'], ['GET' => 0], null, false, false, null]],
        652 => [[['_route' => 'admin_users_edit', '_controller' => 'App\\Controller\\User\\AdminUserController::edit'], ['cin'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        696 => [[['_route' => 'admin_stock_edit', '_controller' => 'App\\Controller\\stocks\\AdminStockController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        710 => [[['_route' => 'admin_stock_delete', '_controller' => 'App\\Controller\\stocks\\AdminStockController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        737 => [[['_route' => 'admin_stock_mouvement', '_controller' => 'App\\Controller\\stocks\\AdminStockController::gestionStock'], ['id'], ['POST' => 0], null, false, true, null]],
        771 => [[['_route' => 'admin_categorie_edit', '_controller' => 'App\\Controller\\stocks\\Admin_CategorieController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        785 => [[['_route' => 'admin_categorie_delete', '_controller' => 'App\\Controller\\stocks\\Admin_CategorieController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        814 => [[['_route' => 'app_animaux_show', '_controller' => 'App\\Controller\\Animals\\AnimauxController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        843 => [[['_route' => 'app_animaux_export_card', '_controller' => 'App\\Controller\\Animals\\AnimauxController::exportCard'], ['id'], ['GET' => 0], null, false, false, null]],
        858 => [[['_route' => 'app_animaux_export_medical', '_controller' => 'App\\Controller\\Animals\\AnimauxController::exportMedical'], ['id'], ['GET' => 0], null, false, false, null]],
        870 => [[['_route' => 'app_animaux_edit', '_controller' => 'App\\Controller\\Animals\\AnimauxController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        884 => [[['_route' => 'app_animaux_match', '_controller' => 'App\\Controller\\Animals\\AnimauxController::match'], ['id'], ['GET' => 0], null, false, false, null]],
        893 => [[['_route' => 'app_animaux_delete', '_controller' => 'App\\Controller\\Animals\\AnimauxController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        950 => [[['_route' => 'agriculteur_evenement_inscription', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\EvenementController::inscrire'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        993 => [[['_route' => 'agriculteur_participation_modifier', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\ParticipationController::modifier'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        1017 => [[['_route' => 'agriculteur_participation_annuler', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\ParticipationController::annuler'], ['id'], ['POST' => 0], null, false, true, null]],
        1060 => [[['_route' => 'agri_machine_show', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineShow'], ['id'], ['GET' => 0], null, false, true, null]],
        1079 => [[['_route' => 'agri_machine_edit', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineEdit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1100 => [[['_route' => 'agri_machine_delete', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineDelete'], ['id'], ['POST' => 0], null, false, false, null]],
        1132 => [[['_route' => 'agri_maintenances_show', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1149 => [[['_route' => 'agri_maintenances_edit', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1164 => [[['_route' => 'agri_maintenances_delete', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1198 => [[['_route' => 'app_mouvement_new_alias', '_controller' => 'App\\Controller\\stocks\\MouvementController::gestionStockAlias'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        1237 => [[['_route' => 'app_abonnement_pdf', '_controller' => 'App\\Controller\\User\\AbonnementFrontController::pdf'], ['id'], ['GET' => 0], null, false, true, null]],
        1273 => [[['_route' => 'app_offre_souscrire', '_controller' => 'App\\Controller\\User\\OffreFrontController::souscrire'], ['id'], ['POST' => 0], null, false, true, null]],
        1313 => [[['_route' => 'app_ouvrier_edit', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::edit'], ['cin'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1331 => [[['_route' => 'app_ouvrier_delete', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::deleteOuvrier'], ['cin'], ['POST' => 0], null, false, false, null]],
        1349 => [[['_route' => 'app_ouvrier_taches', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::taches'], ['cin'], ['GET' => 0], null, false, false, null]],
        1366 => [[['_route' => 'app_ouvrier_tache_add', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::ajouterTache'], ['cin'], ['POST' => 0], null, false, false, null]],
        1399 => [[['_route' => 'app_tache_update_etat', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::updateEtatTache'], ['id'], ['POST' => 0], null, false, false, null]],
        1417 => [[['_route' => 'app_ouvrier_tache_delete', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::supprimerTache'], ['id'], ['POST' => 0], null, false, false, null]],
        1464 => [[['_route' => 'app_tache_by_ouvrier', '_controller' => 'App\\Controller\\User\\TacheFrontController::tachesByOuvrier'], ['cin'], ['GET' => 0], null, false, true, null]],
        1501 => [[['_route' => 'app_ouvriers_by_terrain', '_controller' => 'App\\Controller\\User\\TacheFrontController::ouvriersByTerrain'], ['idTerrain'], ['GET' => 0], null, false, false, null]],
        1527 => [[['_route' => 'app_assigner_ouvrier_terrain', '_controller' => 'App\\Controller\\User\\TacheFrontController::assignerOuvrierTerrain'], ['idTerrain'], ['POST' => 0], null, false, false, null]],
        1565 => [[['_route' => 'app_desassigner_ouvrier', '_controller' => 'App\\Controller\\User\\TacheFrontController::desassignerOuvrier'], ['cin'], ['POST' => 0], null, false, false, null]],
        1601 => [[['_route' => 'app_article_edit', '_controller' => 'App\\Controller\\stocks\\ArticleController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1610 => [[['_route' => 'app_article_delete', '_controller' => 'App\\Controller\\stocks\\ArticleController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        1643 => [[['_route' => 'app_mouvement_new', '_controller' => 'App\\Controller\\stocks\\ArticleController::gestionStock'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        1675 => [[['_route' => 'article_qr_code', '_controller' => 'App\\Controller\\stocks\\ArticleController::generateQRCode'], ['id'], ['GET' => 0], null, false, false, null]],
        1696 => [[['_route' => 'article_qr_code_download', '_controller' => 'App\\Controller\\stocks\\ArticleController::downloadQRCode'], ['id'], ['GET' => 0], null, false, false, null]],
        1709 => [[['_route' => 'article_qr_code_view', '_controller' => 'App\\Controller\\stocks\\ArticleController::viewQRCode'], ['id'], ['GET' => 0], null, false, false, null]],
        1727 => [[['_route' => 'app_article_show', '_controller' => 'App\\Controller\\stocks\\ArticleController::show'], ['id'], ['GET' => 0], null, false, false, null]],
        1750 => [[['_route' => 'article_scan_redirect', '_controller' => 'App\\Controller\\stocks\\ArticleController::scanRedirect'], ['id'], ['GET' => 0], null, false, false, null]],
        1773 => [[['_route' => 'agri_categories', '_controller' => 'App\\Controller\\stocks\\CategorieController::index'], [], ['GET' => 0], null, true, false, null]],
        1799 => [[['_route' => 'agri_categories_edit', '_controller' => 'App\\Controller\\stocks\\CategorieController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1808 => [[['_route' => 'agri_categories_delete', '_controller' => 'App\\Controller\\stocks\\CategorieController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        1852 => [[['_route' => 'agri_plantes_edit', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1867 => [[['_route' => 'agri_plantes_delete', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1877 => [[['_route' => 'agri_plantes_show', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1916 => [[['_route' => 'agri_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1931 => [[['_route' => 'agri_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1941 => [[['_route' => 'agri_rotations_show', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1979 => [[['_route' => 'agri_terrains_edit', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1994 => [[['_route' => 'agri_terrains_delete', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        2024 => [[['_route' => 'agri_terrains_certificat_propriete', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::certificatPropriete'], ['id'], ['GET' => 0], null, false, false, null]],
        2034 => [[['_route' => 'agri_terrains_show', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2069 => [[['_route' => 'app_examens_show', '_controller' => 'App\\Controller\\Animals\\ExamensController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2083 => [[['_route' => 'app_examens_edit', '_controller' => 'App\\Controller\\Animals\\ExamensController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2092 => [[['_route' => 'app_examens_delete', '_controller' => 'App\\Controller\\Animals\\ExamensController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        2132 => [[['_route' => 'evenement_modifier', '_controller' => 'App\\Controller\\Evenements\\EvenementController::modifier'], ['id'], null, null, false, true, null]],
        2159 => [[['_route' => 'evenement_supprimer', '_controller' => 'App\\Controller\\Evenements\\EvenementController::supprimer'], ['id'], null, null, false, true, null]],
        2206 => [[['_route' => 'categorie_evenement_modifier', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::modifier'], ['id'], null, null, false, true, null]],
        2255 => [[['_route' => 'categorie_evenement_supprimer', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::supprimer'], ['id'], null, null, false, true, null]],
        2283 => [[['_route' => 'app_set_locale', '_controller' => 'App\\Controller\\LocaleController::setLocale'], ['locale'], ['GET' => 0], null, false, true, null]],
        2339 => [[['_route' => 'ouvrier_evenement_inscription', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\EvenementController::inscrire'], ['id'], null, null, false, true, null]],
        2383 => [[['_route' => 'ouvrier_participation_modifier', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\ParticipationController::modifier'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        2408 => [[['_route' => 'ouvrier_participation_annuler', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\ParticipationController::annuler'], ['id'], ['POST' => 0], null, false, true, null]],
        2445 => [[['_route' => 'ouvrier_article_show', '_controller' => 'App\\Controller\\OuvrierDashboardController::articleShow'], ['id'], ['GET' => 0], null, false, true, null]],
        2466 => [[['_route' => 'ouvrier_article_sortie', '_controller' => 'App\\Controller\\OuvrierDashboardController::sortie'], ['id'], ['POST' => 0], null, false, false, null]],
        2491 => [[['_route' => 'ouvrier_article_mouvements', '_controller' => 'App\\Controller\\OuvrierDashboardController::mouvements'], ['id'], ['GET' => 0], null, false, false, null]],
        2526 => [[['_route' => 'ouvrier_mouvement_modifier', '_controller' => 'App\\Controller\\OuvrierDashboardController::modifierMouvement'], ['id'], ['POST' => 0], null, false, false, null]],
        2566 => [[['_route' => 'ouvrier_tache_statut', '_controller' => 'App\\Controller\\User\\OuvrierController::changerStatut'], ['id', 'statut'], null, null, false, true, null]],
        2592 => [[['_route' => 'app_offre_show', '_controller' => 'App\\Controller\\User\\OffreController::show'], ['idOffres'], ['GET' => 0], null, false, true, null]],
        2608 => [[['_route' => 'app_offre_pdf', '_controller' => 'App\\Controller\\User\\OffreController::pdf'], ['idOffres'], ['GET' => 0], null, false, false, null]],
        2621 => [[['_route' => 'app_offre_edit', '_controller' => 'App\\Controller\\User\\OffreController::edit'], ['idOffres'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2636 => [[['_route' => 'app_offre_delete', '_controller' => 'App\\Controller\\User\\OffreController::delete'], ['idOffres'], ['POST' => 0], null, false, false, null]],
        2687 => [[['_route' => 'participation_modifier', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::modifier'], ['id'], null, null, false, true, null]],
        2714 => [[['_route' => 'participation_supprimer', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::supprimer'], ['id'], null, null, false, true, null]],
        2750 => [[['_route' => 'admin_plantes_edit', '_controller' => 'App\\Controller\\Terrain\\PlanteController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2765 => [[['_route' => 'admin_plantes_delete', '_controller' => 'App\\Controller\\Terrain\\PlanteController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        2775 => [[['_route' => 'admin_plantes_show', '_controller' => 'App\\Controller\\Terrain\\PlanteController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2819 => [[['_route' => 'admin_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\RotationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2834 => [[['_route' => 'admin_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\RotationController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        2844 => [[['_route' => 'admin_rotations_show', '_controller' => 'App\\Controller\\Terrain\\RotationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2888 => [[['_route' => 'admin_rotationsagri_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriEdit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2903 => [[['_route' => 'admin_rotationsagri_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriDelete'], ['id'], ['POST' => 0], null, false, false, null]],
        2913 => [[['_route' => 'admin_rotationsagri_rotations_show', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriShow'], ['id'], ['GET' => 0], null, false, true, null]],
        2956 => [[['_route' => 'admin_terrains_edit', '_controller' => 'App\\Controller\\Terrain\\TerrainController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2971 => [[['_route' => 'admin_terrains_delete', '_controller' => 'App\\Controller\\Terrain\\TerrainController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        2981 => [[['_route' => 'admin_terrains_show', '_controller' => 'App\\Controller\\Terrain\\TerrainController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        3007 => [[['_route' => 'app_tache_show', '_controller' => 'App\\Controller\\User\\TacheController::show'], ['idTache'], ['GET' => 0], null, false, true, null]],
        3023 => [[['_route' => 'app_tache_pdf', '_controller' => 'App\\Controller\\User\\TacheController::pdf'], ['idTache'], ['GET' => 0], null, false, false, null]],
        3036 => [[['_route' => 'app_tache_edit', '_controller' => 'App\\Controller\\User\\TacheController::edit'], ['idTache'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        3051 => [
            [['_route' => 'app_tache_delete', '_controller' => 'App\\Controller\\User\\TacheController::delete'], ['idTache'], ['POST' => 0], null, false, false, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
