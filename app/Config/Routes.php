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
$routes->get('/admin/dashboard/pending-payment-requests-count', 'Admin\DashboardController::getPendingPaymentRequestsCount', ['filter' => 'auth']);
$routes->get('/admin/dashboard/pending-refund-requests-count', 'Admin\DashboardController::getPendingRefundRequestsCount', ['filter' => 'auth']);
$routes->get('/search', 'Admin\DashboardController::search', ['filter' => 'auth']);

// Help & Support Routes (must be before catch-all routes)
$routes->get('/help', 'Admin\HelpController::index', ['filter' => 'auth']);
$routes->get('/help/', 'Admin\HelpController::index', ['filter' => 'auth']);
$routes->get('/help/index.html', 'Admin\HelpController::index', ['filter' => 'auth']);
$routes->get('/help/user-manual', 'Admin\UserManualController::index', ['filter' => 'auth']);
$routes->get('/help/api-documentation', 'Admin\ApiDocumentationController::index', ['filter' => 'auth']);

// Sidebar Routes
$routes->get('/contributions', 'Admin\SidebarController::contributions', ['filter' => 'auth']);
$routes->get('/payers', 'Admin\SidebarController::payers', ['filter' => 'auth']);
$routes->post('/payers/save', 'Admin\SidebarController::savePayer', ['filter' => 'auth']);
$routes->get('/payers/get/(:num)', 'Admin\SidebarController::getPayer/$1', ['filter' => 'auth']);
$routes->get('/payers/get-details/(:num)', 'Admin\SidebarController::getPayerDetails/$1', ['filter' => 'auth']);
$routes->post('/payers/update/(:num)', 'Admin\SidebarController::updatePayer/$1', ['filter' => 'auth']);
$routes->post('/payers/delete/(:num)', 'Admin\SidebarController::deletePayer/$1', ['filter' => 'auth']);
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
// Backup routes
$routes->post('/admin/backup/create', 'Admin\BackupController::createBackup', ['filter' => 'auth']);
$routes->get('/admin/backup/download/(:any)', 'Admin\BackupController::downloadBackup/$1', ['filter' => 'auth']);
$routes->get('/admin/backup/list', 'Admin\BackupController::listBackups', ['filter' => 'auth']);
// System routes
$routes->get('/admin/system/info', 'Admin\SystemController::getSystemInfo', ['filter' => 'auth']);
$routes->get('/admin/system/download-logs', 'Admin\SystemController::downloadLogs', ['filter' => 'auth']);
$routes->post('/admin/system/clear-cache', 'Admin\SystemController::clearCache', ['filter' => 'auth']);
$routes->post('/admin/system/update-version', 'Admin\SystemController::updateVersion', ['filter' => 'auth']);
// Email settings routes
$routes->get('/admin/email-settings/config', 'Admin\EmailSettingsController::getConfig', ['filter' => 'auth']);
$routes->post('/admin/email-settings/config', 'Admin\EmailSettingsController::updateConfig', ['filter' => 'auth']);
$routes->post('/admin/email-settings/test-email', 'Admin\EmailSettingsController::testEmail', ['filter' => 'auth']);
$routes->get('/admin/email-settings/templates', 'Admin\EmailSettingsController::getTemplates', ['filter' => 'auth']);
$routes->post('/admin/email-settings/templates', 'Admin\EmailSettingsController::updateTemplate', ['filter' => 'auth']);
$routes->post('/admin/email-settings/toggle-notifications', 'Admin\EmailSettingsController::toggleNotifications', ['filter' => 'auth']);
$routes->get('/admin/email-settings/notifications-status', 'Admin\EmailSettingsController::getNotificationsStatus', ['filter' => 'auth']);
$routes->get('profile/get', 'Admin\SidebarController::getProfile');

// Payment Methods Management Routes
$routes->get('/admin/settings/payment-methods', 'Admin\Settings\PaymentMethodController::index', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/data', 'Admin\Settings\PaymentMethodController::getData', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/create', 'Admin\Settings\PaymentMethodController::create', ['filter' => 'auth']);
$routes->post('/admin/settings/payment-methods/store', 'Admin\Settings\PaymentMethodController::store', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/edit/(:num)', 'Admin\Settings\PaymentMethodController::edit/$1', ['filter' => 'auth']);
$routes->post('/admin/settings/payment-methods/update/(:num)', 'Admin\Settings\PaymentMethodController::update/$1', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/delete/(:num)', 'Admin\Settings\PaymentMethodController::delete/$1', ['filter' => 'auth']);
$routes->post('/admin/settings/payment-methods/delete/(:num)', 'Admin\Settings\PaymentMethodController::delete/$1', ['filter' => 'auth']);
$routes->get('/admin/settings/payment-methods/toggle-status/(:num)', 'Admin\Settings\PaymentMethodController::toggleStatus/$1', ['filter' => 'auth']);
$routes->post('/admin/settings/payment-methods/toggle-status/(:num)', 'Admin\Settings\PaymentMethodController::toggleStatus/$1', ['filter' => 'auth']);
$routes->options('/admin/settings/payment-methods/instructions/(:any)', 'Admin\Settings\PaymentMethodController::handleInstructionsOptions');
$routes->get('/admin/settings/payment-methods/instructions/(:any)', 'Admin\Settings\PaymentMethodController::getInstructions/$1');

// Refund Methods Management Routes
$routes->get('/admin/settings/refund-methods/data', 'Admin\Settings\RefundMethodController::getData', ['filter' => 'auth']);
$routes->post('/admin/settings/refund-methods/store', 'Admin\Settings\RefundMethodController::store', ['filter' => 'auth']);
$routes->post('/admin/settings/refund-methods/update/(:num)', 'Admin\Settings\RefundMethodController::update/$1', ['filter' => 'auth']);
$routes->post('/admin/settings/refund-methods/delete/(:num)', 'Admin\Settings\RefundMethodController::delete/$1', ['filter' => 'auth']);
$routes->post('/admin/settings/refund-methods/toggle-status/(:num)', 'Admin\Settings\RefundMethodController::toggleStatus/$1', ['filter' => 'auth']);

// Contribution Categories Management Routes
$routes->get('/admin/settings/contribution-categories/data', 'Admin\Settings\ContributionCategoryController::getData', ['filter' => 'auth']);
$routes->post('/admin/settings/contribution-categories/store', 'Admin\Settings\ContributionCategoryController::store', ['filter' => 'auth']);
$routes->post('/admin/settings/contribution-categories/update/(:num)', 'Admin\Settings\ContributionCategoryController::update/$1', ['filter' => 'auth']);
$routes->post('/admin/settings/contribution-categories/delete/(:num)', 'Admin\Settings\ContributionCategoryController::delete/$1', ['filter' => 'auth']);
$routes->post('/admin/settings/contribution-categories/toggle-status/(:num)', 'Admin\Settings\ContributionCategoryController::toggleStatus/$1', ['filter' => 'auth']);

// Admin Notifications Routes
$routes->get('/admin/check-new-activities', 'Admin\DashboardController::checkNewActivities', ['filter' => 'auth']);
$routes->post('/admin/mark-activity-read/(:num)', 'Admin\DashboardController::markActivityAsRead/$1', ['filter' => 'auth']);
$routes->post('/admin/mark-all-activities-read', 'Admin\DashboardController::markAllAsRead', ['filter' => 'auth']);
$routes->get('/admin/get-all-activities', 'Admin\DashboardController::getAllActivities', ['filter' => 'auth']);
$routes->get('/admin/get-unread-count', 'Admin\DashboardController::getUnreadCount', ['filter' => 'auth']);

// Payment Requests Management Routes
$routes->get('/payment-requests', 'Admin\DashboardController::paymentRequests', ['filter' => 'auth']);
$routes->post('/admin/approve-payment-request', 'Admin\DashboardController::approvePaymentRequest', ['filter' => 'auth']);
$routes->post('/admin/reject-payment-request', 'Admin\DashboardController::rejectPaymentRequest', ['filter' => 'auth']);
$routes->post('/admin/process-payment-request', 'Admin\DashboardController::processPaymentRequest', ['filter' => 'auth']);
$routes->get('/admin/get-payment-request-details', 'Admin\DashboardController::getPaymentRequestDetails', ['filter' => 'auth']);
$routes->post('/admin/delete-payment-request', 'Admin\DashboardController::deletePaymentRequest', ['filter' => 'auth']);

// Refunds Management Routes
$routes->get('/refunds', 'Admin\RefundsController::index', ['filter' => 'auth']);
$routes->post('/admin/refunds/process', 'Admin\RefundsController::processRefund', ['filter' => 'auth']);
$routes->get('/admin/refunds/get-payment-details', 'Admin\RefundsController::getPaymentDetails', ['filter' => 'auth']);
    $routes->get('/admin/refunds/get-payment-groups', 'Admin\RefundsController::getPaymentGroups', ['filter' => 'auth']);
    $routes->get('/admin/refunds/get-payment-group-details', 'Admin\RefundsController::getPaymentGroupDetails', ['filter' => 'auth']);
$routes->post('/admin/refunds/approve', 'Admin\RefundsController::approveRequest', ['filter' => 'auth']);
$routes->post('/admin/refunds/complete', 'Admin\RefundsController::completeRefund', ['filter' => 'auth']);
$routes->post('/admin/refunds/reject', 'Admin\RefundsController::rejectRequest', ['filter' => 'auth']);
$routes->get('/admin/refunds/get-details', 'Admin\RefundsController::getRefundDetails', ['filter' => 'auth']);

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
$routes->get('/payments/export-contribution-pdf/(:num)', 'Admin\PaymentsController::exportContributionPaymentsPDF/$1', ['filter' => 'auth']);

// Payers Management Routes
$routes->post('/payers/create', 'Admin\PayersController::create', ['filter' => 'auth']);
$routes->get('/payers/export/pdf', 'Admin\PayersController::exportPDF', ['filter' => 'auth']);
$routes->get('/payers/export/csv', 'Admin\PayersController::exportCSV', ['filter' => 'auth']);
$routes->get('/payment_methods/test', 'Admin\PaymentMethodsController::test', ['filter' => 'auth']);

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
$routes->options('api/payer/login', 'Payer\LoginController::handleOptions'); // CORS preflight
$routes->post('api/payer/login', 'Payer\LoginController::mobileLogin'); // Mobile API endpoint
$routes->get('payer/forgotPassword', 'Payer\LoginController::forgotPassword');
$routes->post('payer/forgotPasswordPost', 'Payer\LoginController::forgotPasswordPost');
$routes->post('payer/verifyResetCode', 'Payer\LoginController::verifyResetCode');
$routes->post('payer/resetPassword', 'Payer\LoginController::resetPassword');
// Mobile API endpoints for forgot password
$routes->options('api/payer/forgot-password', 'Payer\LoginController::handleOptions');
$routes->post('api/payer/forgot-password', 'Payer\LoginController::mobileForgotPassword');
$routes->options('api/payer/verify-reset-code', 'Payer\LoginController::handleOptions');
$routes->post('api/payer/verify-reset-code', 'Payer\LoginController::mobileVerifyResetCode');
$routes->options('api/payer/reset-password', 'Payer\LoginController::handleOptions');
$routes->post('api/payer/reset-password', 'Payer\LoginController::mobileResetPassword');
$routes->get('payer/signup', 'Payer\SignupController::index');
$routes->post('payer/signupPost', 'Payer\SignupController::signupPost');
$routes->post('payer/verifyEmail', 'Payer\SignupController::verifyEmail');
$routes->post('payer/resendVerificationCode', 'Payer\SignupController::resendVerificationCode');
// Mobile API endpoints for signup
$routes->options('api/payer/signup', 'Payer\SignupController::handleOptions');
$routes->post('api/payer/signup', 'Payer\SignupController::mobileSignup');
$routes->options('api/payer/verify-email', 'Payer\SignupController::handleOptions');
$routes->post('api/payer/verify-email', 'Payer\SignupController::mobileVerifyEmail');
$routes->options('api/payer/resend-verification', 'Payer\SignupController::handleOptions');
$routes->post('api/payer/resend-verification', 'Payer\SignupController::mobileResendVerificationCode');

        // Mobile API routes (no auth filter - will check in controller)
        // OPTIONS routes for CORS preflight
        $routes->options('api/payer/dashboard', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/contributions', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/payment-history', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/announcements', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/payment-requests', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/payment-methods', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/submit-payment-request', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/refund-requests', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/refund-methods', 'Payer\\DashboardController::handleOptions');
        $routes->options('api/payer/submit-refund-request', 'Payer\\DashboardController::handleOptions');
        // GET routes
        $routes->get('api/payer/dashboard', 'Payer\\DashboardController::mobileDashboard');
        $routes->get('api/payer/contributions', 'Payer\\DashboardController::mobileContributions');
        $routes->get('api/payer/payment-history', 'Payer\\DashboardController::mobilePaymentHistory');
        $routes->get('api/payer/announcements', 'Payer\\DashboardController::mobileAnnouncements');
        $routes->get('api/payer/payment-requests', 'Payer\\DashboardController::mobilePaymentRequests');
        $routes->get('api/payer/refund-requests', 'Payer\\DashboardController::refundRequests');
        $routes->get('api/payer/refund-methods', 'Payer\\DashboardController::getActiveRefundMethods');
        // POST routes
        $routes->post('api/payer/submit-refund-request', 'Payer\\DashboardController::submitRefundRequest');
        $routes->get('api/payer/payment-methods', 'Payer\\DashboardController::getActivePaymentMethods');
        // POST routes (API endpoints - no auth filter, will check in controller)
        $routes->post('api/payer/submit-payment-request', 'Payer\\DashboardController::submitPaymentRequest');

        // API endpoints for mobile (no auth filter - will check in controller)
        $routes->options('api/payer/get-contribution-payments/(:num)', 'Payer\\DashboardController::handleOptions');
        $routes->get('api/payer/get-contribution-payments/(:num)', 'Payer\\DashboardController::getContributionPayments/$1');
        $routes->options('api/payer/get-contribution-details', 'Payer\\DashboardController::handleOptions');
        $routes->get('api/payer/get-contribution-details', 'Payer\\DashboardController::getContributionDetails');
        // Refund API endpoints (no auth filter - will check in controller)
        $routes->options('api/payer/refund-requests', 'Payer\\DashboardController::handleOptions');
        $routes->get('api/payer/refund-requests', 'Payer\\DashboardController::refundRequests');
        $routes->options('api/payer/refund-methods', 'Payer\\DashboardController::handleOptions');
        $routes->get('api/payer/refund-methods', 'Payer\\DashboardController::getActiveRefundMethods');
        $routes->options('api/payer/submit-refund-request', 'Payer\\DashboardController::handleOptions');
        $routes->post('api/payer/submit-refund-request', 'Payer\\DashboardController::submitRefundRequest');
        // Profile picture upload API endpoint (no auth filter - will check in controller)
        $routes->options('api/payer/upload-profile-picture', 'Payer\\DashboardController::handleOptions');
        $routes->post('api/payer/upload-profile-picture', 'Payer\\DashboardController::uploadProfilePicture');
        // Update profile API endpoint (no auth filter - will check in controller)
        $routes->options('payer/update-profile', 'Payer\\DashboardController::handleOptions');
        $routes->post('payer/update-profile', 'Payer\\DashboardController::updateProfile');
        // Check new activities API endpoint (no auth filter - will check in controller)
        $routes->options('api/payer/check-new-activities', 'Payer\\DashboardController::handleOptions');
        $routes->get('api/payer/check-new-activities', 'Payer\\DashboardController::checkNewActivities');
        // Get all activities API endpoint (no auth filter - will check in controller)
        $routes->options('api/payer/get-all-activities', 'Payer\\DashboardController::handleOptions');
        $routes->get('api/payer/get-all-activities', 'Payer\\DashboardController::getAllActivities');
        // Mark activity as read API endpoint (no auth filter - will check in controller)
        $routes->options('api/payer/mark-activity-read/(:num)', 'Payer\\DashboardController::handleOptions');
        $routes->post('api/payer/mark-activity-read/(:num)', 'Payer\\DashboardController::markActivityAsRead/$1');
        
        $routes->group('payer', ['filter' => 'payerAuth'], function($routes) {
            $routes->get('dashboard', 'Payer\\DashboardController::index');
            $routes->get('my-data', 'Payer\\DashboardController::myData');
            $routes->post('update-profile', 'Payer\\DashboardController::updateProfile');
            $routes->post('upload-profile-picture', 'Payer\\DashboardController::uploadProfilePicture');
            $routes->get('announcements', 'Payer\\DashboardController::announcements');
            $routes->get('contributions', 'Payer\\DashboardController::contributions');
            $routes->get('get-contribution-payments/(:num)', 'Payer\\DashboardController::getContributionPayments/$1');
            $routes->get('payment-history', 'Payer\\DashboardController::paymentHistory');
            $routes->get('payment-requests', 'Payer\\DashboardController::paymentRequests');
            $routes->post('submit-payment-request', 'Payer\\DashboardController::submitPaymentRequest');
            $routes->get('get-contribution-details', 'Payer\\DashboardController::getContributionDetails');
            $routes->get('check-new-activities', 'Payer\\DashboardController::checkNewActivities');
            $routes->post('mark-activity-read/(:num)', 'Payer\\DashboardController::markActivityAsRead/$1');
            $routes->get('get-all-activities', 'Payer\\DashboardController::getAllActivities');
            $routes->get('test-activity', 'Payer\\DashboardController::testActivity');
            $routes->get('refund-requests', 'Payer\\DashboardController::refundRequests');
            // Provide active refund methods for the payer modal dropdown
            $routes->get('refund-methods', 'Payer\\DashboardController::getActiveRefundMethods');
            $routes->post('submit-refund-request', 'Payer\\DashboardController::submitRefundRequest');
            // Provide active payment methods for the payer
            $routes->get('payment-methods', 'Payer\\DashboardController::getActivePaymentMethods');
            $routes->get('help', 'Payer\\HelpController::index');
            $routes->get('logout', 'Payer\\LoginController::logout');
        });