
<?php
// Safety checks for variables
$payments = $payments ?? ['recent_payments' => []];
$contributions = $contributions ?? ['top_profitable' => []];
?>

<?= view('partials/container-card', [
    'title' => 'Recent Activity',
    'content' => $this->include('partials/analytics_summary_content', ['payments' => $payments, 'contributions' => $contributions], true)
]) ?>