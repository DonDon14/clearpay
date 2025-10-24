<?php
// Usage variables: $icon, $iconColor, $title, $text
?>

<div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
            <div class="<?= $iconColor ?> mb-2">
                <i class="<?= $icon ?> fa-2x"></i>
            </div>
            <h5 class="card-title"><?= $title ?></h5>
            <p class="card-text text-muted"><?= $text ?></p>
        </div>
    </div>
</div>
