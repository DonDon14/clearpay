<?php
// Usage variables: $icon, $iconColor, $title, $text
$iconColor = $iconColor ?? 'text-primary';
$icon = $icon ?? 'fas fa-info-circle';
$title = $title ?? 'Card Title';
$text = $text ?? 'Card description text';
$subtitle = $subtitle ?? null;
$cardClass = $cardClass ?? '';
$bodyClass = $bodyClass ?? '';
$textClass = $textClass ?? '';
$titleClass = $titleClass ?? '';
?>

<div class="card border-0 shadow-sm h-100 ui-metric-card <?= esc($cardClass) ?>">
    <div class="card-body <?= esc($bodyClass) ?>">
        <div class="<?= $iconColor ?> ui-metric-icon mb-3">
            <i class="<?= $icon ?> fa-2x"></i>
        </div>
        <p class="ui-metric-label <?= esc($titleClass) ?>"><?= $title ?></p>
        <div class="ui-metric-value <?= esc($textClass) ?>"><?= $text ?></div>
        <?php if (!empty($subtitle)): ?>
            <p class="ui-metric-subtitle mb-0"><?= $subtitle ?></p>
        <?php endif; ?>
    </div>
</div>
