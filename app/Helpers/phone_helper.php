<?php

if (!function_exists('validate_phone_number')) {
    /**
     * Validate phone number - must be exactly 11 digits and numbers only
     *
     * @param string $phoneNumber The phone number to validate
     * @return bool True if valid, false otherwise
     */
    function validate_phone_number($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return true; // Empty is allowed (optional field)
        }
        
        // Remove any whitespace
        $phoneNumber = trim($phoneNumber);
        
        // Must be exactly 11 digits and contain only numbers
        return preg_match('/^[0-9]{11}$/', $phoneNumber);
    }
}

if (!function_exists('format_phone_number')) {
    /**
     * Format phone number for display (e.g., 09123456789 -> 0912 345 6789)
     *
     * @param string $phoneNumber The phone number to format
     * @return string Formatted phone number or original if invalid
     */
    function format_phone_number($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return '';
        }
        
        // Remove any whitespace
        $phoneNumber = preg_replace('/\s+/', '', $phoneNumber);
        
        // If valid 11-digit number, format it
        if (preg_match('/^([0-9]{4})([0-9]{3})([0-9]{4})$/', $phoneNumber, $matches)) {
            return $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
        }
        
        return $phoneNumber;
    }
}

if (!function_exists('sanitize_phone_number')) {
    /**
     * Sanitize phone number - remove all non-numeric characters
     *
     * @param string $phoneNumber The phone number to sanitize
     * @return string Sanitized phone number (numbers only)
     */
    function sanitize_phone_number($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return '';
        }
        
        // Remove all non-numeric characters
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    }
}

