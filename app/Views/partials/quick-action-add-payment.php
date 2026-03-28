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

// NOW clear everything to prevent leakage
unset($title, $subtitle, $icon, $bgColor, $colClass, $modalTitle, $action, $contributions);

// Restore only what was explicitly passed, with defaults
$title = $passedTitle ?? 'New Payment';
$subtitle = $passedSubtitle ?? 'Record payment';
$icon = $passedIcon ?? 'fas fa-plus';
$bgColor = $passedBgColor ?? 'bg-primary';
$colClass = $passedColClass ?? 'col-lg-4 col-md-6';
$modalTitle = $passedModalTitle ?? 'Add Payment';
$action = $passedAction ?? base_url('/payments/save'); // CRITICAL: Always default to payments/save
$contributions = $passedContributions ?? [];
?>

<!-- Add Payment Quick Action Button -->
<div class="<?= esc($colClass) ?>">
    <div class="card <?= esc($bgColor) ?> text-white shadow-sm rounded-3 hover-scale h-100 ui-quick-action-card" 
         data-bs-toggle="modal" 
         data-bs-target="#addPaymentModal">
        <div class="card-body d-flex align-items-center gap-3 h-100">
            <div class="icon-circle d-flex align-items-center justify-content-center">
                <i class="<?= esc($icon) ?> fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <p class="ui-quick-action-title mb-1"><?= esc($title) ?></p>
                <small class="text-white-75 ui-quick-action-subtitle"><?= esc($subtitle) ?></small>
            </div>
        </div>
    </div>
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
