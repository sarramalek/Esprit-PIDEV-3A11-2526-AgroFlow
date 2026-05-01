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
        '/admin/materiels/machines' => [[['_route' => 'admin_machines_index', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/machines/new' => [[['_route' => 'admin_machines_new', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/admin/materiels/maintenances' => [[['_route' => 'admin_maintenances_index', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::index'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/statistiques/bar' => [[['_route' => 'admin_maintenances_stats_bar', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::statsBar'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/historique' => [[['_route' => 'admin_maintenances_history', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::history'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/ai/dashboard' => [[['_route' => 'admin_maintenances_ai_dashboard', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::aiDashboard'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/ai/schedule' => [[['_route' => 'admin_maintenances_ai_schedule', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::apiOptimizedSchedule'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/ai/alerts' => [[['_route' => 'admin_maintenances_ai_alerts', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::apiGlobalAlerts'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/export/pdf' => [[['_route' => 'admin_maintenances_export_pdf', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/admin/materiels/maintenances/new' => [[['_route' => 'admin_maintenances_new', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur' => [[['_route' => 'agri_home', '_controller' => 'App\\Controller\\AgriculteurDashboardController::index'], null, null, null, true, false, null]],
        '/agriculteur/animaux' => [[['_route' => 'agri_animaux', '_controller' => 'App\\Controller\\AgriculteurDashboardController::animaux'], null, null, null, false, false, null]],
        '/agriculteur/animaux/examens' => [[['_route' => 'agri_examens', '_controller' => 'App\\Controller\\AgriculteurDashboardController::examens'], null, null, null, false, false, null]],
        '/agriculteur/materiels/machines' => [
            [['_route' => 'agri_machine_index', '_controller' => 'App\\Controller\\AgriculteurDashboardController::machineIndex'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'agri_machines', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineIndex'], null, ['GET' => 0], null, false, false, null],
        ],
        '/agriculteur/materiels/machines/new' => [
            [['_route' => 'agri_machine_new', '_controller' => 'App\\Controller\\AgriculteurDashboardController::machineNew'], null, ['GET' => 0, 'POST' => 1], null, false, false, null],
            [['_route' => 'agri_agri_machine_new', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineNew'], null, ['GET' => 0, 'POST' => 1], null, false, false, null],
        ],
        '/agriculteur/materiels/maintenances' => [[['_route' => 'agri_maintenances', '_controller' => 'App\\Controller\\AgriculteurDashboardController::maintenances'], null, null, null, false, false, null]],
        '/agriculteur/evenements/participations' => [[['_route' => 'agri_participations', '_controller' => 'App\\Controller\\AgriculteurDashboardController::participations'], null, null, null, false, false, null]],
        '/agriculteur/abonnements/offres' => [[['_route' => 'agri_offres', '_controller' => 'App\\Controller\\AgriculteurDashboardController::offres'], null, null, null, false, false, null]],
        '/agriculteur/abonnements' => [[['_route' => 'agri_abonnements', '_controller' => 'App\\Controller\\AgriculteurDashboardController::abonnements'], null, null, null, false, false, null]],
        '/agriculteur/taches' => [[['_route' => 'agri_taches', '_controller' => 'App\\Controller\\AgriculteurDashboardController::taches'], null, null, null, false, false, null]],
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
        '/examen/search/wikipedia' => [[['_route' => 'app_examens_wikipedia_search', '_controller' => 'App\\Controller\\Animals\\ExamensController::searchWikipedia'], null, ['POST' => 0], null, false, false, null]],
        '/reminders' => [[['_route' => 'app_reminders_dashboard', '_controller' => 'App\\Controller\\Animals\\ReminderController::dashboard'], null, ['GET' => 0], null, false, false, null]],
        '/login' => [[['_route' => 'app_login', '_controller' => 'App\\Controller\\AuthController::login'], null, null, null, false, false, null]],
        '/send-code' => [[['_route' => 'send_code', '_controller' => 'App\\Controller\\AuthController::sendCode'], null, ['POST' => 0], null, false, false, null]],
        '/verify-code' => [[['_route' => 'verify_code', '_controller' => 'App\\Controller\\AuthController::verifyCode'], null, ['POST' => 0], null, false, false, null]],
        '/2fa/verify' => [[['_route' => 'app_2fa_verify', '_controller' => 'App\\Controller\\AuthController::twoFactorVerify'], null, null, null, false, false, null]],
        '/2fa/resend' => [[['_route' => 'app_2fa_resend', '_controller' => 'App\\Controller\\AuthController::twoFactorResend'], null, null, null, false, false, null]],
        '/profil/2fa/toggle' => [[['_route' => 'app_2fa_toggle', '_controller' => 'App\\Controller\\AuthController::toggle2fa'], null, ['POST' => 0], null, false, false, null]],
        '/register' => [[['_route' => 'app_register', '_controller' => 'App\\Controller\\AuthController::register'], null, null, null, false, false, null]],
        '/logout' => [[['_route' => 'app_logout', '_controller' => 'App\\Controller\\AuthController::logout'], null, null, null, false, false, null]],
        '/banni' => [[['_route' => 'app_bann', '_controller' => 'App\\Controller\\AuthController::banni'], null, null, null, false, false, null]],
        '/check-session' => [[['_route' => 'app_check_session', '_controller' => 'App\\Controller\\AuthController::checkSession'], null, null, null, false, false, null]],
        '/reset-password' => [[['_route' => 'app_reset_password', '_controller' => 'App\\Controller\\AuthController::request'], null, null, null, false, false, null]],
        '/reset-password/verify' => [[['_route' => 'app_reset_verify', '_controller' => 'App\\Controller\\AuthController::verify'], null, null, null, false, false, null]],
        '/reset-password/new' => [[['_route' => 'app_reset_new_password', '_controller' => 'App\\Controller\\AuthController::newPassword'], null, null, null, false, false, null]],
        '/reset-password/resend' => [[['_route' => 'app_reset_resend', '_controller' => 'App\\Controller\\AuthController::resend'], null, null, null, false, false, null]],
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
        '/agriculteur/chatbot' => [[['_route' => 'agri_chatbot_index', '_controller' => 'App\\Controller\\Materiels\\ChatbotController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/chatbot/api/machines' => [[['_route' => 'agri_chatbot_api_machines', '_controller' => 'App\\Controller\\Materiels\\ChatbotController::getMachines'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/chatbot/api/chat/general' => [[['_route' => 'agri_chatbot_api_general', '_controller' => 'App\\Controller\\Materiels\\ChatbotController::generalChat'], null, ['POST' => 0], null, false, false, null]],
        '/agriculteur/chatbot/api/nearby-shops' => [[['_route' => 'agri_chatbot_api_nearby_shops', '_controller' => 'App\\Controller\\Materiels\\ChatbotController::getNearbyShops'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/materiels/machines/statistiques' => [[['_route' => 'agri_machine_statistiques', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineStatistiques'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/materiels/machines/export-pdf' => [[['_route' => 'agri_machine_export_pdf', '_controller' => 'App\\Controller\\Materiels\\MachineController::exportPDF'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances' => [[['_route' => 'agri_maintenances_index', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/new' => [[['_route' => 'agri_maintenances_new', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/maintenances/export/excel' => [[['_route' => 'agri_maintenances_export_excel', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::exportExcel'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/export/pdf' => [[['_route' => 'agri_maintenances_export_pdf', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::exportPdf'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/chatbot' => [[['_route' => 'agri_maintenances_chatbot', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::chatbotRedirect'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/api/alerts-summary' => [[['_route' => 'agri_maintenances_api_alerts_summary', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::getAllAlertsSummary'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/api/calendar/reminder-dates' => [[['_route' => 'agri_maintenances_api_reminder_dates', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::getReminderDates'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/maintenances/calendar-reminders' => [[['_route' => 'agri_maintenances_calendar_reminders_page', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::calendarRemindersPage'], null, ['GET' => 0], null, false, false, null]],
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
        '/agriculteur/ouvriers' => [[['_route' => 'app_ouvrier_index', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::index'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/ouvriers/nouveau' => [[['_route' => 'app_ouvrier_new', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/agriculteur/ouvriers/test-mail' => [[['_route' => 'app_test_mail', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::testMail'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/ouvriers/tache/assignation-auto' => [[['_route' => 'app_ouvrier_tache_auto', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::assignationAuto'], null, ['POST' => 0], null, false, false, null]],
        '/agriculteur/ouvriers/tache/suggestion-ia' => [[['_route' => 'app_ouvrier_tache_ia_suggest', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::suggestionIA'], null, ['GET' => 0], null, false, false, null]],
        '/agriculteur/ouvriers/debug-ia' => [[['_route' => 'app_test_ia', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::testIA'], null, ['GET' => 0], null, false, false, null]],
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
        '/agriculteur/maintenances/api/gemini/generate' => [[['_route' => 'agri_maintenances_api_gemini_generate', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::generateWithGemini'], null, ['POST' => 0], null, false, false, null]],
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
                        .'|materiels/ma(?'
                            .'|chines/([^/]++)(?'
                                .'|(*:248)'
                                .'|/(?'
                                    .'|edit(*:264)'
                                    .'|delete(?'
                                        .'|(*:281)'
                                        .'|\\-confirm(*:298)'
                                    .')'
                                .')'
                            .')'
                            .'|intenances/(?'
                                .'|ai/(?'
                                    .'|predict/([^/]++)(*:345)'
                                    .'|analyze/([^/]++)(*:369)'
                                .')'
                                .'|(\\d+)(*:383)'
                                .'|(\\d+)/edit(*:401)'
                                .'|(\\d+)/delete(*:421)'
                            .')'
                        .')'
                        .'|a(?'
                            .'|nimaux/([^/]++)/(?'
                                .'|edit(*:458)'
                                .'|delete(*:472)'
                            .')'
                            .'|bonnements/([^/]++)(?'
                                .'|(*:503)'
                                .'|/(?'
                                    .'|pdf(*:518)'
                                    .'|edit(*:530)'
                                    .'|delete(*:544)'
                                .')'
                            .')'
                        .')'
                        .'|examens/([^/]++)/(?'
                            .'|edit(*:579)'
                            .'|delete(*:593)'
                        .')'
                        .'|users/(?'
                            .'|(\\d+)(*:616)'
                            .'|(\\d+)/ban(*:633)'
                            .'|(\\d+)/unban(*:652)'
                            .'|(\\d+)/delete(*:672)'
                            .'|(\\d+)/pdf(*:689)'
                            .'|(\\d+)/edit(*:707)'
                        .')'
                        .'|gestion\\-stocks/(?'
                            .'|([^/]++)/(?'
                                .'|edit(*:751)'
                                .'|delete(*:765)'
                            .')'
                            .'|mouvement/([^/]++)(*:792)'
                            .'|categorie/([^/]++)/(?'
                                .'|edit(*:826)'
                                .'|delete(*:840)'
                            .')'
                        .')'
                    .')'
                    .'|gri(?'
                        .'|culteur/(?'
                            .'|m(?'
                                .'|a(?'
                                    .'|teriels/machines/(?'
                                        .'|(\\d+)(*:901)'
                                        .'|(\\d+)/edit(*:919)'
                                        .'|(\\d+)(?'
                                            .'|(*:935)'
                                        .')'
                                        .'|(\\d+)/edit(*:954)'
                                        .'|(\\d+)/delete(*:974)'
                                    .')'
                                    .'|intenances/(?'
                                        .'|(\\d+)(*:1002)'
                                        .'|(\\d+)/edit(*:1021)'
                                        .'|(\\d+)/delete(*:1042)'
                                        .'|api/(?'
                                            .'|alerts/(\\d+)(*:1070)'
                                            .'|calendar/reminder\\-detail/(\\d+)(*:1110)'
                                            .'|generate\\-recommendation/(\\d+)(*:1149)'
                                            .'|diagnostics/(\\d+)(*:1175)'
                                            .'|schedule/(\\d+)(*:1198)'
                                        .')'
                                    .')'
                                .')'
                                .'|ouvements/new/([^/]++)(*:1232)'
                            .')'
                            .'|evenements/inscription/([^/]++)(*:1273)'
                            .'|participations/(?'
                                .'|modifier/([^/]++)(*:1317)'
                                .'|annuler/([^/]++)(*:1342)'
                            .')'
                            .'|abonnement/front/pdf/([^/]++)(*:1381)'
                            .'|o(?'
                                .'|ffre/souscrire/([^/]++)(*:1417)'
                                .'|uvriers/(?'
                                    .'|([^/]++)/(?'
                                        .'|modifier(*:1457)'
                                        .'|supprimer(*:1475)'
                                        .'|taches(?'
                                            .'|(*:1493)'
                                            .'|/ajouter(*:1510)'
                                        .')'
                                    .')'
                                    .'|tache/([^/]++)/(?'
                                        .'|etat(*:1543)'
                                        .'|supprimer(*:1561)'
                                    .')'
                                .')'
                            .')'
                            .'|tache/(?'
                                .'|front/agriculteur/([^/]++)(*:1608)'
                                .'|terrain/([^/]++)/(?'
                                    .'|ouvriers(*:1645)'
                                    .'|assigner\\-ouvrier(*:1671)'
                                .')'
                                .'|ouvrier/([^/]++)/desassigner(*:1709)'
                            .')'
                            .'|stocks/(?'
                                .'|([^/]++)(?'
                                    .'|/edit(*:1745)'
                                    .'|(*:1754)'
                                .')'
                                .'|mouvements/new/([^/]++)(*:1787)'
                                .'|([^/]++)/(?'
                                    .'|qr\\-code(?'
                                        .'|(*:1819)'
                                        .'|/(?'
                                            .'|download(*:1840)'
                                            .'|view(*:1853)'
                                        .')'
                                    .')'
                                    .'|details(*:1871)'
                                    .'|scan\\-redirect(*:1894)'
                                .')'
                                .'|categories(?'
                                    .'|(*:1917)'
                                    .'|/([^/]++)(?'
                                        .'|/edit(*:1943)'
                                        .'|(*:1952)'
                                    .')'
                                .')'
                            .')'
                        .')'
                        .'|/(?'
                            .'|plantes/(?'
                                .'|([^/]++)(?'
                                    .'|/(?'
                                        .'|edit(*:1999)'
                                        .'|delete(*:2014)'
                                    .')'
                                    .'|(*:2024)'
                                .')'
                                .'|langue/([^/]++)(*:2049)'
                            .')'
                            .'|rotations/([^/]++)(?'
                                .'|/(?'
                                    .'|edit(*:2088)'
                                    .'|delete(*:2103)'
                                .')'
                                .'|(*:2113)'
                            .')'
                            .'|terrains/([^/]++)(?'
                                .'|/(?'
                                    .'|edit(*:2151)'
                                    .'|delete(*:2166)'
                                    .'|certificat\\-propriete(*:2196)'
                                .')'
                                .'|(*:2206)'
                            .')'
                        .')'
                    .')'
                    .'|nimaux/([^/]++)(?'
                        .'|(*:2236)'
                        .'|/(?'
                            .'|e(?'
                                .'|xport/(?'
                                    .'|card(*:2266)'
                                    .'|medical(*:2282)'
                                .')'
                                .'|dit(*:2295)'
                            .')'
                            .'|match(*:2310)'
                        .')'
                        .'|(*:2320)'
                    .')'
                    .'|pi/terrains/([^/]++)(*:2350)'
                .')'
                .'|/e(?'
                    .'|xamen/([^/]++)(?'
                        .'|(*:2382)'
                        .'|/edit(*:2396)'
                        .'|(*:2405)'
                    .')'
                    .'|venements/(?'
                        .'|modifier/([^/]++)(*:2445)'
                        .'|supprimer/([^/]++)(*:2472)'
                    .')'
                .')'
                .'|/modifierCategorieEvenement/([^/]++)(*:2519)'
                .'|/s(?'
                    .'|upprimerCategorieEvenement/([^/]++)(*:2568)'
                    .'|et\\-locale/([^/]++)(*:2596)'
                .')'
                .'|/o(?'
                    .'|uvrier/(?'
                        .'|evenements/inscription/([^/]++)(*:2652)'
                        .'|participations/(?'
                            .'|modifier/([^/]++)(*:2696)'
                            .'|annuler/([^/]++)(*:2721)'
                        .')'
                        .'|stocks/(?'
                            .'|produits/(?'
                                .'|(\\d+)(*:2758)'
                                .'|(\\d+)/sortie(*:2779)'
                                .'|(\\d+)/mouvements(*:2804)'
                            .')'
                            .'|mouvements/(\\d+)/modifier(*:2839)'
                        .')'
                        .'|tache/([^/]++)/statut/([^/]++)(*:2879)'
                        .'|langue/([^/]++)(*:2903)'
                    .')'
                    .'|ffre/([^/]++)(?'
                        .'|(*:2929)'
                        .'|/(?'
                            .'|pdf(*:2945)'
                            .'|edit(*:2958)'
                            .'|delete(*:2973)'
                        .')'
                    .')'
                .')'
                .'|/p(?'
                    .'|articipations/(?'
                        .'|modifier/([^/]++)(*:3024)'
                        .'|supprimer/([^/]++)(*:3051)'
                    .')'
                    .'|lantes/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:3087)'
                            .'|delete(*:3102)'
                        .')'
                        .'|(*:3112)'
                    .')'
                .')'
                .'|/rotations/(?'
                    .'|([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:3156)'
                            .'|delete(*:3171)'
                        .')'
                        .'|(*:3181)'
                    .')'
                    .'|agri/rotations/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:3225)'
                            .'|delete(*:3240)'
                        .')'
                        .'|(*:3250)'
                    .')'
                .')'
                .'|/t(?'
                    .'|errains/([^/]++)(?'
                        .'|/(?'
                            .'|edit(*:3293)'
                            .'|delete(*:3308)'
                        .')'
                        .'|(*:3318)'
                    .')'
                    .'|ache/([^/]++)(?'
                        .'|(*:3344)'
                        .'|/(?'
                            .'|pdf(*:3360)'
                            .'|edit(*:3373)'
                            .'|delete(*:3388)'
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
        248 => [[['_route' => 'admin_machines_show', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        264 => [[['_route' => 'admin_machines_edit', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        281 => [[['_route' => 'admin_machines_delete', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        298 => [[['_route' => 'admin_machines_delete_confirm', '_controller' => 'App\\Controller\\AdminMateriels\\MachineAdminController::deleteConfirm'], ['id'], ['GET' => 0], null, false, false, null]],
        345 => [[['_route' => 'admin_maintenances_ai_predict', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::apiPredictMachine'], ['id'], ['GET' => 0], null, false, true, null]],
        369 => [[['_route' => 'admin_maintenances_ai_analyze', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::apiAnalyze'], ['id'], ['GET' => 0], null, false, true, null]],
        383 => [[['_route' => 'admin_maintenances_show', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        401 => [[['_route' => 'admin_maintenances_edit', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        421 => [[['_route' => 'admin_maintenances_delete', '_controller' => 'App\\Controller\\AdminMateriels\\MaintenanceAdminController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        458 => [[['_route' => 'admin_animaux_edit', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminAnimauxController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        472 => [[['_route' => 'admin_animaux_delete', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminAnimauxController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        503 => [[['_route' => 'admin_abonnements_show', '_controller' => 'App\\Controller\\User\\AbonnementController::show'], ['idAbonn'], ['GET' => 0], null, false, true, null]],
        518 => [[['_route' => 'admin_abonnements_pdf', '_controller' => 'App\\Controller\\User\\AbonnementController::pdf'], ['idAbonn'], ['GET' => 0], null, false, false, null]],
        530 => [[['_route' => 'admin_abonnements_edit', '_controller' => 'App\\Controller\\User\\AbonnementController::edit'], ['idAbonn'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        544 => [[['_route' => 'admin_abonnements_delete', '_controller' => 'App\\Controller\\User\\AbonnementController::delete'], ['idAbonn'], ['POST' => 0], null, false, false, null]],
        579 => [[['_route' => 'admin_examens_edit', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminExamenController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        593 => [[['_route' => 'admin_examens_delete', '_controller' => 'App\\Controller\\Animals\\Admin\\AdminExamenController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        616 => [[['_route' => 'admin_users_show', '_controller' => 'App\\Controller\\User\\AdminUserController::show'], ['cin'], ['GET' => 0], null, false, true, null]],
        633 => [[['_route' => 'admin_users_ban', '_controller' => 'App\\Controller\\User\\AdminUserController::ban'], ['cin'], ['POST' => 0], null, false, false, null]],
        652 => [[['_route' => 'admin_users_unban', '_controller' => 'App\\Controller\\User\\AdminUserController::unban'], ['cin'], ['POST' => 0], null, false, false, null]],
        672 => [[['_route' => 'admin_users_delete', '_controller' => 'App\\Controller\\User\\AdminUserController::delete'], ['cin'], ['POST' => 0], null, false, false, null]],
        689 => [[['_route' => 'admin_users_pdf', '_controller' => 'App\\Controller\\User\\AdminUserController::pdf'], ['cin'], ['GET' => 0], null, false, false, null]],
        707 => [[['_route' => 'admin_users_edit', '_controller' => 'App\\Controller\\User\\AdminUserController::edit'], ['cin'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        751 => [[['_route' => 'admin_stock_edit', '_controller' => 'App\\Controller\\stocks\\AdminStockController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        765 => [[['_route' => 'admin_stock_delete', '_controller' => 'App\\Controller\\stocks\\AdminStockController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        792 => [[['_route' => 'admin_stock_mouvement', '_controller' => 'App\\Controller\\stocks\\AdminStockController::gestionStock'], ['id'], ['POST' => 0], null, false, true, null]],
        826 => [[['_route' => 'admin_categorie_edit', '_controller' => 'App\\Controller\\stocks\\Admin_CategorieController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        840 => [[['_route' => 'admin_categorie_delete', '_controller' => 'App\\Controller\\stocks\\Admin_CategorieController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        901 => [[['_route' => 'agri_machine_show', '_controller' => 'App\\Controller\\AgriculteurDashboardController::machineShow'], ['id'], ['GET' => 0], null, false, true, null]],
        919 => [[['_route' => 'agri_machine_edit', '_controller' => 'App\\Controller\\AgriculteurDashboardController::machineEdit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        935 => [
            [['_route' => 'agri_machine_delete', '_controller' => 'App\\Controller\\AgriculteurDashboardController::machineDelete'], ['id'], ['POST' => 0], null, false, true, null],
            [['_route' => 'agri_agri_machine_show', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineShow'], ['id'], ['GET' => 0], null, false, true, null],
        ],
        954 => [[['_route' => 'agri_agri_machine_edit', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineEdit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        974 => [[['_route' => 'agri_agri_machine_delete', '_controller' => 'App\\Controller\\Materiels\\MachineController::machineDelete'], ['id'], ['POST' => 0], null, false, false, null]],
        1002 => [[['_route' => 'agri_maintenances_show', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        1021 => [[['_route' => 'agri_maintenances_edit', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1042 => [[['_route' => 'agri_maintenances_delete', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        1070 => [[['_route' => 'agri_maintenances_api_alerts', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::getAlerts'], ['id'], ['GET' => 0], null, false, true, null]],
        1110 => [[['_route' => 'agri_maintenances_api_reminder_detail', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::getReminderDetail'], ['id'], ['GET' => 0], null, false, true, null]],
        1149 => [[['_route' => 'agri_maintenances_api_generate_recommendation', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::generateRecommendation'], ['id'], ['POST' => 0], null, false, true, null]],
        1175 => [[['_route' => 'agri_maintenances_api_diagnostics', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::apiDiagnostics'], ['id'], ['GET' => 0], null, false, true, null]],
        1198 => [[['_route' => 'agri_maintenances_api_schedule', '_controller' => 'App\\Controller\\Materiels\\MaintenancesController::apiSchedule'], ['id'], ['GET' => 0], null, false, true, null]],
        1232 => [[['_route' => 'app_mouvement_new_alias', '_controller' => 'App\\Controller\\stocks\\MouvementController::gestionStockAlias'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        1273 => [[['_route' => 'agriculteur_evenement_inscription', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\EvenementController::inscrire'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        1317 => [[['_route' => 'agriculteur_participation_modifier', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\ParticipationController::modifier'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        1342 => [[['_route' => 'agriculteur_participation_annuler', '_controller' => 'App\\Controller\\Evenements\\Agriculteur\\ParticipationController::annuler'], ['id'], ['POST' => 0], null, false, true, null]],
        1381 => [[['_route' => 'app_abonnement_pdf', '_controller' => 'App\\Controller\\User\\AbonnementFrontController::pdf'], ['id'], ['GET' => 0], null, false, true, null]],
        1417 => [[['_route' => 'app_offre_souscrire', '_controller' => 'App\\Controller\\User\\OffreFrontController::souscrire'], ['id'], ['POST' => 0], null, false, true, null]],
        1457 => [[['_route' => 'app_ouvrier_edit', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::edit'], ['cin'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1475 => [[['_route' => 'app_ouvrier_delete', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::deleteOuvrier'], ['cin'], ['POST' => 0], null, false, false, null]],
        1493 => [[['_route' => 'app_ouvrier_taches', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::taches'], ['cin'], ['GET' => 0], null, false, false, null]],
        1510 => [[['_route' => 'app_ouvrier_tache_add', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::ajouterTache'], ['cin'], ['POST' => 0], null, false, false, null]],
        1543 => [[['_route' => 'app_tache_update_etat', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::updateEtatTache'], ['id'], ['POST' => 0], null, false, false, null]],
        1561 => [[['_route' => 'app_ouvrier_tache_delete', '_controller' => 'App\\Controller\\User\\Ouvrier_agriController::supprimerTache'], ['id'], ['POST' => 0], null, false, false, null]],
        1608 => [[['_route' => 'app_tache_by_ouvrier', '_controller' => 'App\\Controller\\User\\TacheFrontController::tachesByOuvrier'], ['cin'], ['GET' => 0], null, false, true, null]],
        1645 => [[['_route' => 'app_ouvriers_by_terrain', '_controller' => 'App\\Controller\\User\\TacheFrontController::ouvriersByTerrain'], ['idTerrain'], ['GET' => 0], null, false, false, null]],
        1671 => [[['_route' => 'app_assigner_ouvrier_terrain', '_controller' => 'App\\Controller\\User\\TacheFrontController::assignerOuvrierTerrain'], ['idTerrain'], ['POST' => 0], null, false, false, null]],
        1709 => [[['_route' => 'app_desassigner_ouvrier', '_controller' => 'App\\Controller\\User\\TacheFrontController::desassignerOuvrier'], ['cin'], ['POST' => 0], null, false, false, null]],
        1745 => [[['_route' => 'app_article_edit', '_controller' => 'App\\Controller\\stocks\\ArticleController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1754 => [[['_route' => 'app_article_delete', '_controller' => 'App\\Controller\\stocks\\ArticleController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        1787 => [[['_route' => 'app_mouvement_new', '_controller' => 'App\\Controller\\stocks\\ArticleController::gestionStock'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        1819 => [[['_route' => 'article_qr_code', '_controller' => 'App\\Controller\\stocks\\ArticleController::generateQRCode'], ['id'], ['GET' => 0], null, false, false, null]],
        1840 => [[['_route' => 'article_qr_code_download', '_controller' => 'App\\Controller\\stocks\\ArticleController::downloadQRCode'], ['id'], ['GET' => 0], null, false, false, null]],
        1853 => [[['_route' => 'article_qr_code_view', '_controller' => 'App\\Controller\\stocks\\ArticleController::viewQRCode'], ['id'], ['GET' => 0], null, false, false, null]],
        1871 => [[['_route' => 'app_article_show', '_controller' => 'App\\Controller\\stocks\\ArticleController::show'], ['id'], ['GET' => 0], null, false, false, null]],
        1894 => [[['_route' => 'article_scan_redirect', '_controller' => 'App\\Controller\\stocks\\ArticleController::scanRedirect'], ['id'], ['GET' => 0], null, false, false, null]],
        1917 => [[['_route' => 'agri_categories', '_controller' => 'App\\Controller\\stocks\\CategorieController::index'], [], ['GET' => 0], null, true, false, null]],
        1943 => [[['_route' => 'agri_categories_edit', '_controller' => 'App\\Controller\\stocks\\CategorieController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        1952 => [[['_route' => 'agri_categories_delete', '_controller' => 'App\\Controller\\stocks\\CategorieController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        1999 => [[['_route' => 'agri_plantes_edit', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2014 => [[['_route' => 'agri_plantes_delete', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        2024 => [[['_route' => 'agri_plantes_show', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2049 => [[['_route' => 'agri_plantes_changer_langue', '_controller' => 'App\\Controller\\Terrain\\AgriPlanteController::changerLangue'], ['locale'], null, null, false, true, null]],
        2088 => [[['_route' => 'agri_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2103 => [[['_route' => 'agri_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        2113 => [[['_route' => 'agri_rotations_show', '_controller' => 'App\\Controller\\Terrain\\AgriRotationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2151 => [[['_route' => 'agri_terrains_edit', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2166 => [[['_route' => 'agri_terrains_delete', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        2196 => [[['_route' => 'agri_terrains_certificat_propriete', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::certificatPropriete'], ['id'], ['GET' => 0], null, false, false, null]],
        2206 => [[['_route' => 'agri_terrains_show', '_controller' => 'App\\Controller\\Terrain\\AgriTerrainController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2236 => [[['_route' => 'app_animaux_show', '_controller' => 'App\\Controller\\Animals\\AnimauxController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2266 => [[['_route' => 'app_animaux_export_card', '_controller' => 'App\\Controller\\Animals\\AnimauxController::exportCard'], ['id'], ['GET' => 0], null, false, false, null]],
        2282 => [[['_route' => 'app_animaux_export_medical', '_controller' => 'App\\Controller\\Animals\\AnimauxController::exportMedical'], ['id'], ['GET' => 0], null, false, false, null]],
        2295 => [[['_route' => 'app_animaux_edit', '_controller' => 'App\\Controller\\Animals\\AnimauxController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2310 => [[['_route' => 'app_animaux_match', '_controller' => 'App\\Controller\\Animals\\AnimauxController::match'], ['id'], ['GET' => 0], null, false, false, null]],
        2320 => [[['_route' => 'app_animaux_delete', '_controller' => 'App\\Controller\\Animals\\AnimauxController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        2350 => [[['_route' => 'api_terrains_by_agriculteur', '_controller' => 'App\\Controller\\AuthController::terrainsByAgriculteur'], ['cinAgriculteur'], null, null, false, true, null]],
        2382 => [[['_route' => 'app_examens_show', '_controller' => 'App\\Controller\\Animals\\ExamensController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        2396 => [[['_route' => 'app_examens_edit', '_controller' => 'App\\Controller\\Animals\\ExamensController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2405 => [[['_route' => 'app_examens_delete', '_controller' => 'App\\Controller\\Animals\\ExamensController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        2445 => [[['_route' => 'evenement_modifier', '_controller' => 'App\\Controller\\Evenements\\EvenementController::modifier'], ['id'], null, null, false, true, null]],
        2472 => [[['_route' => 'evenement_supprimer', '_controller' => 'App\\Controller\\Evenements\\EvenementController::supprimer'], ['id'], null, null, false, true, null]],
        2519 => [[['_route' => 'categorie_evenement_modifier', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::modifier'], ['id'], null, null, false, true, null]],
        2568 => [[['_route' => 'categorie_evenement_supprimer', '_controller' => 'App\\Controller\\Evenements\\CategorieEvenementController::supprimer'], ['id'], null, null, false, true, null]],
        2596 => [[['_route' => 'app_set_locale', '_controller' => 'App\\Controller\\LocaleController::setLocale'], ['locale'], ['GET' => 0], null, false, true, null]],
        2652 => [[['_route' => 'ouvrier_evenement_inscription', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\EvenementController::inscrire'], ['id'], null, null, false, true, null]],
        2696 => [[['_route' => 'ouvrier_participation_modifier', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\ParticipationController::modifier'], ['id'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        2721 => [[['_route' => 'ouvrier_participation_annuler', '_controller' => 'App\\Controller\\Evenements\\Ouvrier\\ParticipationController::annuler'], ['id'], ['POST' => 0], null, false, true, null]],
        2758 => [[['_route' => 'ouvrier_article_show', '_controller' => 'App\\Controller\\OuvrierDashboardController::articleShow'], ['id'], ['GET' => 0], null, false, true, null]],
        2779 => [[['_route' => 'ouvrier_article_sortie', '_controller' => 'App\\Controller\\OuvrierDashboardController::sortie'], ['id'], ['POST' => 0], null, false, false, null]],
        2804 => [[['_route' => 'ouvrier_article_mouvements', '_controller' => 'App\\Controller\\OuvrierDashboardController::mouvements'], ['id'], ['GET' => 0], null, false, false, null]],
        2839 => [[['_route' => 'ouvrier_mouvement_modifier', '_controller' => 'App\\Controller\\OuvrierDashboardController::modifierMouvement'], ['id'], ['POST' => 0], null, false, false, null]],
        2879 => [[['_route' => 'ouvrier_tache_statut', '_controller' => 'App\\Controller\\User\\OuvrierController::changerStatut'], ['id', 'statut'], null, null, false, true, null]],
        2903 => [[['_route' => 'ouvrier_changer_langue', '_controller' => 'App\\Controller\\User\\OuvrierController::changerLangue'], ['locale'], null, null, false, true, null]],
        2929 => [[['_route' => 'app_offre_show', '_controller' => 'App\\Controller\\User\\OffreController::show'], ['idOffres'], ['GET' => 0], null, false, true, null]],
        2945 => [[['_route' => 'app_offre_pdf', '_controller' => 'App\\Controller\\User\\OffreController::pdf'], ['idOffres'], ['GET' => 0], null, false, false, null]],
        2958 => [[['_route' => 'app_offre_edit', '_controller' => 'App\\Controller\\User\\OffreController::edit'], ['idOffres'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        2973 => [[['_route' => 'app_offre_delete', '_controller' => 'App\\Controller\\User\\OffreController::delete'], ['idOffres'], ['POST' => 0], null, false, false, null]],
        3024 => [[['_route' => 'participation_modifier', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::modifier'], ['id'], null, null, false, true, null]],
        3051 => [[['_route' => 'participation_supprimer', '_controller' => 'App\\Controller\\Evenements\\ParticipationController::supprimer'], ['id'], null, null, false, true, null]],
        3087 => [[['_route' => 'admin_plantes_edit', '_controller' => 'App\\Controller\\Terrain\\PlanteController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        3102 => [[['_route' => 'admin_plantes_delete', '_controller' => 'App\\Controller\\Terrain\\PlanteController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        3112 => [[['_route' => 'admin_plantes_show', '_controller' => 'App\\Controller\\Terrain\\PlanteController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        3156 => [[['_route' => 'admin_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\RotationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        3171 => [[['_route' => 'admin_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\RotationController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        3181 => [[['_route' => 'admin_rotations_show', '_controller' => 'App\\Controller\\Terrain\\RotationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        3225 => [[['_route' => 'admin_rotationsagri_rotations_edit', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriEdit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        3240 => [[['_route' => 'admin_rotationsagri_rotations_delete', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriDelete'], ['id'], ['POST' => 0], null, false, false, null]],
        3250 => [[['_route' => 'admin_rotationsagri_rotations_show', '_controller' => 'App\\Controller\\Terrain\\RotationController::agriShow'], ['id'], ['GET' => 0], null, false, true, null]],
        3293 => [[['_route' => 'admin_terrains_edit', '_controller' => 'App\\Controller\\Terrain\\TerrainController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        3308 => [[['_route' => 'admin_terrains_delete', '_controller' => 'App\\Controller\\Terrain\\TerrainController::delete'], ['id'], ['POST' => 0], null, false, false, null]],
        3318 => [[['_route' => 'admin_terrains_show', '_controller' => 'App\\Controller\\Terrain\\TerrainController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        3344 => [[['_route' => 'app_tache_show', '_controller' => 'App\\Controller\\User\\TacheController::show'], ['idTache'], ['GET' => 0], null, false, true, null]],
        3360 => [[['_route' => 'app_tache_pdf', '_controller' => 'App\\Controller\\User\\TacheController::pdf'], ['idTache'], ['GET' => 0], null, false, false, null]],
        3373 => [[['_route' => 'app_tache_edit', '_controller' => 'App\\Controller\\User\\TacheController::edit'], ['idTache'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        3388 => [
            [['_route' => 'app_tache_delete', '_controller' => 'App\\Controller\\User\\TacheController::delete'], ['idTache'], ['POST' => 0], null, false, false, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
