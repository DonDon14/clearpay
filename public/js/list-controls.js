(function() {
  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
  }

  function selectedLabel(select) {
    if (!select || select.selectedIndex < 0) return '';
    return select.options[select.selectedIndex].text.trim();
  }

  function resultText(visible, total, label) {
    const singularLabel = label.endsWith('s') ? label.slice(0, -1) : label;
    const format = count => `${count} ${count === 1 ? singularLabel : label}`;
    if (total === 0) return `No ${label} yet`;
    if (visible === 0) return 'No matches';
    if (visible === total) return format(total);
    return `${visible} of ${format(total)} shown`;
  }

  function getValue(row, field) {
    const attr = field.attribute || `data-${field.key}`;
    return String(row.getAttribute(attr) || '').toLowerCase();
  }

  function sortRows(rows, config) {
    if (!config.sort || !config.sort.element) return rows;

    const option = (config.sort.options || {})[config.sort.element.value];
    if (!option) return rows;

    const type = option.type || 'text';
    const direction = option.direction === 'desc' ? -1 : 1;
    const attr = option.attribute;

    return rows.sort((a, b) => {
      const aValue = String(a.getAttribute(attr) || '');
      const bValue = String(b.getAttribute(attr) || '');

      if (type === 'number') {
        return ((parseFloat(aValue) || 0) - (parseFloat(bValue) || 0)) * direction;
      }

      return aValue.localeCompare(bValue) * direction;
    });
  }

  window.createListControls = function(config) {
    const search = document.getElementById(config.searchId);
    const result = document.getElementById(config.resultId);
    const chips = document.getElementById(config.chipsId);
    const clear = document.getElementById(config.clearId);
    const rows = Array.from(document.querySelectorAll(config.itemSelector));
    const empty = config.emptySelector ? document.querySelector(config.emptySelector) : null;
    const label = config.label || 'items';
    let timer = null;

    const filters = (config.filters || []).map(filter => ({
      ...filter,
      element: document.getElementById(filter.id)
    }));

    const sort = config.sort
      ? { ...config.sort, element: document.getElementById(config.sort.id) }
      : null;

    function apply() {
      const term = search ? search.value.trim().toLowerCase() : '';
      let visible = 0;

      rows.forEach(row => {
        const searchable = String(row.getAttribute(config.searchAttribute || 'data-search') || '').toLowerCase();
        const matchesSearch = term === '' || searchable.includes(term);
        const matchesFilters = filters.every(filter => {
          const value = filter.element ? filter.element.value.toLowerCase() : '';
          return value === '' || getValue(row, filter) === value;
        });
        const isVisible = matchesSearch && matchesFilters;
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visible++;
      });

      if (sort && sort.element && config.containerSelector) {
        const container = document.querySelector(config.containerSelector);
        if (container) {
          sortRows(rows, { sort }).forEach(row => container.appendChild(row));
          if (empty) container.appendChild(empty);
        }
      }

      if (result) result.textContent = resultText(visible, rows.length, label);
      if (empty) empty.classList.toggle('d-none', visible !== 0 || rows.length === 0);

      if (chips) {
        const active = [];
        if (term) active.push({ key: 'search', label: `Search: ${term}` });
        filters.forEach(filter => {
          if (filter.element && filter.element.value) {
            active.push({ key: filter.key, label: `${filter.label}: ${selectedLabel(filter.element)}` });
          }
        });
        chips.innerHTML = active.map(chip => `
          <button type="button" class="ui-filter-chip" data-filter-key="${escapeHtml(chip.key)}">
            <span>${escapeHtml(chip.label)}</span>
            <i class="fas fa-times"></i>
          </button>
        `).join('');
      }

      if (typeof config.onAfterApply === 'function') {
        config.onAfterApply({ visible, total: rows.length });
      }
    }

    function clearFilter(key) {
      if (key === 'search' && search) search.value = '';
      filters.forEach(filter => {
        if (filter.key === key && filter.element) filter.element.value = '';
      });
      apply();
    }

    if (search) {
      search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(apply, config.debounce || 160);
      });
      search.addEventListener('keyup', event => {
        if (event.key === 'Escape') {
          search.value = '';
          apply();
        }
      });
    }

    filters.forEach(filter => {
      if (filter.element) filter.element.addEventListener('change', apply);
    });

    if (sort && sort.element) sort.element.addEventListener('change', apply);

    if (clear) {
      clear.addEventListener('click', () => {
        if (search) search.value = '';
        filters.forEach(filter => {
          if (filter.element) filter.element.value = '';
        });
        apply();
        if (search) search.focus();
      });
    }

    if (chips) {
      chips.addEventListener('click', event => {
        const chip = event.target.closest('.ui-filter-chip');
        if (chip) clearFilter(chip.getAttribute('data-filter-key'));
      });
    }

    apply();

    return { apply, clearFilter };
  };
})();
