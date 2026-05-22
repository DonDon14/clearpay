<?php
$controlId = $controlId ?? 'listControls';
$searchId = $searchId ?? $controlId . 'Search';
$resultId = $resultId ?? $controlId . 'Results';
$chipsId = $chipsId ?? $controlId . 'Chips';
$clearId = $clearId ?? $controlId . 'Clear';
$searchLabel = $searchLabel ?? 'Search';
$placeholder = $placeholder ?? 'Search...';
$filters = $filters ?? [];
$sort = $sort ?? null;
?>

<div class="ui-filter-panel ui-filter-panel-inline" id="<?= esc($controlId) ?>">
    <div class="ui-filter-toolbar">
        <div class="ui-filter-field ui-filter-search">
            <label for="<?= esc($searchId) ?>" class="ui-filter-label"><?= esc($searchLabel) ?></label>
            <div class="ui-filter-input-shell">
                <i class="fas fa-search"></i>
                <input type="search" id="<?= esc($searchId) ?>" class="form-control" placeholder="<?= esc($placeholder) ?>" autocomplete="off">
            </div>
        </div>

        <?php foreach ($filters as $filter): ?>
            <div class="ui-filter-field">
                <label for="<?= esc($filter['id']) ?>" class="ui-filter-label"><?= esc($filter['label']) ?></label>
                <select id="<?= esc($filter['id']) ?>" class="form-select">
                    <?php foreach (($filter['options'] ?? []) as $value => $label): ?>
                        <option value="<?= esc((string) $value) ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($sort)): ?>
            <div class="ui-filter-field">
                <label for="<?= esc($sort['id']) ?>" class="ui-filter-label"><?= esc($sort['label'] ?? 'Sort') ?></label>
                <select id="<?= esc($sort['id']) ?>" class="form-select">
                    <?php foreach (($sort['options'] ?? []) as $value => $label): ?>
                        <option value="<?= esc((string) $value) ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <button type="button" class="btn btn-outline-secondary ui-clear-filters-btn" id="<?= esc($clearId) ?>">
            <i class="fas fa-times me-1"></i>Clear
        </button>
    </div>
    <div class="ui-filter-summary">
        <span class="ui-results-count" id="<?= esc($resultId) ?>"></span>
        <div class="ui-filter-chips" id="<?= esc($chipsId) ?>" aria-live="polite"></div>
    </div>
</div>
