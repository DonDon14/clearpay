<?php
// CRITICAL FIX: CodeIgniter's view() function extracts variables into the local scope
// This causes variables to persist between multiple view() calls
// We MUST explicitly set these to null if they weren't passed

// Save what was passed (if anything)
$passedModalTarget = $modalTarget ?? null;
$passedLink = $link ?? null;
$passedIcon = $icon ?? 'fas fa-cog';
$passedTitle = $title ?? 'Action';
$passedSubtitle = $subtitle ?? '';
$passedBgColor = $bgColor ?? 'bg-primary';
$passedColClass = $colClass ?? 'col-6';

// NOW clear everything to prevent leakage
unset($modalTarget, $link, $icon, $title, $subtitle, $bgColor, $colClass);

// Restore only what was explicitly passed
$modalTarget = $passedModalTarget;
$link = $passedLink;
$icon = $passedIcon;
$title = $passedTitle;
$subtitle = $passedSubtitle;
$bgColor = $passedBgColor;
$colClass = $passedColClass;
?>

<!-- partials/quick-action.php -->
<!-- DEBUG: title=<?= htmlspecialchars($title) ?> modalTarget=<?= var_export($modalTarget, true) ?> link=<?= var_export($link, true) ?> -->
<div class="<?= esc($colClass) ?>">
    <?php if (isset($modalTarget) && !empty($modalTarget)): ?>
        <!-- Modal Trigger Button -->
        <button 
            type="button"
            class="btn text-white w-100 py-3 <?= esc($bgColor) ?> shadow-sm d-flex flex-column align-items-center justify-content-center"
            data-bs-toggle="modal"
            data-bs-target="#<?= esc($modalTarget) ?>"
        >
            <i class="<?= esc($icon ?? 'fas fa-cog') ?> mb-2 fs-4"></i>
            <strong><?= esc($title ?? 'Action') ?></strong>
            <small class="text-white-75"><?= esc($subtitle ?? '') ?></small>
        </button>
    <?php else: ?>
        <!-- Regular Link -->
        <a href="<?= esc($link ?? '#') ?>" 
           class="btn text-white w-100 py-3 <?= esc($bgColor) ?> shadow-sm d-flex flex-column align-items-center justify-content-center text-decoration-none">
            <i class="<?= esc($icon ?? 'fas fa-cog') ?> mb-2 fs-4"></i>
            <strong><?= esc($title ?? 'Action') ?></strong>
            <small class="text-white-75"><?= esc($subtitle ?? '') ?></small>
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
