<?php
// Variables: $icon, $title, $subtitle, $bgColor (optional), $colClass (optional), $link (optional), $modalTarget (optional)
$bgColor = $bgColor ?? 'bg-primary';
$colClass = $colClass ?? 'col-lg-3 col-md-6';

// Detect modal trigger
$isModal = isset($modalTarget) && !empty($modalTarget);
?>

<!-- partials/quick-action.php -->
<div class="<?= esc($colClass ?? 'col-6') ?>">
    <?php if (!empty($modalTarget)): ?>
        <button 
            class="btn text-white w-100 py-3 <?= esc($bgColor ?? 'bg-primary') ?> shadow-sm d-flex flex-column align-items-center justify-content-center"
            data-bs-toggle="modal"
            data-bs-target="<?= esc($modalTarget) ?>"
        >
            <i class="<?= esc($icon ?? 'fas fa-cog') ?> mb-2 fs-4"></i>
            <strong><?= esc($title ?? 'Action') ?></strong>
            <small><?= esc($subtitle ?? '') ?></small>
        </button>
    <?php else: ?>
        <a href="<?= esc($link ?? '#') ?>" 
            class="btn text-white w-100 py-3 <?= esc($bgColor ?? 'bg-secondary') ?> shadow-sm d-flex flex-column align-items-center justify-content-center">
            <i class="<?= esc($icon ?? 'fas fa-cog') ?> mb-2 fs-4"></i>
            <strong><?= esc($title ?? 'Action') ?></strong>
            <small><?= esc($subtitle ?? '') ?></small>
        </a>
    <?php endif; ?>
</div>


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
</style>
