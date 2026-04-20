<?php
$title = $title ?? '';
$subtitle = $subtitle ?? '';
$actionsHtml = $actionsHtml ?? '';
$pillsHtml = $pillsHtml ?? '';
?>
<div class="ui-page-intro payer-page-intro">
    <div>
        <h6><?= esc($title) ?></h6>
        <p><?= esc($subtitle) ?></p>
    </div>
    <?php if ($actionsHtml !== ''): ?>
        <div class="payer-page-tools">
            <?= $actionsHtml ?>
        </div>
    <?php endif; ?>
    <?php if ($pillsHtml !== ''): ?>
        <div class="ui-pill-group">
            <?= $pillsHtml ?>
        </div>
    <?php endif; ?>
</div>
