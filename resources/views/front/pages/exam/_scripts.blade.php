@php
    $examClasses = $classes->map(function ($class) {
        return [
            'id' => $class->id,
            'name' => $class->name,
            'unit_id' => (int) $class->unit_id,
        ];
    })->values();
@endphp
<script>
    $(function() {
        const examClasses = @json($examClasses);

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

        $('#examMarksTable tbody tr.exam-mark-row').each(function() {
            syncRowClassOptions($(this));
        });

        $(document).on('change', '.exam-unit-select', function() {
            const $row = $(this).closest('tr');
            $row.find('.exam-class-select').data('selected-class', '');
            syncRowClassOptions($row);
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
            $newRow.find('input[name="full_marks[]"]').val('');

            $tbody.append($newRow);
            syncRowClassOptions($newRow);
            $newRow.find('.exam-unit-select').trigger('focus');
        });

        $(document).on('click', '.remove-exam-mark-row', function() {
            const $tbody = $('#examMarksTable tbody');
            const $rows = $tbody.find('tr.exam-mark-row');
            const $currentRow = $(this).closest('tr');

            if ($rows.length > 1) {
                $currentRow.remove();
                return;
            }

            $currentRow.find('.exam-unit-select').val('');
            $currentRow.find('.exam-class-select').data('selected-class', '').val('').html('<option value="">Select</option>');
            $currentRow.find('input[name="full_marks[]"]').val('');
            syncRowClassOptions($currentRow);
        });
    });
</script>
