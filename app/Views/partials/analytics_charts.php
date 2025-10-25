<?= view('partials/container-card', [
    'title' => 'Revenue Trends',
    'content' => $this->include('partials/analytics_charts_content', ['charts' => $charts], true)
]) ?>