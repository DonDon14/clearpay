<?php
// Usage variables: $icon, $iconColor, $title, $text
?>

<div class="card border-0 shadow-sm h-100">
    <div class="card-body text-center">
        <div class="<?= $iconColor ?> mb-2">
            <i class="<?= $icon ?> fa-2x"></i>
        </div>
        <h5 class="card-title"><?= $title ?></h5>
        <p class="card-text text-muted"><?= $text ?></p>
    </div>
</div>
