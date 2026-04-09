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
        '/agriculteur/evenements' => [
            [['_route' => 'agri_evenements', '_controller' => 'App\\Controller\\AgriculteurDashboardController::evenements'], null, null, null, false, false, null],
            [['_route' => 'agriculteur_evenement_index', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\EvenementController::index'], null, null, null, true, false, null],
        ],
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
        '/agriculteur/participations' => [[['_route' => 'agriculteur_participation_index', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\ParticipationController::index'], null, null, null, true, false, null]],
        '/CategoriesEvenement' => [[['_route' => 'categorie_evenement_index', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::index'], null, null, null, false, false, null]],
        '/ajouterCategorieEvenement' => [[['_route' => 'categorie_evenement_ajouter', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::ajouter'], null, null, null, false, false, null]],
        '/ouvrier/evenements' => [[['_route' => 'ouvrier_evenement_index', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\EvenementController::index'], null, null, null, true, false, null]],
        '/ouvrier/participations' => [[['_route' => 'ouvrier_participation_index', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\ParticipationController::index'], null, null, null, true, false, null]],
        '/evenements' => [[['_route' => 'evenement_index', '_controller' => 'App\\Controller\\Evenements\\EvenementController::index'], null, null, null, false, false, null]],
        '/evenements/ajouter' => [[['_route' => 'evenement_ajouter', '_controller' => 'App\\Controller\\Evenements\\EvenementController::ajouter'], null, null, null, false, false, null]],
        '/participations' => [[['_route' => 'participation_index', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::index'], null, null, null, false, false, null]],
        '/participations/ajouter' => [[['_route' => 'participation_ajouter', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::ajouter'], null, null, null, false, false, null]],
        '/' => [[['_route' => 'app_home', '_controller' => 'App\\Controller\\HomeController::index'], null, null, null, false, false, null]],
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
                    .'|griculteur/(?'
                        .'|evenements/inscription/([^/]++)(*:300)'
                        .'|participations/(?'
                            .'|modifier/([^/]++)(*:343)'
                            .'|annuler/([^/]++)(*:367)'
                        .')'
                    .')'
                .')'
                .'|/e(?'
                    .'|xamen/([^/]++)(?'
                        .'|(*:400)'
                        .'|/edit(*:413)'
                        .'|(*:421)'
                    .')'
                    .'|venements/(?'
                        .'|modifier/([^/]++)(*:460)'
                        .'|supprimer/([^/]++)(*:486)'
                    .')'
                .')'
                .'|/modifierCategorieEvenement/([^/]++)(*:532)'
                .'|/supprimerCategorieEvenement/([^/]++)(*:577)'
                .'|/ouvrier/(?'
                    .'|evenements/inscription/([^/]++)(*:628)'
                    .'|participations/(?'
                        .'|modifier/([^/]++)(*:671)'
                        .'|annuler/([^/]++)(*:695)'
                    .')'
                .')'
                .'|/participations/(?'
                    .'|modifier/([^/]++)(*:741)'
                    .'|supprimer/([^/]++)(*:767)'
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
        300 => [[['_route' => 'agriculteur_evenement_inscription', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\EvenementController::inscrire'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        343 => [[['_route' => 'agriculteur_participation_modifier', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\ParticipationController::modifier'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        367 => [[['_route' => 'agriculteur_participation_annuler', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\ParticipationController::annuler'], ['id'], ['POST' => 0], null, false, true, null]],
        400 => [[['_route' => 'app_examens_show', '_controller' => 'App\\Controller\\Animals\\ExamensController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        413 => [[['_route' => 'app_examens_edit', '_controller' => 'App\\Controller\\Animals\\ExamensController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        421 => [[['_route' => 'app_examens_delete', '_controller' => 'App\\Controller\\Animals\\ExamensController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        460 => [[['_route' => 'evenement_modifier', '_controller' => 'App\\Controller\\Evenements\\EvenementController::modifier'], ['id'], null, null, false, true, null]],
        486 => [[['_route' => 'evenement_supprimer', '_controller' => 'App\\Controller\\Evenements\\EvenementController::supprimer'], ['id'], null, null, false, true, null]],
        532 => [[['_route' => 'categorie_evenement_modifier', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::modifier'], ['id'], null, null, false, true, null]],
        577 => [[['_route' => 'categorie_evenement_supprimer', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::supprimer'], ['id'], null, null, false, true, null]],
        628 => [[['_route' => 'ouvrier_evenement_inscription', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\EvenementController::inscrire'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        671 => [[['_route' => 'ouvrier_participation_modifier', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\ParticipationController::modifier'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        695 => [[['_route' => 'ouvrier_participation_annuler', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\ParticipationController::annuler'], ['id'], ['POST' => 0], null, false, true, null]],
        741 => [[['_route' => 'participation_modifier', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::modifier'], ['id'], null, null, false, true, null]],
        767 => [
            [['_route' => 'participation_supprimer', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::supprimer'], ['id'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
