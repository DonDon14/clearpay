<?php
// Reusable Add Contribution Quick Action Component
// This component automatically includes the modal and handles all the functionality

// Default values
$title = $title ?? 'Add Contribution';
$subtitle = $subtitle ?? 'Add a new contribution';
$icon = $icon ?? 'fas fa-plus-square';
$bgColor = $bgColor ?? 'bg-info';
$colClass = $colClass ?? 'col-lg-4 col-md-4 col-sm-6';
$modalTitle = $modalTitle ?? 'Add Contribution';
$action = $action ?? base_url('/contributions/save');
$toneMap = [
    'bg-primary' => 'primary',
    'bg-success' => 'success',
    'bg-info' => 'cyan',
    'bg-secondary' => 'slate',
    'bg-danger' => 'rose',
    'bg-warning' => 'amber',
];
$tone = $tone ?? ($toneMap[$bgColor] ?? 'primary');
?>

<!-- Add Contribution Quick Action Button -->
<div class="<?= esc($colClass) ?>">
    <button type="button"
            class="ui-admin-action-card ui-admin-action-<?= esc($tone) ?> h-100"
            data-bs-toggle="modal"
            data-bs-target="#contributionModal">
        <span class="ui-admin-action-icon"><i class="<?= esc($icon) ?>"></i></span>
        <span class="ui-admin-action-copy">
            <span class="ui-admin-action-title"><?= esc($title) ?></span>
            <span class="ui-admin-action-subtitle"><?= esc($subtitle) ?></span>
        </span>
    </button>
</div>

<!-- Include the contribution modal (only once per page) -->
<?php if (!isset($GLOBALS['add_contribution_modal_included'])): ?>
    <?php $GLOBALS['add_contribution_modal_included'] = true; ?>
    
    <?= view('partials/modal-contribution', [
        'title' => $modalTitle,
        'action' => $action,
    ]) ?>
    
    <script src="<?= base_url('js/contribution.js') ?>"></script>
<?php endif; ?>
