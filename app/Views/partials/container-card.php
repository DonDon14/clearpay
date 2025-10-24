<?php
// container-card.php
$cardClass = $cardClass ?? 'shadow-sm';
?>

<div class="card <?= $cardClass ?> mb-3">
    <?php if (!empty($title)) : ?>
        <div class="card-header">
            <h5 class="card-title mb-0"><?= $title ?></h5>
        </div>
    <?php endif; ?>
    <div class="card-body d-flex flex-wrap gap-2">
        <?php if (!empty($cards)) : ?>
            <?php foreach ($cards as $card) : ?>
                <?= view('partials/card', $card) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
