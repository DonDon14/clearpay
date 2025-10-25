<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route - redirect to admin login

// Admin Routes
$routes->get('/', 'Admin\LoginController::index');
$routes->post('/loginPost', 'Admin\LoginController::loginPost');
$routes->get('/logout', 'Admin\LoginController::logout');

$routes->get('/dashboard', 'Admin\DashboardController::index', ['filter' => 'auth']);

// Sidebar Routes
$routes->get('/payments', 'Admin\SidebarController::payments', ['filter' => 'auth']);
$routes->get('/contributions', 'Admin\SidebarController::contributions', ['filter' => 'auth']);
$routes->get('/partial-payments', 'Admin\SidebarController::partialPayments', ['filter' => 'auth']);
$routes->get('/history', 'Admin\SidebarController::history', ['filter' => 'auth']);
$routes->get('/payers', 'Admin\SidebarController::payers', ['filter' => 'auth']);
$routes->get('/announcements', 'Admin\SidebarController::announcements', ['filter' => 'auth']);
$routes->get('/analytics', 'Admin\SidebarController::analytics', ['filter' => 'auth']);
$routes->get('/profile', 'Admin\SidebarController::profile', ['filter' => 'auth']);
$routes->get('/settings', 'Admin\SidebarController::settings', ['filter' => 'auth']);

// Payments Management Routes
$routes->get('dashboard/recentPayments', 'Admin\DashboardController::recentPayments', ['filter' => 'auth']);
$routes->post('/payments/save', 'Admin\PaymentsController::save', ['filter' => 'auth']);

// Contributions Management Routes
$routes->post('/contributions/save', 'Admin\ContributionsController::save', ['filter' => 'auth']);