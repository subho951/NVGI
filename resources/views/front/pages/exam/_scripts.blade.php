@php
    $examClasses = $classes->map(function ($class) {
        return [
            'id' => $class->id,
            'name' => $class->name,
            'unit_id' => (int) $class->unit_id,
        ];
    })->values();
    $examSubjects = collect($class_subjects ?? [])->map(function ($classSubject) {
        return [
            'id' => $classSubject->subject_id,
            'name' => ($classSubject->subject) ? $classSubject->subject->name : '',
            'class_id' => (int) $classSubject->class_id,
        ];
    })->filter(function ($subject) {
        return ($subject['id'] > 0 && $subject['name'] !== '');
    })->values();
@endphp
<script>
    $(function() {
        const examClasses = @json($examClasses);
        const examSubjects = @json($examSubjects);

        function buildClassOptions(unitId, selectedClassId) {
            let html = '<option value="">Select</option>';

            examClasses.forEach(function(item) {
                if (String(item.unit_id) === String(unitId)) {
                    const selected = String(item.id) === String(selectedClassId) ? ' selected' : '';
                    html += '<option value="' + item.id + '"' + selected + '>' + item.name + '</option>';
                }
            });

            return html;
        }

        function normalizeSelectedSubjects(value) {
            if (Array.isArray(value)) {
                return value.map(String).filter(function(item) {
                    return item !== '';
                });
            }

            if (value === null || value === undefined || value === '') {
                return [];
            }

            return String(value).split(',').map(function(item) {
                return $.trim(item);
            }).filter(function(item) {
                return item !== '';
            });
        }

        function buildSubjectOptions(classId, selectedSubjectIds) {
            const selectedIds = normalizeSelectedSubjects(selectedSubjectIds);
            let html = '';

            examSubjects.forEach(function(item) {
                if (String(item.class_id) === String(classId)) {
                    const selected = selectedIds.indexOf(String(item.id)) !== -1 ? ' selected' : '';
                    html += '<option value="' + item.id + '"' + selected + '>' + item.name + '</option>';
                }
            });

            if (html === '') {
                html = '<option value="" disabled>Select class first</option>';
            }

            return html;
        }

        function reindexExamMarkRows() {
            $('#examMarksTable tbody tr.exam-mark-row').each(function(index) {
                const $row = $(this);
                $row.find('.exam-unit-select').attr('name', 'unit_id[' + index + ']');
                $row.find('.exam-class-select').attr('name', 'class_id[' + index + ']');
                $row.find('.exam-subject-select').attr('name', 'subject_id[' + index + '][]');
                $row.find('input[name^="full_marks"]').attr('name', 'full_marks[' + index + ']');
            });
        }

        function syncRowClassOptions($row) {
            const $unitSelect = $row.find('.exam-unit-select');
            const $classSelect = $row.find('.exam-class-select');
            const unitId = $unitSelect.val() || '';
            const selectedClassId = $classSelect.data('selected-class') || $classSelect.val() || '';

            $classSelect.html(buildClassOptions(unitId, selectedClassId));

            if (selectedClassId) {
                $classSelect.val(selectedClassId);
            }

            $classSelect.data('selected-class', $classSelect.val() || '');
        }

        function syncRowSubjectOptions($row) {
            const $classSelect = $row.find('.exam-class-select');
            const $subjectSelect = $row.find('.exam-subject-select');
            const classId = $classSelect.val() || '';
            const selectedSubjectIds = $subjectSelect.data('selected-subjects') || $subjectSelect.val() || [];

            $subjectSelect.html(buildSubjectOptions(classId, selectedSubjectIds));

            if (selectedSubjectIds) {
                $subjectSelect.val(normalizeSelectedSubjects(selectedSubjectIds));
            }

            $subjectSelect.data('selected-subjects', ($subjectSelect.val() || []).join(','));
        }

        $('#examMarksTable tbody tr.exam-mark-row').each(function() {
            const $row = $(this);
            syncRowClassOptions($row);
            syncRowSubjectOptions($row);
        });
        reindexExamMarkRows();

        $(document).on('change', '.exam-unit-select', function() {
            const $row = $(this).closest('tr');
            $row.find('.exam-class-select').data('selected-class', '');
            $row.find('.exam-subject-select').data('selected-subjects', '').val('').html('<option value="" disabled>Select class first</option>');
            syncRowClassOptions($row);
            syncRowSubjectOptions($row);
        });

        $(document).on('change', '.exam-class-select', function() {
            const $row = $(this).closest('tr');
            $row.find('.exam-subject-select').data('selected-subjects', '');
            syncRowSubjectOptions($row);
        });

        $(document).on('change', '.exam-subject-select', function() {
            $(this).data('selected-subjects', ($(this).val() || []).join(','));
        });

        $('#addExamMarkRow').on('click', function() {
            const $tbody = $('#examMarksTable tbody');
            const $lastRow = $tbody.find('tr.exam-mark-row:last');

            if (!$lastRow.length) {
                return;
            }

            const $newRow = $lastRow.clone();
            const copiedUnitId = $lastRow.find('.exam-unit-select').val() || '';

            $newRow.find('.exam-unit-select').val(copiedUnitId);
            $newRow.find('.exam-class-select').data('selected-class', '').val('').html('<option value="">Select</option>');
            $newRow.find('.exam-subject-select').data('selected-subjects', '').val('').html('<option value="" disabled>Select class first</option>');
            $newRow.find('input[name^="full_marks"]').val('');

            $tbody.append($newRow);
            reindexExamMarkRows();
            syncRowClassOptions($newRow);
            syncRowSubjectOptions($newRow);
            $newRow.find('.exam-unit-select').trigger('focus');
        });

        $(document).on('click', '.remove-exam-mark-row', function() {
            const $tbody = $('#examMarksTable tbody');
            const $rows = $tbody.find('tr.exam-mark-row');
            const $currentRow = $(this).closest('tr');

            if ($rows.length > 1) {
                $currentRow.remove();
                reindexExamMarkRows();
                return;
            }

            $currentRow.find('.exam-unit-select').val('');
            $currentRow.find('.exam-class-select').data('selected-class', '').val('').html('<option value="">Select</option>');
            $currentRow.find('.exam-subject-select').data('selected-subjects', '').val('').html('<option value="" disabled>Select class first</option>');
            $currentRow.find('input[name^="full_marks"]').val('');
            reindexExamMarkRows();
            syncRowClassOptions($currentRow);
            syncRowSubjectOptions($currentRow);
        });
    });
</script>
