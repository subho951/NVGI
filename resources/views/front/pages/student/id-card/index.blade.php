@extends('front.layouts.afterlogin')
@section('content')
<?php

$brand = $brand ?? [];
$searchStudentId = trim((string)($search_student_id ?? ''));
$searchUnitId = (($search_unit_id !== '' && $search_unit_id !== null) ? (int)$search_unit_id : '');
$searchBranchId = (($search_branch_id !== '' && $search_branch_id !== null) ? (int)$search_branch_id : '');
$searchClassId = (($search_class_id !== '' && $search_class_id !== null) ? (int)$search_class_id : '');
$filtersLocked = !empty($filters_locked);
$selectionKey = trim((string)($selection_key ?? ''));
$studentsTotal = (($students && method_exists($students, 'total')) ? $students->total() : 0);
?>
<style>
    .id-card-page{
        color:#1f2937;
    }
    .id-card-hero{
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 52%, #2563eb 100%);
        color: #ffffff;
        border-radius: 18px;
        padding: 1.15rem 1.25rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
    }
    .id-card-hero h2{
        color:#ffffff;
        margin-bottom: .35rem;
        font-weight: 800;
        letter-spacing: .2px;
    }
    .id-card-hero p{
        margin-bottom: 0;
        color: rgba(255,255,255,.88);
    }
    .id-card-search-card,
    .id-card-result-card,
    .id-card-empty-card{
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }
    .id-card-search-card .card-header,
    .id-card-result-card .card-header{
        background: linear-gradient(90deg, #f8fafc 0%, #eff6ff 100%);
        border-bottom: 1px solid #dbeafe;
    }
    .id-card-search-card .card-body,
    .id-card-result-card .card-body{
        background: #ffffff;
    }
    .field-help{
        font-size: .78rem;
        color:#64748b;
    }
    .student-id-filter-lock{
        border-left: 4px solid #f59e0b;
        background: #fffbeb;
        color: #92400e;
        font-weight: 700;
    }
    .id-card-table thead th{
        background: #0f172a;
        color: #ffffff;
        font-size: .78rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .id-card-table tbody td{
        vertical-align: middle;
    }
    .student-thumb{
        width: 42px;
        height: 42px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
    }
    .student-name-cell{
        font-weight: 700;
        color:#0f172a;
    }
    .student-code-cell{
        font-size: .82rem;
        color:#475569;
    }
    .selection-toolbar{
        background: linear-gradient(90deg, #f8fafc 0%, #eef2ff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: .7rem .85rem;
    }
    .selected-count-badge{
        font-size: .85rem;
        padding: .4rem .65rem;
    }
    .student-select-checkbox{
        width: 1.05rem;
        height: 1.05rem;
        cursor: pointer;
    }
    .filters-disabled{
        background: #f8fafc !important;
        pointer-events: none;
        opacity: .72;
    }
    .empty-state{
        border: 1px dashed #cbd5e1;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 18px;
        padding: 2rem;
        text-align: center;
        color: #475569;
    }
    .empty-state h5{
        font-weight: 800;
        color:#0f172a;
    }
    .pagination{
        margin-bottom: 0;
    }
    .pagination .page-link{
        border-radius: .6rem !important;
        margin: 0 .14rem;
    }
</style>
<div class="id-card-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div class="id-card-hero flex-grow-1">
            <h2>Generate ID Card</h2>
            <p>Search students with Unit, Branch and Class filters, or use a Student ID to override everything else.</p>
        </div>
        <div class="d-flex align-items-start">
            <a href="{{ url('student/list') }}" class="btn btn-outline-secondary btn-sm">Back to Students</a>
        </div>
    </div>

    @if(session('success_message'))
    <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide" role="alert">
        {{ session('success_message') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error_message'))
    <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide" role="alert">
        {{ session('error_message') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following issues:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card id-card-search-card mb-4">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-1 fw-bold text-slate-800">Search Filters</h5>
                    <div class="text-muted small">The Student ID field takes priority over Unit, Branch and Class selections.</div>
                </div>
                <!-- <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Dependent dropdowns via AJAX</span> -->
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('student.id-card.index') }}" id="idCardSearchForm">
                <input type="hidden" name="search_submitted" value="1">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label for="student_id" class="form-label fw-semibold">Student ID</label>
                        <input type="text" class="form-control form-control-sm" name="student_id" id="student_id" placeholder="Enter Student ID" value="{{ $searchStudentId }}">
                        <div class="field-help mt-1">Typing a Student ID ignores the dropdown filters.</div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="unit_id" class="form-label fw-semibold">Unit</label>
                        <select class="form-select form-select-sm" name="unit_id" id="unit_id" {{ $filtersLocked ? 'disabled' : '' }}>
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ ((int)$searchUnitId === (int)$unit->id) ? 'selected' : '' }}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="branch_id" class="form-label fw-semibold">Branch</label>
                        <select class="form-select form-select-sm" name="branch_id" id="branch_id" {{ $filtersLocked ? 'disabled' : '' }}>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ ((int)$searchBranchId === (int)$branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="class_id" class="form-label fw-semibold">Class</label>
                        <select class="form-select form-select-sm" name="class_id" id="class_id" {{ $filtersLocked ? 'disabled' : (($searchBranchId === '' && $searchClassId === '') ? 'disabled' : '') }}>
                            <option value="">Select Class</option>
                            @foreach($classes as $classRow)
                                <option value="{{ $classRow->id }}" {{ ((int)$searchClassId === (int)$classRow->id) ? 'selected' : '' }}>{{ $classRow->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <a href="{{ route('student.id-card.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Search</button>
                </div>
            </form>

            @if($filtersLocked)
                <div class="alert student-id-filter-lock mt-3 mb-0 py-2">
                    Student ID search is active. The dropdown filters are disabled until the Student ID field is cleared.
                </div>
            @endif
        </div>
    </div>

    @if($search_applied)
        <form method="POST" action="{{ route('student.id-card.preview') }}" target="_blank" id="idCardPreviewForm">
            @csrf
            <input type="hidden" name="selection_key" value="{{ $selectionKey }}">
            <div class="card id-card-result-card">
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="mb-1 fw-bold text-slate-800">Student Listing</h5>
                            <div class="text-muted small">{{ $studentsTotal }} student{{ ($studentsTotal === 1) ? '' : 's' }} found</div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSelectionBtn">Clear Selection</button>
                            <button type="submit" class="btn btn-success btn-sm px-4" id="generateBtn" data-generate-card-btn disabled>Generate ID Card</button>
                            <button type="submit"
                                    class="btn btn-warning btn-sm px-4"
                                    id="generateEscortBtn"
                                    data-generate-card-btn
                                    formaction="{{ route('student.escort-card.preview') }}"
                                    disabled>Generate Escort Card</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="selection-toolbar mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <span class="badge bg-primary selected-count-badge" id="selectedCountBadge">0</span>
                            <span class="ms-2 text-muted">selected for preview</span>
                        </div>
                        <div class="small text-muted">
                            Select rows on this page, then open the printable preview in a new tab.
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle id-card-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 52px;">
                                        <input type="checkbox" class="form-check-input student-select-checkbox" id="selectAllStudents">
                                    </th>
                                    <th>Student Name</th>
                                    <th style="width: 150px;">Student ID</th>
                                    <th>Class / Branch / Unit</th>
                                    <th style="width: 100px;" class="text-center">Photo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $row)
                                    <?php
                                        $studentName = trim((string)$row->full_name);
                                        if ($studentName === '') {
                                            $studentName = 'Student';
                                        }
                                        $studentPhoto = (($row->photo != '') ? config('constants.app_url') . config('constants.uploads_url_path') . $row->photo : config('constants.no_image_avatar'));
                                        $className = trim((string)$row->class_name);
                                        $className = ($className !== '') ? $className : '-';
                                        $unitName = trim((string)$row->unit_name);
                                        $unitName = ($unitName !== '') ? $unitName : '-';
                                        $branchName = trim((string)$row->branch_name);
                                        $branchName = ($branchName !== '') ? $branchName : '-';
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   class="form-check-input student-select-checkbox"
                                                   value="{{ $row->id }}"
                                                   data-student-id="{{ $row->id }}">
                                        </td>
                                        <td>
                                            <div class="student-name-cell">{{ $studentName }}</div>
                                            <div class="student-code-cell">Selected from the search results</div>
                                        </td>
                                        <td>
                                            <div class="student-code-cell fw-semibold">{{ $row->student_id_serial }}</div>
                                        </td>
                                        <td>
                                            <div class="student-name-cell">{{ $className }}</div>
                                            <div class="student-code-cell">{{ $branchName }} / {{ $unitName }}</div>
                                        </td>
                                        <td class="text-center">
                                            <img src="{{ $studentPhoto }}" alt="{{ e($studentName) }}" class="student-thumb">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state my-2">
                                                <h5 class="mb-2">No matching students found</h5>
                                                <div>Adjust the filters or try a different Student ID.</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($students && $students->total() > 0)
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                            <div class="text-muted small">
                                Showing {{ $students->firstItem() }} - {{ $students->lastItem() }} of {{ $students->total() }} students
                            </div>
                            <div>
                                {{ $students->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div id="selectedIdsContainer"></div>
        </form>
    @else
        <div class="card id-card-empty-card">
            <div class="card-body">
                <div class="empty-state">
                    <h5 class="mb-2">Search to start generating cards</h5>
                    <div class="mb-3">Use the filters above to load students, then select the rows you want to print.</div>
                    <div class="small text-muted">This page is designed to work well with large student lists and pagination.</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
@section('scripts')
<script>
(function () {
    const selectionKey = @json($selectionKey);
    const storageKey = 'student-id-card-selection:' + selectionKey;
    const studentIdInput = document.getElementById('student_id');
    const unitSelect = document.getElementById('unit_id');
    const branchSelect = document.getElementById('branch_id');
    const classSelect = document.getElementById('class_id');
    const searchForm = document.getElementById('idCardSearchForm');
    const previewForm = document.getElementById('idCardPreviewForm');
    const selectedIdsContainer = document.getElementById('selectedIdsContainer');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const generateButtons = Array.from(document.querySelectorAll('[data-generate-card-btn]'));
    const clearBtn = document.getElementById('clearSelectionBtn');
    const selectAll = document.getElementById('selectAllStudents');
    const branchesUrl = @json(route('student.id-card.branches'));
    const classesUrl = @json(route('student.id-card.classes'));
    let selectedIds = new Set();

    function rowCheckboxes() {
        return Array.from(document.querySelectorAll('.student-select-checkbox[data-student-id]'));
    }

    function readSelection() {
        try {
            const raw = sessionStorage.getItem(storageKey);
            if (!raw) {
                return [];
            }

            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed.map(String) : [];
        } catch (error) {
            return [];
        }
    }

    function saveSelection() {
        sessionStorage.setItem(storageKey, JSON.stringify(Array.from(selectedIds)));
    }

    function updateHiddenInputs() {
        if (!selectedIdsContainer) {
            return;
        }

        selectedIdsContainer.innerHTML = '';
        Array.from(selectedIds).forEach(function (studentId) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = studentId;
            selectedIdsContainer.appendChild(input);
        });
    }

    function updateBadge() {
        const count = selectedIds.size;
        if (selectedCountBadge) {
            selectedCountBadge.textContent = String(count);
        }
        generateButtons.forEach(function (button) {
            button.disabled = count === 0;
        });
    }

    function updateSelectAllState() {
        if (!selectAll) {
            return;
        }

        const rows = rowCheckboxes();
        if (!rows.length) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
            return;
        }

        const checkedCount = rows.filter(function (checkbox) {
            return checkbox.checked;
        }).length;

        selectAll.checked = (checkedCount === rows.length);
        selectAll.indeterminate = (checkedCount > 0 && checkedCount < rows.length);
    }

    function syncRowCheckboxes() {
        rowCheckboxes().forEach(function (checkbox) {
            checkbox.checked = selectedIds.has(String(checkbox.value));
        });
        updateSelectAllState();
    }

    function refreshSelectionUi() {
        saveSelection();
        updateHiddenInputs();
        updateBadge();
        syncRowCheckboxes();
    }

    function restoreSelection() {
        selectedIds = new Set(readSelection());
        refreshSelectionUi();
    }

    function clearSelection() {
        selectedIds = new Set();
        refreshSelectionUi();
    }

    function setStudentIdMode() {
        const locked = studentIdInput && studentIdInput.value.trim() !== '';
        [unitSelect, branchSelect, classSelect].forEach(function (selectElement) {
            if (!selectElement) {
                return;
            }
            selectElement.disabled = locked;
            selectElement.classList.toggle('filters-disabled', locked);
        });
    }

    function updateDependentDisabledState() {
        if (!branchSelect || !classSelect) {
            return;
        }

        if (studentIdInput && studentIdInput.value.trim() !== '') {
            branchSelect.disabled = true;
            classSelect.disabled = true;
            return;
        }

        branchSelect.disabled = !(unitSelect && unitSelect.value !== '');
        classSelect.disabled = !(branchSelect && (branchSelect.value !== '' || classSelect.value !== ''));
    }

    function buildOptions(selectElement, items, placeholder, selectedValue, valueKey, labelKey) {
        if (!selectElement) {
            return;
        }

        selectElement.innerHTML = '';
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = placeholder;
        selectElement.appendChild(defaultOption);

        (items || []).forEach(function (item) {
            const option = document.createElement('option');
            option.value = String(item[valueKey]);
            option.textContent = item[labelKey];
            if (selectedValue !== undefined && selectedValue !== null && String(selectedValue) === String(item[valueKey])) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });
    }

    async function loadBranches(unitId, selectedBranchId) {
        if (!branchSelect) {
            return;
        }

        buildOptions(branchSelect, [], 'Select Branch', '', 'id', 'name');
        buildOptions(classSelect, [], 'Select Class', '', 'id', 'name');

        if (!unitId) {
            updateDependentDisabledState();
            return;
        }

        const url = new URL(branchesUrl, window.location.origin);
        url.searchParams.set('unit_id', unitId);

        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to load branches.');
        }

        const json = await response.json();
        buildOptions(branchSelect, json.branches || [], 'Select Branch', selectedBranchId, 'id', 'name');
        updateDependentDisabledState();
    }

    async function loadClasses(branchId, selectedClassId) {
        if (!classSelect) {
            return;
        }

        buildOptions(classSelect, [], 'Select Class', '', 'id', 'name');

        if (!branchId) {
            updateDependentDisabledState();
            return;
        }

        const url = new URL(classesUrl, window.location.origin);
        url.searchParams.set('branch_id', branchId);

        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to load classes.');
        }

        const json = await response.json();
        buildOptions(classSelect, json.classes || [], 'Select Class', selectedClassId, 'id', 'name');
        updateDependentDisabledState();
    }

    async function hydrateDependentDropdowns() {
        if (studentIdInput && studentIdInput.value.trim() !== '') {
            setStudentIdMode();
            updateDependentDisabledState();
            return;
        }

        if (unitSelect && unitSelect.value) {
            try {
                if (!branchSelect || branchSelect.options.length <= 1) {
                    await loadBranches(unitSelect.value, branchSelect ? branchSelect.value : '');
                }
            } catch (error) {
                console.error(error);
            }
        }

        if (branchSelect && branchSelect.value) {
            try {
                if (!classSelect || classSelect.options.length <= 1) {
                    await loadClasses(branchSelect.value, classSelect ? classSelect.value : '');
                }
            } catch (error) {
                console.error(error);
            }
        }

        updateDependentDisabledState();
    }

    document.addEventListener('change', function (event) {
        const checkbox = event.target.closest('.student-select-checkbox[data-student-id]');
        if (checkbox) {
            const studentId = String(checkbox.value);
            if (checkbox.checked) {
                selectedIds.add(studentId);
            } else {
                selectedIds.delete(studentId);
            }
            refreshSelectionUi();
            return;
        }

        if (event.target === selectAll) {
            rowCheckboxes().forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                if (selectAll.checked) {
                    selectedIds.add(String(checkbox.value));
                } else {
                    selectedIds.delete(String(checkbox.value));
                }
            });
            refreshSelectionUi();
            return;
        }
    });

    if (studentIdInput) {
        studentIdInput.addEventListener('input', function () {
            setStudentIdMode();
            updateDependentDisabledState();
        });
    }

    if (unitSelect) {
        unitSelect.addEventListener('change', async function () {
            if (studentIdInput && studentIdInput.value.trim() !== '') {
                return;
            }

            try {
                await loadBranches(this.value, '');
            } catch (error) {
                console.error(error);
                buildOptions(branchSelect, [], 'Select Branch', '', 'id', 'name');
                buildOptions(classSelect, [], 'Select Class', '', 'id', 'name');
            }
            updateDependentDisabledState();
        });
    }

    if (branchSelect) {
        branchSelect.addEventListener('change', async function () {
            if (studentIdInput && studentIdInput.value.trim() !== '') {
                return;
            }

            try {
                await loadClasses(this.value, '');
            } catch (error) {
                console.error(error);
                buildOptions(classSelect, [], 'Select Class', '', 'id', 'name');
            }
            updateDependentDisabledState();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            clearSelection();
        });
    }

    if (previewForm) {
        previewForm.addEventListener('submit', function (event) {
            if (selectedIds.size === 0) {
                event.preventDefault();
                alert('Please select at least one student.');
                return;
            }

            updateHiddenInputs();
        });
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowCheckboxes().forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                if (selectAll.checked) {
                    selectedIds.add(String(checkbox.value));
                } else {
                    selectedIds.delete(String(checkbox.value));
                }
            });
            refreshSelectionUi();
        });
    }

    setStudentIdMode();
    updateDependentDisabledState();
    restoreSelection();
    hydrateDependentDropdowns();
})();
</script>
@endsection
