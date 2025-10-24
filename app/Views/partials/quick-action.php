<?php
// Variables to pass: $icon, $title, $subtitle, $bgColor (optional)
$bgColor = $bgColor ?? 'bg-primary'; // default background color
?>

<div class="col-lg-3 col-md-6 mb-2">
    <a href="<?= $link ?? '#' ?>" class="d-block text-decoration-none">
        <div class="card <?= $bgColor ?> text-white shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fs-3">
                    <i class="<?= $icon ?>"></i>
                </div>
                <div>
                    <h6 class="mb-1"><?= $title ?></h6>
                    <small><?= $subtitle ?></small>
                </div>
            </div>
        </div>
    </a>
</div>
