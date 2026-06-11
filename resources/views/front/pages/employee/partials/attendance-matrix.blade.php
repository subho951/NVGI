<style>
    .attendance-matrix-panel {
        overflow: hidden;
        border: 1px solid #dce8f2;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(28, 55, 82, .05);
    }

    .attendance-matrix-scroll {
        overflow-x: auto;
        overscroll-behavior-inline: contain;
    }

    .attendance-matrix {
        width: max-content;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        color: #12213a;
        font-size: 11px;
    }

    .attendance-matrix th,
    .attendance-matrix td {
        padding: 12px 9px;
        border: 0;
        border-bottom: 3px solid #e5f3fc;
        background: #ffffff;
        text-align: left;
        vertical-align: middle;
    }

    .attendance-matrix th {
        position: sticky;
        z-index: 4;
        top: 0;
        padding-top: 11px;
        padding-bottom: 11px;
        color: #15243d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .attendance-matrix tbody tr:last-child td {
        border-bottom: 0;
    }

    .attendance-matrix .serial-column {
        width: 44px;
        min-width: 44px;
    }

    .attendance-matrix .employee-column {
        width: 260px;
        min-width: 260px;
    }

    .attendance-matrix .count-column {
        width: 72px;
        min-width: 72px;
        text-align: center;
    }

    .attendance-matrix .date-column {
        width: 142px;
        min-width: 142px;
        text-align: center;
    }

    .attendance-matrix .sticky-column {
        position: sticky;
        z-index: 2;
        background: #ffffff;
    }

    .attendance-matrix th.sticky-column {
        z-index: 6;
    }

    .attendance-matrix .sticky-serial { left: 0; }
    .attendance-matrix .sticky-employee {
        left: 44px;
        box-shadow: 8px 0 12px rgba(31, 58, 84, .05);
    }

    .attendance-employee-name {
        font-weight: 900;
        white-space: normal;
    }

    .attendance-employee-code {
        margin-top: 3px;
        color: #52647a;
        font-size: 9px;
        font-weight: 900;
    }

    .attendance-employee-details {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 7px;
    }

    .attendance-category,
    .attendance-location {
        display: inline-flex;
        padding: 6px 9px;
        border: 1px solid #d7e4fa;
        border-radius: 999px;
        color: #234984;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.25;
        white-space: normal;
    }

    .attendance-location {
        border-color: #dfe5ee;
        color: #47576b;
    }

    .attendance-count {
        display: inline-grid;
        min-width: 28px;
        height: 28px;
        place-items: center;
        border-radius: 999px;
        background: #e7f1ff;
        color: #1265c4;
        font-weight: 900;
    }

    .attendance-count.absent {
        background: #ffe8e7;
        color: #c12b25;
    }

    .attendance-count.none {
        background: #f1f4f7;
        color: #7c8794;
    }

    .attendance-day {
        display: flex;
        min-height: 44px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    .attendance-class {
        width: 100%;
        padding-bottom: 7px;
        border-bottom: 1px dashed #dce4ec;
    }

    .attendance-class:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .attendance-class-meta {
        margin-bottom: 5px;
        color: #7b8796;
        font-size: 8px;
        font-weight: 800;
        line-height: 1.3;
        text-align: center;
        white-space: normal;
    }

    .attendance-badges {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .attendance-badge {
        display: inline-flex;
        min-width: 86px;
        min-height: 27px;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 900;
        line-height: 1;
        text-decoration: none;
        white-space: nowrap;
    }

    button.attendance-badge {
        cursor: pointer;
        font-family: inherit;
    }

    .attendance-badge.in {
        border-color: #9de3b6;
        background: #e1f9e9;
        color: #087b3b;
    }

    .attendance-badge.in.late {
        border-color: #8fc5ff;
        background: #e3f1ff;
        color: #075fb7;
    }

    .attendance-badge.out {
        border-color: #a8dcfb;
        background: #e6f6ff;
        color: #0574ad;
    }

    .attendance-badge.absent {
        border-color: #ffaaa7;
        background: #ffe8e7;
        color: #c12b25;
    }

    .attendance-badge.pending {
        border-color: #e1e5ea;
        background: #f3f5f7;
        color: #7a8490;
    }

    .attendance-late-note {
        display: block;
        margin-top: 3px;
        color: #1265c4;
        font-size: 8px;
        font-weight: 900;
        text-align: center;
    }

    .attendance-empty {
        padding: 42px 20px;
        color: #738092;
        text-align: center;
    }

    .attendance-photo-layer {
        position: fixed;
        z-index: 9999;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(13, 24, 38, .72);
    }

    .attendance-photo-layer.open {
        display: flex;
    }

    .attendance-photo-card {
        position: relative;
        width: min(100%, 440px);
        padding: 16px;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
    }

    .attendance-photo-card img {
        display: block;
        width: 100%;
        max-height: 68vh;
        border-radius: 12px;
        object-fit: contain;
        background: #eef2f5;
    }

    .attendance-photo-caption {
        margin: 12px 42px 0 0;
        color: #24344a;
        font-size: 12px;
        font-weight: 900;
    }

    .attendance-photo-close {
        position: absolute;
        right: 14px;
        bottom: 10px;
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 0;
        border-radius: 50%;
        background: #172f49;
        color: #ffffff;
        cursor: pointer;
    }

    @media (max-width: 760px) {
        .attendance-matrix .employee-column {
            width: 210px;
            min-width: 210px;
        }

        .attendance-matrix .sticky-employee {
            box-shadow: 7px 0 10px rgba(31, 58, 84, .08);
        }
    }
</style>

<section class="attendance-matrix-panel">
    @if($rows->isNotEmpty())
        <div class="attendance-matrix-scroll">
            <table id="{{ $matrix_table_id ?? 'attendanceMatrix' }}" class="attendance-matrix">
                <thead>
                    <tr>
                        <th class="serial-column sticky-column sticky-serial">#</th>
                        <th class="employee-column sticky-column sticky-employee">Employee Details</th>
                        <th class="count-column">Late</th>
                        <th class="count-column">Absent</th>
                        @foreach($report_dates as $reportDate)
                            <th class="date-column">
                                {{ $reportDate['label'] }}
                                <small style="display:block;margin-top:2px;color:#7d8996;">{{ $reportDate['day'] }}</small>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td class="serial-column sticky-column sticky-serial">{{ $loop->iteration }}</td>
                            <td class="employee-column sticky-column sticky-employee">
                                <div class="attendance-employee-name">{{ $row['employee_name'] ?: '-' }}</div>
                                <div class="attendance-employee-code">{{ $row['employee_no'] ?: '-' }}</div>
                                <div class="attendance-employee-details">
                                    <span class="attendance-category">{{ $row['categories'] ?: '-' }}</span>
                                    <span class="attendance-location">{{ $row['branches'] ?: '-' }}</span>
                                </div>
                            </td>
                            <td class="count-column">
                                <span
                                    class="attendance-count {{ $row['late_count'] > 0 ? '' : 'none' }}"
                                    data-late-count="{{ $row['late_count'] }}"
                                >
                                    {{ $row['late_count'] }}
                                </span>
                            </td>
                            <td class="count-column">
                                <span
                                    class="attendance-count {{ $row['absent_count'] > 0 ? 'absent' : 'none' }}"
                                    data-absent-count="{{ $row['absent_count'] }}"
                                >
                                    {{ $row['absent_count'] }}
                                </span>
                            </td>
                            @foreach($report_dates as $reportDate)
                                @php($dayEntries = $row['days'][$reportDate['key']] ?? collect())
                                <td class="date-column">
                                    <div class="attendance-day">
                                        @forelse($dayEntries as $entry)
                                            <div class="attendance-class">
                                                <div class="attendance-class-meta">
                                                    {{ $entry['branch_name'] }} | {{ $entry['scheduled_time'] ?: 'Time not set' }}
                                                </div>
                                                <div class="attendance-badges">
                                                    @if($entry['punch_in_time'])
                                                        @if($entry['punch_in_image'])
                                                            <button
                                                                type="button"
                                                                class="attendance-badge in {{ $entry['is_late'] ? 'late' : '' }} js-attendance-photo"
                                                                data-photo="{{ url('public' . $entry['punch_in_image']) }}"
                                                                data-caption="{{ $row['employee_name'] }} | IN {{ $entry['punch_in_time'] }}"
                                                            >
                                                                IN: {{ $entry['punch_in_time'] }}
                                                            </button>
                                                        @else
                                                            <span class="attendance-badge in {{ $entry['is_late'] ? 'late' : '' }}">
                                                                IN: {{ $entry['punch_in_time'] }}
                                                            </span>
                                                        @endif
                                                        @if($entry['is_late'])
                                                            <span class="attendance-late-note">Late {{ $entry['late_minutes'] }} min</span>
                                                        @endif
                                                    @endif

                                                    @if($entry['punch_out_time'])
                                                        @if($entry['punch_out_image'])
                                                            <button
                                                                type="button"
                                                                class="attendance-badge out js-attendance-photo"
                                                                data-photo="{{ url('public' . $entry['punch_out_image']) }}"
                                                                data-caption="{{ $row['employee_name'] }} | OUT {{ $entry['punch_out_time'] }}"
                                                            >
                                                                OUT: {{ $entry['punch_out_time'] }}
                                                            </button>
                                                        @else
                                                            <span class="attendance-badge out">OUT: {{ $entry['punch_out_time'] }}</span>
                                                        @endif
                                                    @endif

                                                    @if(!$entry['punch_in_time'])
                                                        <span class="attendance-badge {{ $entry['status'] === 'absent' ? 'absent' : 'pending' }}">
                                                            {{ $entry['status'] === 'absent' ? 'ABSENT' : '--' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <span style="color:#8994a2;">--</span>
                                        @endforelse
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="attendance-empty">No roster or attendance records found for the selected filters.</div>
    @endif
</section>

<div class="attendance-photo-layer" id="attendancePhotoLayer" role="dialog" aria-modal="true" aria-label="Attendance photo">
    <div class="attendance-photo-card">
        <img id="attendancePhotoPreview" alt="Attendance punch photo">
        <div class="attendance-photo-caption" id="attendancePhotoCaption"></div>
        <button type="button" class="attendance-photo-close" id="attendancePhotoClose" aria-label="Close photo">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<script>
    (function() {
        const layer = document.getElementById('attendancePhotoLayer');
        const preview = document.getElementById('attendancePhotoPreview');
        const caption = document.getElementById('attendancePhotoCaption');
        const closeButton = document.getElementById('attendancePhotoClose');

        if (!layer || !preview || !caption || !closeButton) {
            return;
        }

        document.querySelectorAll('.js-attendance-photo').forEach(function(button) {
            button.addEventListener('click', function() {
                preview.src = button.dataset.photo || '';
                caption.textContent = button.dataset.caption || 'Attendance photo';
                layer.classList.add('open');
            });
        });

        function closePhoto() {
            layer.classList.remove('open');
            preview.removeAttribute('src');
        }

        closeButton.addEventListener('click', closePhoto);
        layer.addEventListener('click', function(event) {
            if (event.target === layer) {
                closePhoto();
            }
        });
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePhoto();
            }
        });
    })();
</script>
