<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Installation route
$routes->get('/install', 'InstallController::index');

// Firebase routes
$routes->get('/firebase', 'Firebase::index');
$routes->get('/firebase/config', 'Firebase::config');
$routes->get('/firebase/test', 'Firebase::test');

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // API Info
    $routes->get('/', 'ApiController::index');
    
    // Users API
    $routes->resource('users', ['controller' => 'UsersController']);
    $routes->get('users/active', 'UsersController::active');
    
    // Posts API (example)
    $routes->resource('posts', ['controller' => 'PostsController']);
    
    // File Upload API
    $routes->post('files/upload', 'FilesController::upload');
    $routes->post('files/upload-private', 'FilesController::uploadPrivate');
    $routes->post('files/upload-profile', 'FilesController::uploadProfile');
    $routes->get('files/info/(:any)', 'FilesController::info/$1');
    $routes->delete('files/remove', 'FilesController::remove');
    $routes->get('files/download/(:any)', 'FilesController::download/$1');
    $routes->get('files/list', 'FilesController::list');
    
    // Custom API endpoints
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/register', 'AuthController::register');
    $routes->get('auth/me', 'AuthController::me');
});
