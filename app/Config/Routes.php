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
    // Ban User
    $routes->post("users/ban/(:segment)", "UsersController::ban/$1", ['filter' => 'jwt']);
    // unBan User
    $routes->post("users/unban/(:segment)", "UsersController::unban/$1", ['filter' => 'jwt']);
    // Add Role
    $routes->post("users/addrole/(:segment)", "UsersController::addRole/$1", ['filter' => 'jwt']);
    // Remove Role
    $routes->post("users/removerole/(:segment)", "UsersController::removeRole/$1", ['filter' => 'jwt']);


});

$routes->get('uploads/(:any)', 'ImageController::serveImage/$1');

