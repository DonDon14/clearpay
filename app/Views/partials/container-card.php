<?php
// container-card.php - Flexible container that can hold cards or custom content
$cardClass = $cardClass ?? 'shadow-sm';
$bodyClass = $bodyClass ?? 'd-flex flex-wrap gap-2';
$hasContent = isset($content) && !empty($content);
$hasCards = isset($cards) && !empty($cards) && is_array($cards);
$hasItems = isset($items) && !empty($items) && is_array($items);
?>

<div class="card <?= $cardClass ?> mb-3">
    <?php if (!empty($title)) : ?>
        <div class="card-header <?= !empty($headerAction) ? 'd-flex justify-content-between align-items-center' : '' ?>">
            <div>
                <h5 class="card-title mb-0"><?= $title ?></h5>
                <?php if (!empty($subtitle)) : ?>
                    <small class="text-muted d-block"><?= $subtitle ?></small>
                <?php endif; ?>
            </div>
            <?php if (!empty($headerAction)) : ?>
                <?= $headerAction ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="card-body <?= $bodyClass ?>">
        <?php if ($hasContent) : ?>
            <!-- Custom content -->
            <?= $content ?>
        <?php elseif ($hasItems) : ?>
            <!-- Flexible items with custom view -->
            <?php foreach ($items as $item) : ?>
                <?php if (isset($item['view']) && !empty($item['view'])) : ?>
                    <?= view($item['view'], $item) ?>
                <?php else : ?>
                    <?= view('partials/card', $item) ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php elseif ($hasCards) : ?>
            <!-- Default card rendering -->
            <?php foreach ($cards as $card) : ?>
                <?= view('partials/card', $card) ?>
            <?php endforeach; ?>
        <?php else : ?>
            <!-- Fallback: No content provided -->
            <p class="text-muted">No content available</p>
        <?php endif; ?>
    </div>
</div>
