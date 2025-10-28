<?php

if (!function_exists('payment_method_dropdown')) {
    /**
     * Generate a dropdown for payment methods
     *
     * @param string $name The name attribute for the select element
     * @param mixed $selectedId The ID of the selected payment method
     * @param array $attributes Additional attributes for the select element
     * @return string HTML select element
     */
    function payment_method_dropdown($name = 'payment_method_id', $selectedId = null, $attributes = [])
    {
        // Load the PaymentMethodModel
        $paymentMethodModel = new \App\Models\PaymentMethodModel();
        
        // Get active payment methods
        $paymentMethods = $paymentMethodModel->getActiveMethods();
        
        // Set default attributes
        $defaultAttributes = [
            'class' => 'form-select',
            'id' => $name,
        ];
        
        // Merge with provided attributes
        $attributes = array_merge($defaultAttributes, $attributes);
        
        // Build attributes string
        $attributesString = '';
        foreach ($attributes as $key => $value) {
            $attributesString .= ' ' . $key . '="' . esc($value) . '"';
        }
        
        // Start building the select element
        $html = '<select name="' . esc($name) . '"' . $attributesString . '>';
        
        // Add default option
        $html .= '<option value="">Select Payment Method</option>';
        
        // Add payment method options
        foreach ($paymentMethods as $method) {
            $selected = ($selectedId == $method['id']) ? ' selected' : '';
            $html .= '<option value="' . esc($method['id']) . '"' . $selected . '>';
            $html .= esc($method['name']);
            if (!empty($method['description'])) {
                $html .= ' - ' . esc($method['description']);
            }
            $html .= '</option>';
        }
        
        $html .= '</select>';
        
        return $html;
    }
}

if (!function_exists('get_payment_method_name')) {
    /**
     * Get payment method name by ID
     *
     * @param int $id Payment method ID
     * @return string Payment method name or empty string if not found
     */
    function get_payment_method_name($id)
    {
        if (empty($id)) {
            return '';
        }
        
        $paymentMethodModel = new \App\Models\PaymentMethodModel();
        $method = $paymentMethodModel->getMethodById($id);
        
        return $method ? $method['name'] : '';
    }
}

if (!function_exists('get_payment_method_details')) {
    /**
     * Get payment method details by ID
     *
     * @param int $id Payment method ID
     * @return array Payment method details or empty array if not found
     */
    function get_payment_method_details($id)
    {
        if (empty($id)) {
            return [];
        }
        
        $paymentMethodModel = new \App\Models\PaymentMethodModel();
        $method = $paymentMethodModel->getMethodById($id);
        
        return $method ? $method : [];
    }
}

if (!function_exists('get_payment_method_icon')) {
    /**
     * Get payment method icon by ID
     *
     * @param int $id Payment method ID
     * @return string Payment method icon path or default icon
     */
    function get_payment_method_icon($id)
    {
        if (empty($id)) {
            return null;
        }
        
        $paymentMethodModel = new \App\Models\PaymentMethodModel();
        $method = $paymentMethodModel->find($id);
        
        return $method && !empty($method['icon']) ? $method['icon'] : null;
    }
}

if (!function_exists('is_payment_method_active')) {
    /**
     * Check if payment method is active
     *
     * @param int $id Payment method ID
     * @return bool True if active, false otherwise
     */
    function is_payment_method_active($id)
    {
        if (empty($id)) {
            return false;
        }
        
        $paymentMethodModel = new \App\Models\PaymentMethodModel();
        $method = $paymentMethodModel->getMethodById($id);
        
        return $method && $method['status'] === 'active';
    }
}
