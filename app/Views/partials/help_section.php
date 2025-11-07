<?php
// Help & Support section partial view
?>
<a href="<?= base_url('help') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Help & Support') ? 'active' : '' ?>" data-tooltip="Help & Support">
    <i class="fas fa-question-circle"></i>
    <span class="help-text">Help & Support</span>
</a>