function loadTable(config) {
    console.log(config);
    const container = $(config.container);
    const searchInput = $(config.searchInput);
    const exportBtns = $(config.exportButtons);
    let currentPage = 1;

    function fetchData(page = 1, search = '') {
        $.get('/table/fetch', {
            table: config.table,
            columns: config.columns.join(','),
            page: page,
            search: search,
            orderBy: config.orderBy,
            orderType: config.orderType
        }, function (res) {
            renderTable(res.data);
            renderPagination(res.pages, res.page);
        });
    }

    function renderTable(data) {
        let html = '<table class="table table-bordered"><thead><tr>';

        // Render custom headers
        config.headers.forEach(header => {
            html += `<th>${header}</th>`;
        });

        html += '</tr></thead><tbody>';

        // Render rows
        data.forEach(row => {
            html += '<tr>';
            config.columns.forEach(col => {
                html += `<td>${row[col]}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table>';

        $(config.container).html(html);
    }

    function renderPagination(totalPages, current) {
        let html = '<div class="pagination">';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="btn btn-sm page-btn" data-page="${i}">${i}</button>`;
        }
        html += '</div>';
        container.append(html);
    }

    container.on('click', '.page-btn', function () {
        currentPage = $(this).data('page');
        fetchData(currentPage, searchInput.val());
    });

    searchInput.on('keyup', function () {
        fetchData(1, $(this).val());
    });

    exportBtns.on('click', function () {
        const format = $(this).data('format');
        const url = `/table/export?table=${config.table}&columns=${config.columns.join(',')}&format=${format}&search=${searchInput.val()}&orderBy=${config.orderBy}&orderType=${config.orderType}`;
        window.open(url, '_blank');
    });

    fetchData();
}