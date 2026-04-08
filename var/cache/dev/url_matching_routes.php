<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
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
        '/agriculteur/stocks/categories' => [[['_route' => 'agri_categories', '_controller' => 'App\\Controller\\AgriculteurDashboardController::categories'], null, null, null, false, false, null]],
        '/agriculteur/stocks/produits' => [[['_route' => 'agri_produits', '_controller' => 'App\\Controller\\AgriculteurDashboardController::produits'], null, null, null, false, false, null]],
        '/agriculteur/materiels/machines' => [
            [['_route' => 'agri_machine_index', '_controller' => 'App\\Controller\\AgriculteurDashboardController::machineIndex'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'agri_machines', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineIndex'], null, ['GET' => 0], null, false, false, null],
        ],
        '/agriculteur/materiels/maintenances' => [[['_route' => 'agri_maintenances', '_controller' => 'App\\Controller\\AgriculteurDashboardController::maintenances'], null, null, null, false, false, null]],
        '/agriculteur/evenements' => [[['_route' => 'agri_evenements', '_controller' => 'App\\Controller\\AgriculteurDashboardController::evenements'], null, null, null, false, false, null]],
        '/agriculteur/evenements/participations' => [[['_route' => 'agri_participations', '_controller' => 'App\\Controller\\AgriculteurDashboardController::participations'], null, null, null, false, false, null]],
        '/agriculteur/abonnements/offres' => [[['_route' => 'agri_offres', '_controller' => 'App\\Controller\\AgriculteurDashboardController::offres'], null, null, null, false, false, null]],
        '/agriculteur/abonnements' => [[['_route' => 'agri_abonnements', '_controller' => 'App\\Controller\\AgriculteurDashboardController::abonnements'], null, null, null, false, false, null]],
        '/agriculteur/taches' => [[['_route' => 'agri_taches', '_controller' => 'App\\Controller\\AgriculteurDashboardController::taches'], null, null, null, false, false, null]],
        '/animaux' => [[['_route' => 'app_animaux_index', '_controller' => 'App\\Controller\\Animals\\AnimauxController::index'], null, ['GET' => 0], null, false, false, null]],
        '/animaux/new' => [[['_route' => 'app_animaux_new', '_controller' => 'App\\Controller\\Animals\\AnimauxController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/examen' => [[['_route' => 'app_examens_index', '_controller' => 'App\\Controller\\Animals\\ExamensController::index'], null, ['GET' => 0], null, false, false, null]],
        '/examen/new' => [[['_route' => 'app_examens_new', '_controller' => 'App\\Controller\\Animals\\ExamensController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/login' => [[['_route' => 'app_login', '_controller' => 'App\\Controller\\AuthController::login'], null, null, null, false, false, null]],
        '/register' => [[['_route' => 'app_register', '_controller' => 'App\\Controller\\AuthController::register'], null, null, null, false, false, null]],
        '/logout' => [[['_route' => 'app_logout', '_controller' => 'App\\Controller\\AuthController::logout'], null, null, null, false, false, null]],
        '/' => [[['_route' => 'app_home', '_controller' => 'App\\Controller\\HomeController::index'], null, null, null, false, false, null]],
        '/agriculteur/materiels/machines/statistiques' => [[['_route' => 'agri_machine_statistiques', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineStatistiques'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/materiels/machines/new' => [[['_route' => 'agri_machine_new', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineNew'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/maintenances' => [[['_route' => 'agri_maintenances_index', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/export/excel' => [[['_route' => 'agri_maintenances_export_excel', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::exportExcel'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/export/pdf' => [[['_route' => 'agri_maintenances_export_pdf', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/new' => [[['_route' => 'agri_maintenances_new', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agri/plantes' => [[['_route' => 'agri_plantes', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agri/plantes/new' => [[['_route' => 'agri_plantes_new', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agri/rotations' => [[['_route' => 'agri_rotations', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agri/rotations/new' => [[['_route' => 'agri_rotations_new', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agri/terrains' => [[['_route' => 'agri_terrains', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agri/terrains/new' => [[['_route' => 'agri_terrains_new', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/plantes' => [[['_route' => 'admin_plantes', '_controller' => 'App\\Controller\\Terrain\\PlanteController::index'], null, ['GET' => 0], null, false, false, null]],
        '/plantes/new' => [[['_route' => 'admin_plantes_new', '_controller' => 'App\\Controller\\Terrain\\PlanteController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/rotations' => [[['_route' => 'admin_rotations', '_controller' => 'App\\Controller\\Terrain\\RotationController::index'], null, ['GET' => 0], null, false, false, null]],
        '/rotations/new' => [[['_route' => 'admin_rotations_new', '_controller' => 'App\\Controller\\Terrain\\RotationController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/rotations/agri/rotations' => [[['_route' => 'admin_rotationsagri_rotations', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriIndex'], null, ['GET' => 0], null, false, false, null]],
        '/rotations/agri/rotations/new' => [[['_route' => 'admin_rotationsagri_rotations_new', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriNew'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/terrains' => [[['_route' => 'admin_terrains', '_controller' => 'App\\Controller\\Terrain\\TerrainController::index'], null, ['GET' => 0], null, false, false, null]],
        '/terrains/new' => [[['_route' => 'admin_terrains_new', '_controller' => 'App\\Controller\\Terrain\\TerrainController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
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
        '/ouvrier/evenements' => [[['_route' => 'ouvrier_evenements', '_controller' => 'App\\Controller\\User\\OuvrierController::evenements'], null, null, null, false, false, null]],
        '/tache' => [[['_route' => 'app_tache_index', '_controller' => 'App\\Controller\\User\\TacheController::index'], null, ['GET' => 0], null, true, false, null]],
        '/tache/export/pdf' => [[['_route' => 'app_tache_export_pdf', '_controller' => 'App\\Controller\\User\\TacheController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/tache/new' => [[['_route' => 'app_tache_new', '_controller' => 'App\\Controller\\User\\TacheController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/tache/front' => [[['_route' => 'app_tache_front', '_controller' => 'App\\Controller\\User\\TacheFrontController::front'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/tache/auto-assigner' => [[['_route' => 'app_tache_auto_assigner', '_controller' => 'App\\Controller\\User\\TacheFrontController::autoAssigner'], null, ['POST' => 0], null, false, false, null]],
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
                        .'|abonnements/(?'
                            .'|([^/]++)(?'
                                .'|(*:402)'
                                .'|/(?'
                                    .'|pdf(*:417)'
                                    .'|edit(*:429)'
                                    .'|delete(*:443)'
                                .')'
                            .')'
                            .'|update\\-etats(*:466)'
                            .'|purge\\-expires(*:488)'
                        .')'
                        .'|users/(?'
                            .'|(\\d+)(*:511)'
                            .'|(\\d+)/edit(*:529)'
                            .'|(\\d+)/delete(*:549)'
                            .'|(\\d+)/pdf(*:566)'
                        .')'
                    .')'
                    .'|nimaux/([^/]++)(?'
                        .'|(*:594)'
                        .'|/edit(*:607)'
                        .'|(*:615)'
                    .')'
                    .'|gri(?'
                        .'|culteur/(?'
                            .'|ma(?'
                                .'|teriels/machines/(?'
                                    .'|(\\d+)(*:671)'
                                    .'|(\\d+)/edit(*:689)'
                                    .'|(\\d+)/delete(*:709)'
                                .')'
                                .'|intenances/([^/]++)(?'
                                    .'|(*:740)'
                                    .'|/(?'
                                        .'|edit(*:756)'
                                        .'|delete(*:770)'
                                    .')'
                                .')'
                            .')'
                            .'|abonnement/front/pdf/([^/]++)(*:810)'
                            .'|offre/souscrire/([^/]++)(*:842)'
                            .'|tache/front/agriculteur/([^/]++)(*:882)'
                        .')'
                        .'|/(?'
                            .'|plantes/([^/]++)(?'
                                .'|/(?'
                                    .'|edit(*:922)'
                                    .'|delete(*:936)'
                                .')'
                                .'|(*:945)'
                            .')'
                            .'|rotations/([^/]++)(?'
                                .'|/(?'
                                    .'|edit(*:983)'
                                    .'|delete(*:997)'
                                .')'
                                .'|(*:1006)'
                            .')'
                            .'|terrains/([^/]++)(?'
                                .'|/(?'
                                    .'|edit(*:1044)'
                                    .'|delete(*:1059)'
                                    .'|certificat\\-propriete(*:1089)'
                                .')'
                                .'|(*:1099)'
                            .')'
                        .')'
                    .')'
                .')'
                .'|/examen/([^/]++)(?'
                    .'|(*:1131)'
                    .'|/edit(*:1145)'
                    .'|(*:1154)'
                .')'
                .'|/plantes/([^/]++)(?'
                    .'|/(?'
                        .'|edit(*:1192)'
                        .'|delete(*:1207)'
                    .')'
                    .'|(*:1217)'
                .')'
                .'|/rotations/(?'
                    .'|([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:1260)'
                            .'|delete(*:1275)'
                        .')'
                        .'|(*:1285)'
                    .')'
                    .'|agri/rotations/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:1329)'
                            .'|delete(*:1344)'
                        .')'
                        .'|(*:1354)'
                    .')'
                .')'
                .'|/t(?'
                    .'|errains/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:1397)'
                            .'|delete(*:1412)'
                        .')'
                        .'|(*:1422)'
                    .')'
                    .'|ache/([^/]++)(?'
                        .'|(*:1448)'
                        .'|/(?'
                            .'|pdf(*:1464)'
                            .'|edit(*:1477)'
                            .'|delete(*:1492)'
                        .')'
                    .')'
                .')'
                .'|/o(?'
                    .'|ffre/([^/]++)(?'
                        .'|(*:1525)'
                        .'|/(?'
                            .'|pdf(*:1541)'
                            .'|edit(*:1554)'
                            .'|delete(*:1569)'
                        .')'
                    .')'
                    .'|uvrier/tache/([^/]++)/statut/([^/]++)(*:1617)'
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
        402 => [[['_route' => 'admin_abonnements_show', '_controller' => 'App\\Controller\\User\\AbonnementController::show'], ['idAbonn'], ['GET' => 0], null, false, true, null]],
        417 => [[['_route' => 'admin_abonnements_pdf', '_controller' => 'App\\Controller\\User\\AbonnementController::pdf'], ['idAbonn'], ['GET' => 0], null, false, false, null]],
        429 => [[['_route' => 'admin_abonnements_edit', '_controller' => 'App\\Controller\\User\\AbonnementController::edit'], ['idAbonn'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        443 => [[['_route' => 'admin_abonnements_delete', '_controller' => 'App\\Controller\\User\\AbonnementController::delete'], ['idAbonn'], ['POST' => 0], null, false, false, null]],
        466 => [[['_route' => 'admin_abonnements_update_etats', '_controller' => 'App\\Controller\\User\\AbonnementController::updateEtats'], [], ['POST' => 0], null, false, false, null]],
        488 => [[['_route' => 'admin_abonnements_purge_expires', '_controller' => 'App\\Controller\\User\\AbonnementController::purgeExpires'], [], ['POST' => 0], null, false, false, null]],
        511 => [[['_route' => 'admin_users_show', '_controller' => 'App\\Controller\\User\\AdminUserController::show'], ['cin'], ['GET' => 0], null, false, true, null]],
        529 => [[['_route' => 'admin_users_edit', '_controller' => 'App\\Controller\\User\\AdminUserController::edit'], ['cin'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        549 => [[['_route' => 'admin_users_delete', '_controller' => 'App\\Controller\\User\\AdminUserController::delete'], ['cin'], ['POST' => 0], null, false, false, null]],
        566 => [[['_route' => 'admin_users_pdf', '_controller' => 'App\\Controller\\User\\AdminUserController::pdf'], ['cin'], ['GET' => 0], null, false, false, null]],
        594 => [[['_route' => 'app_animaux_show', '_controller' => 'App\\Controller\\Animals\\AnimauxController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        607 => [[['_route' => 'app_animaux_edit', '_controller' => 'App\\Controller\\Animals\\AnimauxController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        615 => [[['_route' => 'app_animaux_delete', '_controller' => 'App\\Controller\\Animals\\AnimauxController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        671 => [[['_route' => 'agri_machine_show', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineShow'], ['id'], ['GET' => 0], null, false, true, null]],
        689 => [[['_route' => 'agri_machine_edit', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineEdit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        709 => [[['_route' => 'agri_machine_delete', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineDelete'], ['id'], ['POST' => 0], null, false, false, null]],
        740 => [[['_route' => 'agri_maintenances_show', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        756 => [[['_route' => 'agri_maintenances_edit', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        770 => [[['_route' => 'agri_maintenances_delete', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        810 => [[['_route' => 'app_abonnement_pdf', '_controller' => 'App\\Controller\\User\\AbonnementFrontController::pdf'], ['id'], ['GET' => 0], null, false, true, null]],
        842 => [[['_route' => 'app_offre_souscrire', '_controller' => 'App\\Controller\\User\\OffreFrontController::souscrire'], ['id'], ['POST' => 0], null, false, true, null]],
        882 => [[['_route' => 'app_tache_by_ouvrier', '_controller' => 'App\\Controller\\User\\TacheFrontController::tachesByOuvrier'], ['cin'], ['GET' => 0], null, false, true, null]],
        922 => [[['_route' => 'agri_plantes_edit', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        936 => [[['_route' => 'agri_plantes_delete', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        945 => [[['_route' => 'agri_plantes_show', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        983 => [[['_route' => 'agri_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        997 => [[['_route' => 'agri_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1006 => [[['_route' => 'agri_rotations_show', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1044 => [[['_route' => 'agri_terrains_edit', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1059 => [[['_route' => 'agri_terrains_delete', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1089 => [[['_route' => 'agri_terrains_certificat_propriete', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::certificatPropriete'], ['id'], ['GET' => 0], null, false, false, null]],
        1099 => [[['_route' => 'agri_terrains_show', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1131 => [[['_route' => 'app_examens_show', '_controller' => 'App\\Controller\\Animals\\ExamensController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1145 => [[['_route' => 'app_examens_edit', '_controller' => 'App\\Controller\\Animals\\ExamensController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1154 => [[['_route' => 'app_examens_delete', '_controller' => 'App\\Controller\\Animals\\ExamensController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        1192 => [[['_route' => 'admin_plantes_edit', '_controller' => 'App\\Controller\\Terrain\\PlanteController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1207 => [[['_route' => 'admin_plantes_delete', '_controller' => 'App\\Controller\\Terrain\\PlanteController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1217 => [[['_route' => 'admin_plantes_show', '_controller' => 'App\\Controller\\Terrain\\PlanteController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1260 => [[['_route' => 'admin_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\RotationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1275 => [[['_route' => 'admin_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\RotationController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1285 => [[['_route' => 'admin_rotations_show', '_controller' => 'App\\Controller\\Terrain\\RotationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1329 => [[['_route' => 'admin_rotationsagri_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriEdit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1344 => [[['_route' => 'admin_rotationsagri_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriDelete'], ['id'], ['POST' => 0], null, false, false, null]],
        1354 => [[['_route' => 'admin_rotationsagri_rotations_show', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriShow'], ['id'], ['GET' => 0], null, false, true, null]],
        1397 => [[['_route' => 'admin_terrains_edit', '_controller' => 'App\\Controller\\Terrain\\TerrainController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1412 => [[['_route' => 'admin_terrains_delete', '_controller' => 'App\\Controller\\Terrain\\TerrainController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1422 => [[['_route' => 'admin_terrains_show', '_controller' => 'App\\Controller\\Terrain\\TerrainController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1448 => [[['_route' => 'app_tache_show', '_controller' => 'App\\Controller\\User\\TacheController::show'], ['idTache'], ['GET' => 0], null, false, true, null]],
        1464 => [[['_route' => 'app_tache_pdf', '_controller' => 'App\\Controller\\User\\TacheController::pdf'], ['idTache'], ['GET' => 0], null, false, false, null]],
        1477 => [[['_route' => 'app_tache_edit', '_controller' => 'App\\Controller\\User\\TacheController::edit'], ['idTache'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1492 => [[['_route' => 'app_tache_delete', '_controller' => 'App\\Controller\\User\\TacheController::delete'], ['idTache'], ['POST' => 0], null, false, false, null]],
        1525 => [[['_route' => 'app_offre_show', '_controller' => 'App\\Controller\\User\\OffreController::show'], ['idOffres'], ['GET' => 0], null, false, true, null]],
        1541 => [[['_route' => 'app_offre_pdf', '_controller' => 'App\\Controller\\User\\OffreController::pdf'], ['idOffres'], ['GET' => 0], null, false, false, null]],
        1554 => [[['_route' => 'app_offre_edit', '_controller' => 'App\\Controller\\User\\OffreController::edit'], ['idOffres'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1569 => [[['_route' => 'app_offre_delete', '_controller' => 'App\\Controller\\User\\OffreController::delete'], ['idOffres'], ['POST' => 0], null, false, false, null]],
        1617 => [
            [['_route' => 'ouvrier_tache_statut', '_controller' => 'App\\Controller\\User\\OuvrierController::changerStatut'], ['id', 'statut'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
