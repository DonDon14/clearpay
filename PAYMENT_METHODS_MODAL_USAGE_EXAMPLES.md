<?php
/**
 * Example Usage of Payment Methods Management Modal
 * 
 * This file demonstrates how to use the payment methods management modal
 * in different scenarios throughout the application.
 */

// Example 1: Basic usage in any view
// Just include the modal with payment methods data
echo payment_methods_modal($paymentMethods);

// Example 2: With a trigger button
// This creates both the button and the modal
echo payment_methods_modal_trigger($paymentMethods, 'Manage Payment Methods');

// Example 3: Custom button with modal
echo payment_methods_modal_button('Custom Button Text', [
    'class' => 'btn btn-success btn-lg',
    'id' => 'custom-payment-button'
]);
echo payment_methods_modal($paymentMethods);

// Example 4: In a controller method
// Load payment methods and pass to view
public function somePage()
{
    $paymentMethodModel = new \App\Models\PaymentMethodModel();
    $paymentMethods = $paymentMethodModel->orderBy('name', 'ASC')->findAll();
    
    $data = [
        'title' => 'Some Page',
        'paymentMethods' => $paymentMethods
    ];
    
    return view('some_view', $data);
}

// Example 5: In a view file
// Simply include this line anywhere you want the modal
<?= payment_methods_modal($paymentMethods ?? []) ?>

// Example 6: With custom options
echo payment_methods_modal($paymentMethods, [
    'loadJs' => false,  // Don't load JavaScript (if already loaded)
    'loadCss' => false, // Don't load CSS (if already loaded)
    'baseUrl' => 'https://custom-domain.com'
]);

// Example 7: Multiple modals on same page
// Each modal needs unique IDs, so you can modify the partial or create multiple instances
// For now, only one modal per page is supported due to fixed IDs
