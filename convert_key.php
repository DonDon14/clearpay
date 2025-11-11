<?php
/**
 * Encryption Key Converter
 * Converts hex format to base64 format for CodeIgniter 4 .env file
 * 
 * Usage: php convert_key.php
 */

// Your hex key from .env file
$hexKey = '825a29c18cfd49d1aa992c8b3f271ea5461d6ce256e49419589e4d6772be4c4e';

// Validate hex string
if (!ctype_xdigit($hexKey)) {
    die("Error: Invalid hex string!\n");
}

// Convert hex to binary
$binaryKey = hex2bin($hexKey);

if ($binaryKey === false) {
    die("Error: Failed to convert hex to binary!\n");
}

// Convert binary to base64
$base64Key = base64_encode($binaryKey);

// Display results
echo "========================================\n";
echo "Encryption Key Converter\n";
echo "========================================\n\n";
echo "Original Hex Key:\n";
echo $hexKey . "\n\n";
echo "Converted Base64 Key:\n";
echo $base64Key . "\n\n";
echo "For your .env file, use:\n";
echo "encryption.key = base64:" . $base64Key . "\n\n";
echo "========================================\n";
echo "Key Length: " . strlen($binaryKey) . " bytes (" . (strlen($binaryKey) * 8) . " bits)\n";
echo "========================================\n";

