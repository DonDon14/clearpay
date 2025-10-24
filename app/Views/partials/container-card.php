<?php
// container-card.php - Flexible container that can hold cards or custom content
$cardClass = $cardClass ?? 'shadow-sm';
$bodyClass = $bodyClass ?? 'd-flex flex-wrap gap-2';
?>

<div class="card <?= $cardClass ?> mb-3">
    <?php if (!empty($title)) : ?>
        <div class="card-header">
            <h5 class="card-title mb-0"><?= $title ?></h5>
            <?php if (!empty($subtitle)) : ?>
                <small class="text-muted d-block"><?= $subtitle ?></small>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="card-body <?= $bodyClass ?>">
        <?php if (!empty($content)) : ?>
            <!-- Custom content -->
            <?= $content ?>
        <?php elseif (!empty($cards)) : ?>
            <!-- Default card rendering -->
            <?php foreach ($cards as $card) : ?>
                <?= view('partials/card', $card) ?>
            <?php endforeach; ?>
        <?php elseif (!empty($items)) : ?>
            <!-- Flexible items with custom view -->
            <?php foreach ($items as $item) : ?>
                <?php if (isset($item['view'])) : ?>
                    <?= view($item['view'], $item) ?>
                <?php else : ?>
                    <?= view('partials/card', $item) ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
