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
        '/agriculteur' => [[['_route' => 'agri_home', '_controller' => 'App\\Controller\\AgriculteurDashboardController::index'], null, null, null, true, false, null]],
        '/agriculteur/animaux' => [[['_route' => 'agri_animaux', '_controller' => 'App\\Controller\\AgriculteurDashboardController::animaux'], null, null, null, false, false, null]],
        '/agriculteur/animaux/examens' => [[['_route' => 'agri_examens', '_controller' => 'App\\Controller\\AgriculteurDashboardController::examens'], null, null, null, false, false, null]],
        '/agriculteur/stocks/categories' => [[['_route' => 'agri_categories', '_controller' => 'App\\Controller\\AgriculteurDashboardController::categories'], null, null, null, false, false, null]],
        '/agriculteur/stocks/produits' => [[['_route' => 'agri_produits', '_controller' => 'App\\Controller\\AgriculteurDashboardController::produits'], null, null, null, false, false, null]],
        '/agriculteur/materiels/machines' => [[['_route' => 'agri_machines', '_controller' => 'App\\Controller\\AgriculteurDashboardController::machines'], null, null, null, false, false, null]],
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
                    .'|nimaux/([^/]++)(?'
                        .'|(*:225)'
                        .'|/edit(*:238)'
                        .'|(*:246)'
                    .')'
                    .'|gri/(?'
                        .'|plantes/([^/]++)(?'
                            .'|/(?'
                                .'|edit(*:289)'
                                .'|delete(*:303)'
                            .')'
                            .'|(*:312)'
                        .')'
                        .'|rotations/([^/]++)(?'
                            .'|/(?'
                                .'|edit(*:350)'
                                .'|delete(*:364)'
                            .')'
                            .'|(*:373)'
                        .')'
                        .'|terrains/([^/]++)(?'
                            .'|/(?'
                                .'|edit(*:410)'
                                .'|delete(*:424)'
                                .'|certificat\\-propriete(*:453)'
                            .')'
                            .'|(*:462)'
                        .')'
                    .')'
                .')'
                .'|/examen/([^/]++)(?'
                    .'|(*:492)'
                    .'|/edit(*:505)'
                    .'|(*:513)'
                .')'
                .'|/plantes/([^/]++)(?'
                    .'|/(?'
                        .'|edit(*:550)'
                        .'|delete(*:564)'
                    .')'
                    .'|(*:573)'
                .')'
                .'|/rotations/(?'
                    .'|([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:615)'
                            .'|delete(*:629)'
                        .')'
                        .'|(*:638)'
                    .')'
                    .'|agri/rotations/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:681)'
                            .'|delete(*:695)'
                        .')'
                        .'|(*:704)'
                    .')'
                .')'
                .'|/terrains/([^/]++)(?'
                    .'|/(?'
                        .'|edit(*:743)'
                        .'|delete(*:757)'
                    .')'
                    .'|(*:766)'
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
        225 => [[['_route' => 'app_animaux_show', '_controller' => 'App\\Controller\\Animals\\AnimauxController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        238 => [[['_route' => 'app_animaux_edit', '_controller' => 'App\\Controller\\Animals\\AnimauxController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        246 => [[['_route' => 'app_animaux_delete', '_controller' => 'App\\Controller\\Animals\\AnimauxController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        289 => [[['_route' => 'agri_plantes_edit', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        303 => [[['_route' => 'agri_plantes_delete', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        312 => [[['_route' => 'agri_plantes_show', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        350 => [[['_route' => 'agri_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        364 => [[['_route' => 'agri_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        373 => [[['_route' => 'agri_rotations_show', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        410 => [[['_route' => 'agri_terrains_edit', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        424 => [[['_route' => 'agri_terrains_delete', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        453 => [[['_route' => 'agri_terrains_certificat_propriete', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::certificatPropriete'], ['id'], ['GET' => 0], null, false, false, null]],
        462 => [[['_route' => 'agri_terrains_show', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        492 => [[['_route' => 'app_examens_show', '_controller' => 'App\\Controller\\Animals\\ExamensController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        505 => [[['_route' => 'app_examens_edit', '_controller' => 'App\\Controller\\Animals\\ExamensController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        513 => [[['_route' => 'app_examens_delete', '_controller' => 'App\\Controller\\Animals\\ExamensController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        550 => [[['_route' => 'admin_plantes_edit', '_controller' => 'App\\Controller\\Terrain\\PlanteController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        564 => [[['_route' => 'admin_plantes_delete', '_controller' => 'App\\Controller\\Terrain\\PlanteController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        573 => [[['_route' => 'admin_plantes_show', '_controller' => 'App\\Controller\\Terrain\\PlanteController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        615 => [[['_route' => 'admin_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\RotationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        629 => [[['_route' => 'admin_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\RotationController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        638 => [[['_route' => 'admin_rotations_show', '_controller' => 'App\\Controller\\Terrain\\RotationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        681 => [[['_route' => 'admin_rotationsagri_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriEdit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        695 => [[['_route' => 'admin_rotationsagri_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriDelete'], ['id'], ['POST' => 0], null, false, false, null]],
        704 => [[['_route' => 'admin_rotationsagri_rotations_show', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriShow'], ['id'], ['GET' => 0], null, false, true, null]],
        743 => [[['_route' => 'admin_terrains_edit', '_controller' => 'App\\Controller\\Terrain\\TerrainController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        757 => [[['_route' => 'admin_terrains_delete', '_controller' => 'App\\Controller\\Terrain\\TerrainController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        766 => [
            [['_route' => 'admin_terrains_show', '_controller' => 'App\\Controller\\Terrain\\TerrainController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
