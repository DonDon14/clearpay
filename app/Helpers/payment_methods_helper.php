<?php
/**
 * Payment Methods Management Helper
 * 
 * This helper provides easy functions to include the payment methods management modal
 * in any page or view.
 */

if (!function_exists('payment_methods_modal')) {
    /**
     * Include the payment methods management modal
     * 
     * @param array $paymentMethods Array of payment methods data
     * @param array $options Additional options
     * @return string HTML output
     */
    function payment_methods_modal($paymentMethods = [], $options = [])
    {
        // Default options
        $defaultOptions = [
            'loadJs' => true,
            'loadCss' => true,
            'baseUrl' => base_url()
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        $output = '';
        
        // Include the modal partial
        $output .= view('partials/modals/payment_methods_management', [
            'paymentMethods' => $paymentMethods
        ]);
        
        // Include JavaScript data
        $output .= '<script>';
        $output .= 'window.paymentMethodsData = ' . json_encode($paymentMethods) . ';';
        $output .= 'window.baseUrl = "' . $options['baseUrl'] . '";';
        $output .= '</script>';
        
        // Include JavaScript file if requested
        if ($options['loadJs']) {
            $output .= '<script src="' . $options['baseUrl'] . 'js/modals/payment_methods_management.js"></script>';
        }
        
        return $output;
    }
}

if (!function_exists('payment_methods_modal_button')) {
    /**
     * Generate a button to open the payment methods management modal
     * 
     * @param string $text Button text
     * @param array $attributes Additional button attributes
     * @return string HTML button
     */
    function payment_methods_modal_button($text = 'Manage Payment Methods', $attributes = [])
    {
        $defaultAttributes = [
            'class' => 'btn btn-primary',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#paymentMethodsModal'
        ];
        
        $attributes = array_merge($defaultAttributes, $attributes);
        
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . $key . '="' . esc($value) . '"';
        }
        
        return '<button type="button"' . $attrString . '>' . esc($text) . '</button>';
    }
}

if (!function_exists('payment_methods_modal_trigger')) {
    /**
     * Generate a complete payment methods management section with button and modal
     * 
     * @param array $paymentMethods Array of payment methods data
     * @param string $buttonText Button text
     * @param array $options Additional options
     * @return string HTML output
     */
    function payment_methods_modal_trigger($paymentMethods = [], $buttonText = 'Manage Payment Methods', $options = [])
    {
        $output = '';
        
        // Add the button
        $output .= payment_methods_modal_button($buttonText, $options['buttonAttributes'] ?? []);
        
        // Add the modal
        $output .= payment_methods_modal($paymentMethods, $options);
        
        return $output;
    }
}
