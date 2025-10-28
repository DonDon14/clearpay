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
$routes->get('/register', 'Admin\LoginController::register');
$routes->post('/registerPost', 'Admin\LoginController::registerPost');
$routes->post('/verifyEmail', 'Admin\LoginController::verifyEmail');
$routes->post('/resendVerificationCode', 'Admin\LoginController::resendVerificationCode');
$routes->get('/forgotPassword', 'Admin\LoginController::forgotPassword');
$routes->post('/forgotPasswordPost', 'Admin\LoginController::forgotPasswordPost');
$routes->post('/verifyResetCode', 'Admin\LoginController::verifyResetCode');
$routes->post('/resetPassword', 'Admin\LoginController::resetPassword');

$routes->post('/clearSidebarFlag', 'Admin\DashboardController::clearSidebarFlag', ['filter' => 'auth']);
$routes->get('/dashboard', 'Admin\DashboardController::index', ['filter' => 'auth']);
$routes->get('/search', 'Admin\DashboardController::search', ['filter' => 'auth']);

// Sidebar Routes
$routes->get('/contributions', 'Admin\SidebarController::contributions', ['filter' => 'auth']);
$routes->get('/payers', 'Admin\SidebarController::payers', ['filter' => 'auth']);
$routes->post('/payers/save', 'Admin\SidebarController::savePayer', ['filter' => 'auth']);
$routes->get('/payers/get/(:num)', 'Admin\SidebarController::getPayer/$1', ['filter' => 'auth']);
$routes->get('/payers/get-details/(:num)', 'Admin\SidebarController::getPayerDetails/$1', ['filter' => 'auth']);
$routes->post('/payers/update/(:num)', 'Admin\SidebarController::updatePayer/$1', ['filter' => 'auth']);
$routes->get('/payers/export-pdf/(:num)', 'Admin\SidebarController::exportPayerPDF/$1', ['filter' => 'auth']);
$routes->get('/announcements', 'Admin\SidebarController::announcements', ['filter' => 'auth']);
$routes->get('/announcements/index', 'Admin\AnnouncementsController::index', ['filter' => 'auth']);
$routes->post('/announcements/save', 'Admin\AnnouncementsController::save', ['filter' => 'auth']);
$routes->get('/announcements/get/(:num)', 'Admin\AnnouncementsController::get/$1', ['filter' => 'auth']);
$routes->post('/announcements/delete/(:num)', 'Admin\AnnouncementsController::delete/$1', ['filter' => 'auth']);
$routes->post('/announcements/update-status/(:num)', 'Admin\AnnouncementsController::updateStatus/$1', ['filter' => 'auth']);
$routes->get('/analytics', 'Admin\Analytics::index', ['filter' => 'auth']);
$routes->get('/admin/analytics/export/(:any)', 'Admin\Analytics::export/$1', ['filter' => 'auth']);
$routes->get('/profile', 'Admin\SidebarController::profile', ['filter' => 'auth']);
$routes->post('/profile/update', 'Admin\SidebarController::update', ['filter' => 'auth']);
$routes->get('/settings', 'Admin\SidebarController::settings', ['filter' => 'auth']);

// Payment Methods Management Routes
$routes->get('/admin/settings/payment-methods', 'Admin\Settings\PaymentMethodController::index', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/data', 'Admin\Settings\PaymentMethodController::getData', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/create', 'Admin\Settings\PaymentMethodController::create', ['filter' => 'auth']);
$routes->post('/admin/settings/payment-methods/store', 'Admin\Settings\PaymentMethodController::store', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/edit/(:num)', 'Admin\Settings\PaymentMethodController::edit/$1', ['filter' => 'auth']);
$routes->post('/admin/settings/payment-methods/update/(:num)', 'Admin\Settings\PaymentMethodController::update/$1', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/delete/(:num)', 'Admin\Settings\PaymentMethodController::delete/$1', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/toggle-status/(:num)', 'Admin\Settings\PaymentMethodController::toggleStatus/$1', ['filter' => 'auth']);

// Payment Requests Management Routes
$routes->get('/payment-requests', 'Admin\DashboardController::paymentRequests', ['filter' => 'auth']);
$routes->post('/admin/approve-payment-request', 'Admin\DashboardController::approvePaymentRequest', ['filter' => 'auth']);
$routes->post('/admin/reject-payment-request', 'Admin\DashboardController::rejectPaymentRequest', ['filter' => 'auth']);
$routes->post('/admin/process-payment-request', 'Admin\DashboardController::processPaymentRequest', ['filter' => 'auth']);
$routes->get('/admin/get-payment-request-details', 'Admin\DashboardController::getPaymentRequestDetails', ['filter' => 'auth']);
$routes->post('/admin/delete-payment-request', 'Admin\DashboardController::deletePaymentRequest', ['filter' => 'auth']);

    // Payments Management Routes
    $routes->get('/payments', 'Admin\PaymentsController::index', ['filter' => 'auth']);
    $routes->post('/payments/save', 'Admin\PaymentsController::save', ['filter' => 'auth']);
    $routes->post('/payments/save-with-confirmation', 'Admin\PaymentsController::saveWithConfirmation', ['filter' => 'auth']);
    $routes->post('/payments/add-to-partial', 'Admin\PaymentsController::addToPartial', ['filter' => 'auth']);
    $routes->get('/payments/get-payment-history', 'Admin\PaymentsController::getPaymentHistory', ['filter' => 'auth']);
    $routes->get('/payments/get-payment-details', 'Admin\PaymentsController::getPaymentDetails', ['filter' => 'auth']);
    $routes->post('/payments/delete', 'Admin\PaymentsController::deletePayment', ['filter' => 'auth']);
    $routes->post('/payments/delete-group', 'Admin\PaymentsController::deletePaymentGroup', ['filter' => 'auth']);
$routes->get('/payments/check-unpaid-contributions', 'Admin\PaymentsController::checkUnpaidContributions', ['filter' => 'auth']);
$routes->get('/payments/check-fully-paid-contributions', 'Admin\PaymentsController::checkFullyPaidContributions', ['filter' => 'auth']);
$routes->get('/payments/check-contribution-status', 'Admin\PaymentsController::checkContributionStatus', ['filter' => 'auth']);
$routes->get('/payments/get-contribution-warning-data', 'Admin\PaymentsController::getContributionWarningData', ['filter' => 'auth']);
$routes->post('/payments/update/(:num)', 'Admin\PaymentsController::update/$1', ['filter' => 'auth']);
$routes->get('/payments/recent', 'Admin\PaymentsController::recent', ['filter' => 'auth']);
$routes->get('/payments/search-payers', 'Admin\PaymentsController::searchPayers', ['filter' => 'auth']);
$routes->get('/payments/verify/(:any)', 'Admin\PaymentsController::verify/$1', ['filter' => 'auth']);
$routes->delete('/payments/delete/(:num)', 'Admin\PaymentsController::delete/$1', ['filter' => 'auth']);
$routes->get('/payments/get-details/(:num)', 'Admin\PaymentsController::getDetails/$1', ['filter' => 'auth']);
$routes->get('/payments/by-contribution/(:num)', 'Admin\PaymentsController::byContribution/$1', ['filter' => 'auth']);

// Payers Management Routes
$routes->post('/payers/create', 'Admin\PayersController::create', ['filter' => 'auth']);

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

// Payer Routes
$routes->get('payer/login', 'Payer\LoginController::index');
$routes->post('payer/loginPost', 'Payer\LoginController::loginPost');

        $routes->group('payer', function($routes) {
            $routes->get('dashboard', 'Payer\DashboardController::index');
            $routes->get('my-data', 'Payer\DashboardController::myData');
            $routes->post('update-profile', 'Payer\DashboardController::updateProfile');
            $routes->post('upload-profile-picture', 'Payer\DashboardController::uploadProfilePicture');
            $routes->get('announcements', 'Payer\DashboardController::announcements');
        $routes->get('contributions', 'Payer\DashboardController::contributions');
        $routes->get('get-contribution-payments/(:num)', 'Payer\DashboardController::getContributionPayments/$1');
        $routes->get('payment-history', 'Payer\DashboardController::paymentHistory');
        $routes->get('payment-requests', 'Payer\DashboardController::paymentRequests');
        $routes->post('submit-payment-request', 'Payer\DashboardController::submitPaymentRequest');
        $routes->get('get-contribution-details', 'Payer\DashboardController::getContributionDetails');
        $routes->get('check-new-activities', 'Payer\DashboardController::checkNewActivities');
        $routes->post('mark-activity-read/(:num)', 'Payer\DashboardController::markActivityAsRead/$1');
        $routes->get('get-all-activities', 'Payer\DashboardController::getAllActivities');
        $routes->get('test-activity', 'Payer\DashboardController::testActivity');
        $routes->get('logout', 'Payer\LoginController::logout');
    });