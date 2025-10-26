<?php
// Reusable Add Contribution Quick Action Component
// This component automatically includes the modal and handles all the functionality

// Default values
$title = $title ?? 'Add Contribution';
$subtitle = $subtitle ?? 'Add new contribution type';
$icon = $icon ?? 'fas fa-plus-square';
$bgColor = $bgColor ?? 'bg-info';
$colClass = $colClass ?? 'col-lg-4 col-md-4 col-sm-6';
$modalTitle = $modalTitle ?? 'Add New Contribution';
$action = $action ?? base_url('/contributions/save');
?>

<!-- Add Contribution Quick Action Button -->
<div class="<?= esc($colClass) ?>">
    <div class="card <?= esc($bgColor) ?> text-white shadow-sm rounded-3 hover-scale h-100" 
         style="transition: transform 0.2s, box-shadow 0.2s; min-height: 120px; cursor: pointer;" 
         data-bs-toggle="modal" 
         data-bs-target="#contributionModal">
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

<!-- Include the contribution modal (only once per page) -->
<?php if (!isset($GLOBALS['add_contribution_modal_included'])): ?>
    <?php $GLOBALS['add_contribution_modal_included'] = true; ?>
    
    <?= view('partials/modal-contribution', [
        'title' => $modalTitle,
        'action' => $action,
    ]) ?>
    
    <script src="<?= base_url('js/contribution.js') ?>"></script>
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
