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
            $html .= '</option>';
        }
        
        $html .= '</select>';
        
        return $html;
    }
}

if (!function_exists('payment_method_dropdown_with_icons')) {
    /**
     * Generate a custom dropdown for payment methods with icons
     *
     * @param string $name The name attribute for the select element
     * @param mixed $selectedId The ID of the selected payment method
     * @param array $attributes Additional attributes for the select element
     * @return string HTML custom dropdown element
     */
    function payment_method_dropdown_with_icons($name = 'payment_method_id', $selectedId = null, $attributes = [])
    {
        // Load the PaymentMethodModel
        $paymentMethodModel = new \App\Models\PaymentMethodModel();
        
        // Get active payment methods
        $paymentMethods = $paymentMethodModel->getActiveMethods();
        
        // Set default attributes
        $defaultAttributes = [
            'class' => 'form-control',
            'id' => $name,
        ];
        
        // Merge with provided attributes
        $attributes = array_merge($defaultAttributes, $attributes);
        
        // Build attributes string
        $attributesString = '';
        foreach ($attributes as $key => $value) {
            $attributesString .= ' ' . $key . '="' . esc($value) . '"';
        }
        
        // Generate unique ID for the dropdown
        $dropdownId = $attributes['id'] ?? $name;
        $dropdownId .= '_custom';
        
        // Start building the custom dropdown
        $html = '<div class="payment-method-dropdown" id="' . esc($dropdownId) . '_container">';
        
        // Hidden input to store the selected value
        $html .= '<input type="hidden" name="' . esc($name) . '" id="' . esc($dropdownId) . '_input" value="' . esc($selectedId) . '">';
        
        // Custom dropdown button
        $selectedMethod = null;
        if ($selectedId) {
            foreach ($paymentMethods as $method) {
                if ($method['id'] == $selectedId) {
                    $selectedMethod = $method;
                    break;
                }
            }
        }
        
        $html .= '<button type="button" class="btn btn-outline-secondary dropdown-toggle w-100 text-start" id="' . esc($dropdownId) . '_button" aria-expanded="false">';
        
        if ($selectedMethod) {
            if (!empty($selectedMethod['icon']) && file_exists(FCPATH . $selectedMethod['icon'])) {
                $html .= '<img src="' . base_url($selectedMethod['icon']) . '" alt="' . esc($selectedMethod['name']) . '" style="width: 20px; height: 20px; margin-right: 8px; object-fit: cover;">';
            }
            $html .= esc($selectedMethod['name']);
        } else {
            $html .= 'Select Payment Method';
        }
        
        $html .= '</button>';
        
        // Dropdown menu
        $html .= '<ul class="dropdown-menu w-100" aria-labelledby="' . esc($dropdownId) . '_button">';
        
        foreach ($paymentMethods as $method) {
            $html .= '<li>';
            $html .= '<a class="dropdown-item d-flex align-items-center" href="#" data-value="' . esc($method['id']) . '">';
            
            if (!empty($method['icon']) && file_exists(FCPATH . $method['icon'])) {
                $html .= '<img src="' . base_url($method['icon']) . '" alt="' . esc($method['name']) . '" style="width: 20px; height: 20px; margin-right: 8px; object-fit: cover;">';
            } else {
                $html .= '<div style="width: 20px; height: 20px; margin-right: 8px; background: #e9ecef; border-radius: 3px; display: inline-block;"></div>';
            }
            
            $html .= esc($method['name']);
            $html .= '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        // Add CSS for better positioning and animations
        $html .= '<style>
        #' . esc($dropdownId) . '_container {
            position: relative;
            z-index: 1;
        }
        
        #' . esc($dropdownId) . '_container .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            right: 0 !important;
            transform: none !important;
            margin-top: 2px !important;
            z-index: 1060 !important;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.15s ease-in-out, visibility 0.15s ease-in-out;
            background-color: white;
            min-width: 100%;
            max-height: 200px;
            overflow-y: auto;
        }
        
        #' . esc($dropdownId) . '_container .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
        }
        
        #' . esc($dropdownId) . '_container .dropdown-item {
            padding: 0.5rem 1rem;
            transition: background-color 0.15s ease-in-out;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        #' . esc($dropdownId) . '_container .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        #' . esc($dropdownId) . '_container .dropdown-item img {
            border-radius: 3px;
            flex-shrink: 0;
        }
        
        /* Ensure proper stacking within modals */
        .modal .payment-method-dropdown {
            z-index: 1060;
        }
        
        .modal .payment-method-dropdown .dropdown-menu {
            z-index: 1070 !important;
        }
        </style>';
        
        // Add JavaScript to handle dropdown functionality
        $html .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById("' . esc($dropdownId) . '_container");
            const button = document.getElementById("' . esc($dropdownId) . '_button");
            const input = document.getElementById("' . esc($dropdownId) . '_input");
            const dropdownMenu = container.querySelector(".dropdown-menu");
            const dropdownItems = container.querySelectorAll(".dropdown-item");
            
            // Custom dropdown toggle functionality
            button.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle dropdown
                if (dropdownMenu.classList.contains("show")) {
                    dropdownMenu.classList.remove("show");
                    button.setAttribute("aria-expanded", "false");
                } else {
                    dropdownMenu.classList.add("show");
                    button.setAttribute("aria-expanded", "true");
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener("click", function(e) {
                if (!container.contains(e.target)) {
                    dropdownMenu.classList.remove("show");
                    button.setAttribute("aria-expanded", "false");
                }
            });
            
            // Handle item selection
            dropdownItems.forEach(function(item) {
                item.addEventListener("click", function(e) {
                    e.preventDefault();
                    const value = this.getAttribute("data-value");
                    const text = this.textContent.trim();
                    const icon = this.querySelector("img");
                    
                    // Update hidden input
                    input.value = value;
                    
                    // Update button content
                    button.innerHTML = "";
                    if (icon) {
                        const newIcon = icon.cloneNode(true);
                        newIcon.style.marginRight = "8px";
                        button.appendChild(newIcon);
                    }
                    button.appendChild(document.createTextNode(text));
                    
                    // Close dropdown
                    dropdownMenu.classList.remove("show");
                    button.setAttribute("aria-expanded", "false");
                });
            });
        });
        </script>';
        
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
