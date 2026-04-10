@php
    $examNameValue = old('name', (($single_row) ? $single_row->name : ''));
    $examDescriptionValue = old('description', (($single_row) ? $single_row->description : ''));
    $unitInput = old('unit_id');
    $classInput = old('class_id');
    $markInput = old('full_marks');
    $formRows = [];

    if (is_array($unitInput) && count($unitInput) > 0) {
        foreach ($unitInput as $index => $unitId) {
            $formRows[] = [
                'unit_id' => $unitId,
                'class_id' => ((is_array($classInput) && array_key_exists($index, $classInput)) ? $classInput[$index] : ''),
                'full_marks' => ((is_array($markInput) && array_key_exists($index, $markInput)) ? $markInput[$index] : ''),
            ];
        }
    } elseif (($single_row) && $single_row->fullMarks && count($single_row->fullMarks) > 0) {
        foreach ($single_row->fullMarks as $markRow) {
            $formRows[] = [
                'unit_id' => $markRow->unit_id,
                'class_id' => $markRow->class_id,
                'full_marks' => $markRow->full_marks,
            ];
        }
    } else {
        $formRows[] = [
            'unit_id' => '',
            'class_id' => '',
            'full_marks' => '',
        ];
    }
@endphp

<form method="POST" action="" class="row g-3 exam-builder-form">
    @csrf
    <div class="col-lg-4">
        <label for="name" class="form-label">Exam Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="name" id="name" placeholder="Write exam name" value="{{ $examNameValue }}" required>
        @error('name')
            <span class="text-danger small d-block mt-1">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-8">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control form-control-sm" name="description" id="description" rows="2" placeholder="Write optional description">{{ $examDescriptionValue }}</textarea>
        @error('description')
            <span class="text-danger small d-block mt-1">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div>
                <label class="form-label mb-0">Unit wise class wise full marks <span class="text-danger">*</span></label>
                <div class="exam-inline-hint">Build the blueprint that powers the exam marks entry screen.</div>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm exam-row-add-btn" id="addExamMarkRow">
                <i class="fa-solid fa-plus me-1"></i> Add Row
            </button>
        </div>

        <div class="exam-note">
            <i class="fa-solid fa-lightbulb mt-1"></i>
            <div>
                <strong>Configuration tip</strong>
                <div class="mt-1">Use one row for each unit/class combination. The same pair cannot be repeated, which keeps the marks setup clean and accurate.</div>
            </div>
        </div>

        <div class="exam-form-table-wrap">
            <div class="table-responsive">
                <table class="table table-hover align-middle exam-form-table" id="examMarksTable">
                    <thead>
                        <tr>
                            <th>Unit Name</th>
                            <th>Class Name</th>
                            <th style="width: 160px;">Full Marks</th>
                            <th style="width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($formRows as $formRow)
                            <tr class="exam-mark-row">
                                <td>
                                    <select class="form-select form-select-sm exam-unit-select" name="unit_id[]" required>
                                        <option value="">Select</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ ((string) $formRow['unit_id'] === (string) $unit->id) ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm exam-class-select" name="class_id[]" data-selected-class="{{ $formRow['class_id'] }}" required>
                                        <option value="">Select</option>
                                        @foreach($classes as $class)
                                            @if((string) $formRow['unit_id'] === (string) $class->unit_id)
                                                <option value="{{ $class->id }}" {{ ((string) $formRow['class_id'] === (string) $class->id) ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="full_marks[]" value="{{ $formRow['full_marks'] }}" placeholder="Enter full marks" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger btn-sm exam-row-remove-btn remove-exam-mark-row">
                                        <i class="fa-solid fa-trash-can me-1"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @error('unit_id')
            <span class="text-danger d-block mt-2">{{ $message }}</span>
        @enderror
        @error('class_id')
            <span class="text-danger d-block mt-2">{{ $message }}</span>
        @enderror
        @error('full_marks')
            <span class="text-danger d-block mt-2">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-lg-4 mx-auto text-center pt-2">
        <button type="submit" class="btn exam-submit-btn btn-sm w-100">
            <i class="fa-solid fa-floppy-disk me-1"></i> {{ $action }}
        </button>
    </div>
</form>
