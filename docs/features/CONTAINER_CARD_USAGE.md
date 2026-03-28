# Container Card Usage Examples

## Enhanced Flexible Container Card

The `container-card` partial now supports multiple content types:

### 1. **Default Cards** (existing functionality)
```php
<?= view('partials/container-card', [
    'title' => 'Stats',
    'cards' => [
        ['icon' => 'fas fa-users', 'title' => 'Users', 'text' => '123']
    ]
]) ?>
```

### 2. **Custom Content** (new)
```php
<?= view('partials/container-card', [
    'title' => 'Custom Content',
    'bodyClass' => 'p-3',
    'content' => '<p>Any HTML content here</p>'
]) ?>
```

### 3. **Mixed Items with Different Views** (new)
```php
<?= view('partials/container-card', [
    'title' => 'Quick Actions',
    'bodyClass' => 'p-2',
    'items' => [
        ['view' => 'partials/quick-action', 'icon' => 'fas fa-plus', 'title' => 'Add'],
        ['view' => 'partials/button', 'text' => 'Button'],
        ['view' => 'partials/card', 'icon' => 'fas fa-chart', 'title' => 'Chart']
    ]
]) ?>
```

### 4. **Table Content Example**
```php
<?= view('partials/container-card', [
    'title' => 'Recent Transactions',
    'bodyClass' => 'p-0',
    'content' => '
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Name</th><th>Amount</th></tr></thead>
                <tbody><tr><td>John</td><td>$100</td></tr></tbody>
            </table>
        </div>
    '
]) ?>
```

### 5. **Chart/Graph Container**
```php
<?= view('partials/container-card', [
    'title' => 'Analytics Chart',
    'bodyClass' => 'p-3 text-center',
    'content' => '<canvas id="myChart" width="400" height="200"></canvas>'
]) ?>
```

## Parameters

- **title**: Header title
- **subtitle**: Optional subtitle
- **cardClass**: CSS classes for the card wrapper (default: 'shadow-sm')
- **bodyClass**: CSS classes for the card body (default: 'd-flex flex-wrap gap-2')
- **content**: Raw HTML content
- **cards**: Array of card data (original functionality)
- **items**: Array of items with custom views