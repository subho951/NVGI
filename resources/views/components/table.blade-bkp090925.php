<!-- <button class="btn btn-sm export-btn" data-format="csv" style="border: 1px solid green;background-color: green;color: #FFF;"><i class="fa-solid fa-file-csv"></i>&nbsp;Export CSV</button>
<button class="btn btn-sm export-btn" data-format="excel" style="border: 1px solid #009688;background-color: #009688;color: #FFF;">Export Excel</button>
<button class="btn btn-sm export-btn" data-format="pdf" style="border: 1px solid #F44336;background-color: #F44336;color: #FFF;"><i class="fa-solid fa-file-pdf"></i>&nbsp;Export PDF</button> -->

<!-- <button class="export-btn btn btn-sm"
    data-format="csv"
    data-table="{{ $table }}"
    data-columns="{{ implode(',', $columns) }}"
    data-conditions='@json($conditions ?? [])'
    data-titles="{{ implode(',', $headers) }}"
    data-filename="{{ $filename ?? 'export' }}"
    style="border: 1px solid green;background-color: green;color: #FFF;">
    <i class="fa-solid fa-file-csv"></i>&nbsp;Export CSV
</button> -->
<!-- <button class="export-btn btn btn-sm"
    data-format="pdf"
    data-table="{{ $table }}"
    data-columns="{{ implode(',', $columns) }}"
    data-conditions='@json($conditions ?? [])'
    data-titles="{{ implode(',', $headers) }}"
    data-filename="{{ $filename ?? 'export' }}"
    style="border: 1px solid #F44336;background-color: #F44336;color: #FFF;">
    <i class="fa-solid fa-file-pdf"></i>&nbsp;Export PDF
</button> -->

<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <label for="{{ $containerId }}-perPage" class="form-label me-2" style="font-size: 12px;">Show</label>
        <select id="{{ $containerId }}-perPage" class="form-select d-inline-block" style="width: 70px !important;padding: 2px !important;font-size: 12px;">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50" selected>50</option>
            <option value="100">100</option>
            <option value="250">250</option>
            <option value="500">500</option>
        </select>
        <span style="font-size: 12px;">entries</span>
    </div>
    <input type="text" id="{{ $searchId }}" placeholder="Search..." style="float:right;padding: 3px;margin-bottom: 10px;width: 30%;">
</div>



<div class="mt-3 table-responsive" id="{{ $containerId }}"></div>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadTable({
            container: '#{{ $containerId }}',
            searchInput: '#{{ $searchId }}',
            exportButtons: '.export-btn',
            table: '{{ $table }}',
            columns: @json($columns),
            visibleColumns: @json($visibleColumns),
            headers: @json($headers ?? $columns),
            filename: '{{ $filename }}',
            orderBy: '{{ $orderBy ?? 'id' }}',
            orderType: '{{ $orderType ?? 'desc' }}',
            conditions: @json($conditions ?? []),
            routePrefix: '{{ $routePrefix ?? '' }}',
            showActions: {{ $showActions ?? true ? 'true' : 'false' }},
            statusColumn: '{{ $statusColumn ?? 'is_active' }}',
            submissionStatusColumn: "{{ $submissionStatusColumn ?? '' }}",
            finalSubmitColumn: "{{ $finalSubmitColumn ?? '' }}",
            imageColumns: @json($imageColumns ?? []), // ✅ Must be included
            joins: @json($joins ?? [])
        });
    });
</script>