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
        '/agriculteur/terrains' => [[['_route' => 'agri_terrains', '_controller' => 'App\\Controller\\AgriculteurDashboardController::terrains'], null, null, null, false, false, null]],
        '/agriculteur/terrains/rotations' => [[['_route' => 'agri_rotations', '_controller' => 'App\\Controller\\AgriculteurDashboardController::rotations'], null, null, null, false, false, null]],
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
        '/admin/abonnements' => [[['_route' => 'admin_abonnements_index', '_controller' => 'App\\Controller\\User\\AbonnementController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/abonnements/new' => [[['_route' => 'admin_abonnements_new', '_controller' => 'App\\Controller\\User\\AbonnementController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/abonnement/front' => [[['_route' => 'app_abonnement_front', '_controller' => 'App\\Controller\\User\\AbonnementFrontController::front'], null, ['GET' => 0], null, false, false, null]],
        '/admin/users' => [[['_route' => 'admin_users_list', '_controller' => 'App\\Controller\\User\\AdminUserController::list'], null, null, null, false, false, null]],
        '/admin/users/new' => [[['_route' => 'admin_users_new', '_controller' => 'App\\Controller\\User\\AdminUserController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/admin/users/export/excel' => [[['_route' => 'admin_users_excel', '_controller' => 'App\\Controller\\User\\AdminUserController::excel'], null, ['GET' => 0], null, false, false, null]],
        '/offre' => [[['_route' => 'app_offre_list', '_controller' => 'App\\Controller\\User\\OffreController::list'], null, ['GET' => 0], null, true, false, null]],
        '/offre/new' => [[['_route' => 'app_offre_new', '_controller' => 'App\\Controller\\User\\OffreController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/offre/front' => [[['_route' => 'app_offre_front', '_controller' => 'App\\Controller\\User\\OffreFrontController::front'], null, ['GET' => 0], null, false, false, null]],
        '/tache' => [[['_route' => 'app_tache_index', '_controller' => 'App\\Controller\\User\\TacheController::index'], null, ['GET' => 0], null, true, false, null]],
        '/tache/export/pdf' => [[['_route' => 'app_tache_export_pdf', '_controller' => 'App\\Controller\\User\\TacheController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/tache/new' => [[['_route' => 'app_tache_new', '_controller' => 'App\\Controller\\User\\TacheController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/tache/front' => [[['_route' => 'app_tache_front', '_controller' => 'App\\Controller\\User\\TacheFrontController::front'], null, ['GET' => 0], null, false, false, null]],
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
                    .'|dmin/(?'
                        .'|abonnements/([^/]++)(?'
                            .'|(*:286)'
                            .'|/(?'
                                .'|edit(*:302)'
                                .'|delete(*:316)'
                            .')'
                        .')'
                        .'|users/(?'
                            .'|(\\d+)(*:340)'
                            .'|(\\d+)/edit(*:358)'
                            .'|(\\d+)/delete(*:378)'
                            .'|(\\d+)/pdf(*:395)'
                        .')'
                    .')'
                    .'|griculteur/(?'
                        .'|abonnement/front/pdf/([^/]++)(*:448)'
                        .'|offre/souscrire/([^/]++)(*:480)'
                        .'|tache/front/agriculteur/([^/]++)(*:520)'
                    .')'
                .')'
                .'|/examen/([^/]++)(?'
                    .'|(*:549)'
                    .'|/edit(*:562)'
                    .'|(*:570)'
                .')'
                .'|/offre/([^/]++)(?'
                    .'|(*:597)'
                    .'|/(?'
                        .'|edit(*:613)'
                        .'|delete(*:627)'
                    .')'
                .')'
                .'|/tache/([^/]++)(?'
                    .'|(*:655)'
                    .'|/(?'
                        .'|pdf(*:670)'
                        .'|edit(*:682)'
                        .'|delete(*:696)'
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
        225 => [[['_route' => 'app_animaux_show', '_controller' => 'App\\Controller\\Animals\\AnimauxController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        238 => [[['_route' => 'app_animaux_edit', '_controller' => 'App\\Controller\\Animals\\AnimauxController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        246 => [[['_route' => 'app_animaux_delete', '_controller' => 'App\\Controller\\Animals\\AnimauxController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        286 => [[['_route' => 'admin_abonnements_show', '_controller' => 'App\\Controller\\User\\AbonnementController::show'], ['idAbonn'], ['GET' => 0], null, false, true, null]],
        302 => [[['_route' => 'admin_abonnements_edit', '_controller' => 'App\\Controller\\User\\AbonnementController::edit'], ['idAbonn'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        316 => [[['_route' => 'admin_abonnements_delete', '_controller' => 'App\\Controller\\User\\AbonnementController::delete'], ['idAbonn'], ['POST' => 0], null, false, false, null]],
        340 => [[['_route' => 'admin_users_show', '_controller' => 'App\\Controller\\User\\AdminUserController::show'], ['cin'], ['GET' => 0], null, false, true, null]],
        358 => [[['_route' => 'admin_users_edit', '_controller' => 'App\\Controller\\User\\AdminUserController::edit'], ['cin'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        378 => [[['_route' => 'admin_users_delete', '_controller' => 'App\\Controller\\User\\AdminUserController::delete'], ['cin'], ['POST' => 0], null, false, false, null]],
        395 => [[['_route' => 'admin_users_pdf', '_controller' => 'App\\Controller\\User\\AdminUserController::pdf'], ['cin'], ['GET' => 0], null, false, false, null]],
        448 => [[['_route' => 'app_abonnement_pdf', '_controller' => 'App\\Controller\\User\\AbonnementFrontController::pdf'], ['id'], ['GET' => 0], null, false, true, null]],
        480 => [[['_route' => 'app_offre_souscrire', '_controller' => 'App\\Controller\\User\\OffreFrontController::souscrire'], ['id'], ['POST' => 0], null, false, true, null]],
        520 => [[['_route' => 'app_tache_by_ouvrier', '_controller' => 'App\\Controller\\User\\TacheFrontController::tachesByOuvrier'], ['cin'], ['GET' => 0], null, false, true, null]],
        549 => [[['_route' => 'app_examens_show', '_controller' => 'App\\Controller\\Animals\\ExamensController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        562 => [[['_route' => 'app_examens_edit', '_controller' => 'App\\Controller\\Animals\\ExamensController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        570 => [[['_route' => 'app_examens_delete', '_controller' => 'App\\Controller\\Animals\\ExamensController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        597 => [[['_route' => 'app_offre_show', '_controller' => 'App\\Controller\\User\\OffreController::show'], ['idOffres'], ['GET' => 0], null, false, true, null]],
        613 => [[['_route' => 'app_offre_edit', '_controller' => 'App\\Controller\\User\\OffreController::edit'], ['idOffres'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        627 => [[['_route' => 'app_offre_delete', '_controller' => 'App\\Controller\\User\\OffreController::delete'], ['idOffres'], ['POST' => 0], null, false, false, null]],
        655 => [[['_route' => 'app_tache_show', '_controller' => 'App\\Controller\\User\\TacheController::show'], ['idTache'], ['GET' => 0], null, false, true, null]],
        670 => [[['_route' => 'app_tache_pdf', '_controller' => 'App\\Controller\\User\\TacheController::pdf'], ['idTache'], ['GET' => 0], null, false, false, null]],
        682 => [[['_route' => 'app_tache_edit', '_controller' => 'App\\Controller\\User\\TacheController::edit'], ['idTache'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        696 => [
            [['_route' => 'app_tache_delete', '_controller' => 'App\\Controller\\User\\TacheController::delete'], ['idTache'], ['POST' => 0], null, false, false, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
