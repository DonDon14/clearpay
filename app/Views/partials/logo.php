<?php
/**
 * Logo Partial
 * Reusable logo component for ClearPay
 * 
 * Usage: 
 *   echo view('partials/logo', ['options' => ['size' => 'medium', 'spacing' => '0.5rem']]);
 * 
 * @param array $options Optional parameters:
 *   - 'size' => 'small'|'medium'|'large' (default: 'medium')
 *   - 'showText' => true|false (default: true)
 *   - 'textSize' => CSS size (default: '2rem')
 *   - 'spacing' => CSS spacing (default: '0.5rem')
 */

// Get options with defaults
$options = $options ?? [];
$size = $options['size'] ?? 'medium';
$showText = $options['showText'] ?? true;
$textSize = $options['textSize'] ?? '2rem';
$spacing = $options['spacing'] ?? '0.5rem';

// Define sizes
$logoSizes = [
    'small' => '40px',
    'medium' => '120px',
    'large' => '160px'
];

$logoSize = $logoSizes[$size] ?? $logoSizes['medium'];
?>
<div class="branding-logo-container" style="text-align: center;">
    <img 
        src="<?= base_url('uploads/logo.png') ?>" 
        alt="ClearPay Logo" 
        class="branding-logo"
        style="width: <?= $logoSize ?>; height: <?= $logoSize ?>; object-fit: contain; display: block; margin: 0 auto;"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
    >
    <h1 style="display: none; margin: 0; font-size: <?= $logoSize ?>;">ClearPay</h1>
</div>
<?php if ($showText): ?>
<div style="text-align: center; margin-top: 0.25rem;">
    <h1 style="margin: 0; font-size: <?= $textSize ?>; font-weight: 700; color: #3B82F6;">ClearPay</h1>
</div>
<?php endif; ?>

