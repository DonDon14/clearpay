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
$passedTone = $tone ?? null;

// NOW clear everything to prevent leakage
unset($modalTarget, $link, $icon, $title, $subtitle, $bgColor, $colClass, $tone);

// Restore only what was explicitly passed
$modalTarget = $passedModalTarget;
$link = $passedLink;
$icon = $passedIcon;
$title = $passedTitle;
$subtitle = $passedSubtitle;
$bgColor = $passedBgColor;
$colClass = $passedColClass;
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

<!-- partials/quick-action.php -->
<div class="<?= esc($colClass) ?>">
    <?php if (isset($modalTarget) && !empty($modalTarget)): ?>
        <!-- Modal Trigger Button -->
        <button type="button"
                class="ui-admin-action-card ui-admin-action-<?= esc($tone) ?> h-100"
                data-bs-toggle="modal"
                data-bs-target="#<?= esc($modalTarget) ?>">
            <span class="ui-admin-action-icon"><i class="<?= esc($icon ?? 'fas fa-cog') ?>"></i></span>
            <span class="ui-admin-action-copy">
                <span class="ui-admin-action-title"><?= esc($title ?? 'Action') ?></span>
                <span class="ui-admin-action-subtitle"><?= esc($subtitle ?? '') ?></span>
            </span>
        </button>
    <?php else: ?>
        <!-- Regular Link -->
        <a href="<?= esc($link ?? '#') ?>"
           class="ui-admin-action-card ui-admin-action-<?= esc($tone) ?> h-100">
            <span class="ui-admin-action-icon"><i class="<?= esc($icon ?? 'fas fa-cog') ?>"></i></span>
            <span class="ui-admin-action-copy">
                <span class="ui-admin-action-title"><?= esc($title ?? 'Action') ?></span>
                <span class="ui-admin-action-subtitle"><?= esc($subtitle ?? '') ?></span>
            </span>
        </a>
    <?php endif; ?>
</div>
