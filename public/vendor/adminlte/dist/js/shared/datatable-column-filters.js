window.DataTableColumnFilters = (function () {
    function escapeRegex(value) {
        return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function isNumericValue(value) {
        return /^-?\d+(?:[.,]\d+)?$/.test(String(value).trim());
    }

    function bind(table, selector, exactColumns) {
        const exact = new Set(exactColumns || []);

        document.querySelectorAll(selector).forEach(function (cell, index) {
            const input = cell.querySelector('input');
            const select = cell.querySelector('select');

            if (select) {
                const values = table.column(index).data().unique().sort();
                values.each(function (value) {
                    if (value !== null && value !== '') {
                        const option = document.createElement('option');
                        option.value = value;
                        option.textContent = value;
                        select.appendChild(option);
                    }
                });

                select.addEventListener('change', function () {
                    const value = this.value;
                    table.column(index).search(value ? '^' + escapeRegex(value) + '$' : '', true, false).draw();
                });
            }

            if (input) {
                const handler = function () {
                    const value = this.value.trim();
                    if (value === '') {
                        table.column(index).search('').draw();
                        return;
                    }

                    if (exact.has(index) && isNumericValue(value)) {
                        table.column(index).search('^' + escapeRegex(value) + '$', true, false).draw();
                        return;
                    }

                    table.column(index).search(value).draw();
                };

                input.addEventListener('keyup', handler);
                input.addEventListener('change', handler);
                input.addEventListener('clear', handler);
            }
        });
    }

    return { bind: bind };
})();
