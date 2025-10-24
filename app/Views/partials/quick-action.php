<?php
// Variables: $icon, $title, $subtitle, $bgColor (optional)
$bgColor = $bgColor ?? 'bg-primary'; // default background color
?>

<div class="col-lg-3 col-md-6 mb-3">
    <a href="<?= $link ?? '#' ?>" class="text-decoration-none">
        <div class="card <?= $bgColor ?> text-white shadow-sm rounded-3 hover-scale" style="transition: transform 0.2s, box-shadow 0.2s;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-circle d-flex align-items-center justify-content-center me-2">
                    <i class="<?= $icon ?> fs-4"></i>
                </div>
                <div>
                    <h6 class="mb-1 fw-semibold"><?= $title ?></h6>
                    <small class="text-white-75"><?= $subtitle ?></small>
                </div>
            </div>
        </div>
    </a>
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
</style>
