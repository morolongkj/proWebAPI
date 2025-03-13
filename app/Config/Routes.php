<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

service('auth')->routes($routes);
$routes->group("api", ["namespace" => "App\Controllers"], function ($routes) {

    $routes->get("invalid-access", "AuthController::accessDenied");

    // Post
    $routes->post("register", "AuthController::register");

    // Post
    $routes->post("login", "AuthController::login");

    // Get
    $routes->get("profile", "AuthController::profile", ['filter' => 'jwt']);
    // PUT
    $routes->put("profile", "AuthController::updateProfile", ['filter' => 'jwt']);
    // Change Password
    $routes->post('auth/change-password', 'AuthController::changePassword', ['filter' => 'jwt']);
    $routes->delete('auth/deactivate-account', 'AuthController::deactivateAccount', ['filter' => 'jwt']);

    // Get
    $routes->get("logout", "AuthController::logout", ["filter" => "jwt"]);

    $routes->post("signin", "AuthController::jwtLogin");
    $routes->post("request-reset-password", "AuthController::requestPasswordReset");
    $routes->post("reset-password", "AuthController::resetPassword");

    // Get Questionnaires
    $routes->get("questionnaires", "QuestionnairesController::index", ['filter' => 'jwt']);
    // Post Questionnaire
    $routes->post("questionnaires", "QuestionnairesController::create", ['filter' => 'jwt']);
    // Get Single Questionnaire
    $routes->get("questionnaires/(:segment)", "QuestionnairesController::show/$1", ['filter' => 'jwt']);
    // Put Questionnaire
    $routes->put("questionnaires/(:segment)", "QuestionnairesController::update/$1", ['filter' => 'jwt']);
    // Delete Questionnaire
    $routes->delete("questionnaires/(:segment)", "QuestionnairesController::delete/$1", ['filter' => 'jwt']);

    // Get Questionnaire Documents
    $routes->get("questionnaire-documents", "QuestionnaireDocumentsController::index", ['filter' => 'jwt']);
// Post Questionnaire Document
    $routes->post("questionnaire-documents", "QuestionnaireDocumentsController::create", ['filter' => 'jwt']);
// Get Single Questionnaire Document
    $routes->get("questionnaire-documents/(:segment)", "QuestionnaireDocumentsController::show/$1", ['filter' => 'jwt']);
// Put Questionnaire Document
    $routes->put("questionnaire-documents/(:segment)", "QuestionnaireDocumentsController::update/$1", ['filter' => 'jwt']);
// Delete Questionnaire Document
    $routes->delete("questionnaire-documents/(:segment)", "QuestionnaireDocumentsController::delete/$1", ['filter' => 'jwt']);

// Get Prequalification Stages
    $routes->get("prequalification-stages", "PrequalificationStagesController::index", ['filter' => 'jwt']);
// Post Prequalification Stage
    $routes->post("prequalification-stages", "PrequalificationStagesController::create", ['filter' => 'jwt']);
// Get Single Prequalification Stage
    $routes->get("prequalification-stages/(:segment)", "PrequalificationStagesController::show/$1", ['filter' => 'jwt']);
// Put Prequalification Stage
    $routes->put("prequalification-stages/(:segment)", "PrequalificationStagesController::update/$1", ['filter' => 'jwt']);
// Delete Prequalification Stage
    $routes->delete("prequalification-stages/(:segment)", "PrequalificationStagesController::delete/$1", ['filter' => 'jwt']);

// Get Users
    $routes->get("users", "UsersController::index", ['filter' => 'jwt']);
    // Get Users
    $routes->get("users/(:segment)", "UsersController::show/$1", ['filter' => 'jwt']);
// Ban User
    $routes->post("users/ban/(:segment)", "UsersController::ban/$1", ['filter' => 'jwt']);
// unBan User
    $routes->post("users/unban/(:segment)", "UsersController::unban/$1", ['filter' => 'jwt']);
// Add Role
    $routes->post("users/addrole/(:segment)", "UsersController::addRole/$1", ['filter' => 'jwt']);
// Remove Role
    $routes->post("users/removerole/(:segment)", "UsersController::removeRole/$1", ['filter' => 'jwt']);
// Change Password
    $routes->post("users/change-password/(:segment)", "UsersController::changePassword/$1", ['filter' => 'jwt']);
// Get Users Count
    $routes->get("users/count", "UsersController::get_users_count", ['filter' => 'jwt']);
// Put User
    $routes->put("users/(:segment)", "UsersController::update/$1", ['filter' => 'jwt']);

    $routes->resource('categories', [
        'controller' => 'CategoriesController',
        'filter'     => 'jwt',
    ]);

    $routes->post("categories/batch", "CategoriesController::create_batch", ['filter' => 'jwt']);

    $routes->resource('products', [
        'controller' => 'ProductsController',
        'filter'     => 'jwt',
    ]);

    $routes->resource('tenders', [
        'controller' => 'TendersController',
        'filter'     => 'jwt',
    ]);
    $routes->post('tenders/approve/(:segment)', 'TendersController::approve/$1');
    $routes->post('tenders/reject/(:segment)', 'TendersController::reject/$1');
    $routes->resource('tender-attachments', [
        'controller' => 'TenderAttachmentsController',
        'filter'     => 'jwt',
    ]);
    $routes->resource('tender-products', [
        'controller' => 'TenderProductsController',
        'filter'     => 'jwt',
    ]);
    $routes->resource('tender-status', [
        'controller' => 'TenderStatusController',
        'filter'     => 'jwt',
    ]);
    $routes->resource('tender-status-history', [
        'controller' => 'TenderStatusHistoryController',
        'filter'     => 'jwt',
    ]);
    $routes->resource('tender-approvals', [
        'controller' => 'TenderApprovalsController',
        'filter'     => 'jwt',
    ]);

    $routes->resource('companies', [
        'controller' => 'CompaniesController',
        'filter'     => 'jwt',
    ]);

    $routes->resource('documents', [
        'controller' => 'DocumentsController',
        'filter'     => 'jwt',
    ]);

    $routes->resource('company-users', [
        'controller' => 'CompanyUsersController',
        'filter'     => 'jwt',
    ]);

    $routes->get('company-users/company/(:segment)', 'CompanyUsersController::usersByCompany/$1', ['filter' => 'jwt']);
    $routes->get('company-users/user/(:segment)', 'CompanyUsersController::companiesByUser/$1', ['filter' => 'jwt']);

    $routes->resource('prequalified-companies', [
        'controller' => 'PrequalifiedCompaniesController',
        'filter'     => 'jwt',
    ]);

    $routes->resource('questionnaire-products', [
        'controller' => 'QuestionnaireProductsController',
        'filter'     => 'jwt',
    ]);

    $routes->get('questionnaire-submissions', 'QuestionnairesController::listSubmissions', ['filter' => 'jwt']);
    $routes->post('questionnaires/submit', 'QuestionnairesController::submit', ['filter' => 'jwt']);
    $routes->get('questionnaire-submissions/(:any)', 'QuestionnairesController::getSubmissionById/$1', ['filter' => 'jwt']);
    $routes->put('questionnaire-submissions/update-status/(:any)', 'QuestionnairesController::updateStatus/$1', ['filter' => 'jwt']);

    $routes->resource('prequalifications', [
        'controller' => 'PrequalificationsController',
        'filter'     => 'jwt',
    ]);

    $routes->resource('prequalification-attachments', [
        'controller' => 'PrequalificationAttachmentsController',
        'filter'     => 'jwt',
    ]);

    $routes->resource('prequalification-status', [
        'controller' => 'PrequalificationStatusController',
        'filter'     => 'jwt',
    ]);
    $routes->resource('prequalification-status-history', [
        'controller' => 'PrequalificationStatusHistoryController',
        'filter'     => 'jwt',
    ]);

    $routes->resource('notifications', [
        'controller' => 'NotificationsController',
        'filter'     => 'jwt',
    ]);
    $routes->post('notifications/(:segment)/mark-as-read', 'NotificationsController::markAsRead/$1');
    $routes->get('notifications/unread', 'NotificationsController::getUnreadNotifications');

    $routes->get('bids/financial-evaluation/(:segment)', 'BidsController::financialEvaluation/$1');
    $routes->resource('bids', [
        'controller' => 'BidsController',
        'filter'     => 'jwt',
    ]);

                                                                                                                                 // Custom routes for custom methods
    $routes->get('prequalified-companies/product/(:segment)', 'PrequalifiedCompaniesController::companiesByProduct/$1');         // GET /prequalified-companies/product/{productId}
    $routes->get('prequalified-companies/company/(:segment)', 'PrequalifiedCompaniesController::productsByCompany/$1');          // GET /prequalified-companies/company/{companyId}
    $routes->get('prequalified-companies/check/(:segment)/(:segment)', 'PrequalifiedCompaniesController::isPrequalified/$1/$2'); // GET /prequalified-companies/check/{companyId}/{productId}

    $routes->get('uploads/(:segment)', 'FileController::serveFile/$1');
});

// $routes->get('uploads/(:any)', 'ImageController::serveImage/$1');
$routes->get('migrate', 'MigrateController::migrate');
// $routes->get('uploads/(:any)', 'FileController::serveFile/$1');
