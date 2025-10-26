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
$routes->get('/payers', 'Admin\SidebarController::payers', ['filter' => 'auth']);
$routes->post('/payers/save', 'Admin\SidebarController::savePayer', ['filter' => 'auth']);
$routes->get('/announcements', 'Admin\SidebarController::announcements', ['filter' => 'auth']);
$routes->get('/analytics', 'Admin\SidebarController::analytics', ['filter' => 'auth']);
$routes->get('/profile', 'Admin\SidebarController::profile', ['filter' => 'auth']);
$routes->post('/profile/update', 'Admin\SidebarController::update', ['filter' => 'auth']);
$routes->get('/settings', 'Admin\SidebarController::settings', ['filter' => 'auth']);

// Payments Management Routes
$routes->get('dashboard/recentPayments', 'Admin\DashboardController::recentPayments', ['filter' => 'auth']);
$routes->post('/payments/save', 'Admin\PaymentsController::save', ['filter' => 'auth']);
$routes->post('/payments/add-to-partial', 'Admin\PaymentsController::addToPartial', ['filter' => 'auth']);
$routes->post('/payments/update/(:num)', 'Admin\PaymentsController::update/$1', ['filter' => 'auth']);
$routes->get('/payments/recent', 'Admin\PaymentsController::recent', ['filter' => 'auth']);
$routes->get('/payments/search-payers', 'Admin\PaymentsController::searchPayers', ['filter' => 'auth']);
$routes->get('/payments/verify/(:any)', 'Admin\PaymentsController::verify/$1', ['filter' => 'auth']);
$routes->delete('/payments/delete/(:num)', 'Admin\PaymentsController::delete/$1', ['filter' => 'auth']);
$routes->get('/payments/by-contribution/(:num)', 'Admin\PaymentsController::byContribution/$1', ['filter' => 'auth']);

// Contributions Management Routes
$routes->post('/contributions/save', 'Admin\ContributionsController::save', ['filter' => 'auth']);
$routes->get('/contributions/get/(:num)', 'Admin\ContributionsController::get/$1', ['filter' => 'auth']);
$routes->post('/contributions/update/(:num)', 'Admin\ContributionsController::update/$1', ['filter' => 'auth']);
$routes->delete('/contributions/delete/(:num)', 'Admin\ContributionsController::delete/$1', ['filter' => 'auth']);
$routes->post('/contributions/toggle-status/(:num)', 'Admin\ContributionsController::toggleStatus/$1', ['filter' => 'auth']);

// QR Receipt Routes
$routes->post('/qr-receipt/generate/(:num)', 'Admin\QRReceiptController::generate/$1', ['filter' => 'auth']);
$routes->get('/receipts/qr/(:num)', 'Admin\QRReceiptController::getQRImage/$1');
$routes->get('/receipts/download/(:num)', 'Admin\QRReceiptController::download/$1', ['filter' => 'auth']);
$routes->get('/verify/receipt/(:any)', 'Admin\QRReceiptController::verify/$1');
$routes->get('/qr-receipt/show/(:num)', 'Admin\QRReceiptController::showReceipt/$1', ['filter' => 'auth']);