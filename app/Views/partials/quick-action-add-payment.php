<?php
// Reusable Add Payment Quick Action Component
// This component automatically includes the modal and handles all the functionality

// CRITICAL FIX: CodeIgniter's view() function extracts variables into the local scope
// This causes variables to persist between multiple view() calls
// We MUST explicitly save what was passed and clear everything to prevent leakage

// Save what was passed (if anything)
$passedTitle = $title ?? null;
$passedSubtitle = $subtitle ?? null;
$passedIcon = $icon ?? null;
$passedBgColor = $bgColor ?? null;
$passedColClass = $colClass ?? null;
$passedModalTitle = $modalTitle ?? null;
$passedAction = $action ?? null;
$passedContributions = $contributions ?? null;
$passedTone = $tone ?? null;

// NOW clear everything to prevent leakage
unset($title, $subtitle, $icon, $bgColor, $colClass, $modalTitle, $action, $contributions, $tone);

// Restore only what was explicitly passed, with defaults
$title = $passedTitle ?? 'New Payment';
$subtitle = $passedSubtitle ?? 'Record payment';
$icon = $passedIcon ?? 'fas fa-plus';
$bgColor = $passedBgColor ?? 'bg-primary';
$colClass = $passedColClass ?? 'col-lg-4 col-md-6';
$modalTitle = $passedModalTitle ?? 'Add Payment';
$action = $passedAction ?? base_url('/payments/save'); // CRITICAL: Always default to payments/save
$contributions = $passedContributions ?? [];
$toneMap = [
    'bg-primary' => 'primary',
    'bg-success' => 'success',
    'bg-info' => 'cyan',
    'bg-secondary' => 'slate',
    'bg-danger' => 'rose',
    'bg-warning' => 'amber',
];
$tone = $passedTone ?? ($toneMap[$bgColor] ?? 'primary');
?>

<!-- Add Payment Quick Action Button -->
<div class="<?= esc($colClass) ?>">
    <button type="button"
            class="ui-admin-action-card ui-admin-action-<?= esc($tone) ?> h-100"
            data-bs-toggle="modal"
            data-bs-target="#addPaymentModal">
        <span class="ui-admin-action-icon"><i class="<?= esc($icon) ?>"></i></span>
        <span class="ui-admin-action-copy">
            <span class="ui-admin-action-title"><?= esc($title) ?></span>
            <span class="ui-admin-action-subtitle"><?= esc($subtitle) ?></span>
        </span>
    </button>
</div>

<!-- Include the payment modal (only once per page) -->
<?php if (!isset($GLOBALS['add_payment_modal_included'])): ?>
    <?php $GLOBALS['add_payment_modal_included'] = true; ?>
    
    <?= view('partials/modal-add-payment', [
        'title' => $modalTitle,
        'action' => $action,
        'contributions' => $contributions,
    ]) ?>
<?php endif; ?>
