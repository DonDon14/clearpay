<?php
// Help & Support section partial view
// This partial is used in both admin and payer sidebars
// Check if we're in payer context by looking at the current route or session
$currentUri = uri_string();
$isPayerContext = (strpos($currentUri, 'payer/') === 0 || strpos($currentUri, '/payer/') !== false) || session()->get('payer_id');
$helpUrl = $isPayerContext ? base_url('payer/help') : base_url('help');
$pageTitleCheck = ($pageTitle ?? '') === 'Help & Support';
?>
<a href="<?= $helpUrl ?>" class="sidebar-item <?= $pageTitleCheck ? 'active' : '' ?>" data-tooltip="Help & Support">
    <i class="fas fa-question-circle"></i>
    <span class="help-text">Help & Support</span>
</a>