<?php
// Reusable Add Payment Quick Action Component
// This component automatically includes the modal and handles all the functionality

// Default values
$title = $title ?? 'New Payment';
$subtitle = $subtitle ?? 'Record payment';
$icon = $icon ?? 'fas fa-plus';
$bgColor = $bgColor ?? 'bg-primary';
$colClass = $colClass ?? 'col-lg-4 col-md-6';
$modalTitle = $modalTitle ?? 'Add Payment';
$action = $action ?? base_url('/payments/save');
$contributions = $contributions ?? [];
?>

<!-- Add Payment Quick Action Button -->
<div class="<?= esc($colClass) ?>">
    <div class="card <?= esc($bgColor) ?> text-white shadow-sm rounded-3 hover-scale h-100" 
         style="transition: transform 0.2s, box-shadow 0.2s; min-height: 120px; cursor: pointer;" 
         data-bs-toggle="modal" 
         data-bs-target="#addPaymentModal">
        <div class="card-body d-flex align-items-center gap-3 h-100">
            <div class="icon-circle d-flex align-items-center justify-content-center">
                <i class="<?= esc($icon) ?> fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-semibold"><?= esc($title) ?></h6>
                <small class="text-white-75"><?= esc($subtitle) ?></small>
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
    
    <script src="<?= base_url('js/payment.js') ?>"></script>
<?php endif; ?>

<style>
.icon-circle {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    flex-shrink: 0;
    font-size: 1.25rem;
}

.hover-scale:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.text-white-75 {
    opacity: 0.75;
}
</style>
