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
        <div class="card <?= esc($bgColor) ?> text-white shadow-sm rounded-3 hover-scale h-100" 
             style="transition: transform 0.2s, box-shadow 0.2s; min-height: 120px; cursor: pointer;" 
             data-bs-toggle="modal" 
             data-bs-target="#<?= esc($modalTarget) ?>">
            <div class="card-body d-flex align-items-center gap-3 h-100">
                <div class="icon-circle d-flex align-items-center justify-content-center">
                    <i class="<?= esc($icon ?? 'fas fa-cog') ?> fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-semibold"><?= esc($title ?? 'Action') ?></h6>
                    <small class="text-white-75"><?= esc($subtitle ?? '') ?></small>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Regular Link -->
        <a href="<?= esc($link ?? '#') ?>" 
           class="card <?= esc($bgColor) ?> text-white shadow-sm rounded-3 hover-scale h-100 text-decoration-none" 
           style="transition: transform 0.2s, box-shadow 0.2s; min-height: 120px;">
            <div class="card-body d-flex align-items-center gap-3 h-100">
                <div class="icon-circle d-flex align-items-center justify-content-center">
                    <i class="<?= esc($icon ?? 'fas fa-cog') ?> fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-semibold"><?= esc($title ?? 'Action') ?></h6>
                    <small class="text-white-75"><?= esc($subtitle ?? '') ?></small>
                </div>
            </div>
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

.text-white-75 {
    opacity: 0.75;
}
</style>
