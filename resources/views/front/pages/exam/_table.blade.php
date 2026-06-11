@php
    use App\Helpers\Helper;
    $controllerRoute = $module['controller_route'];
@endphp

<div class="exam-table-wrap">
    <div class="table-responsive">
        <table id="example" class="table table-hover align-middle exam-table">
            <thead>
                <tr>
                    <th style="width: 70px;">#</th>
                    <th>Exam Name</th>
                    <th>Unit Name(s)</th>
                    <th>Description</th>
                    <th>Class subject full marks</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 190px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $encodedId = Helper::encoded($row->id);
                        $statusUrl = $controllerRoute . '/change-status/';
                        $unitNames = [];
                        $markGroups = [];
                        $subjectRowsCount = 0;

                        if ($row->fullMarks && $row->fullMarks->count() > 0) {
                            foreach ($row->fullMarks as $markRow) {
                                $unitName = ($markRow->unit) ? $markRow->unit->name : '';
                                $className = ($markRow->examClass) ? $markRow->examClass->name : '';
                                $subjectName = ($markRow->subject) ? $markRow->subject->name : '';
                                $marksValue = (string) $markRow->full_marks;
                                $groupKey = (int) $markRow->unit_id . '-' . (int) $markRow->class_id;

                                if ($unitName && !in_array($unitName, $unitNames, true)) {
                                    $unitNames[] = $unitName;
                                }

                                if (!isset($markGroups[$groupKey])) {
                                    $markGroups[$groupKey] = [
                                        'unit' => $unitName,
                                        'class' => $className,
                                        'subjects' => [],
                                        'marks' => [],
                                    ];
                                }

                                $subjectLabel = !empty($subjectName) ? $subjectName : 'Subject not assigned';
                                if (!in_array($subjectLabel, $markGroups[$groupKey]['subjects'], true)) {
                                    $markGroups[$groupKey]['subjects'][] = $subjectLabel;
                                }

                                if (!in_array($marksValue, $markGroups[$groupKey]['marks'], true)) {
                                    $markGroups[$groupKey]['marks'][] = $marksValue;
                                }

                                $subjectRowsCount++;
                            }
                        }

                        $classRowsCount = count($markGroups);
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="exam-table-meta">
                                <span class="exam-table-name">{{ $row->name }}</span>
                                <span class="exam-table-subtext">{{ number_format($classRowsCount) }} class row{{ ($classRowsCount === 1) ? '' : 's' }} / {{ number_format($subjectRowsCount) }} subject row{{ ($subjectRowsCount === 1) ? '' : 's' }}</span>
                            </div>
                        </td>
                        <td>
                            @if(count($unitNames) > 0)
                                <div class="exam-tags">
                                    @foreach($unitNames as $unitName)
                                        <span class="exam-tag">{{ $unitName }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="exam-description">
                                {{ !empty($row->description) ? \Illuminate\Support\Str::limit($row->description, 140) : '-' }}
                            </div>
                        </td>
                        <td>
                            @if($classRowsCount > 0)
                                <div class="d-flex flex-column gap-2">
                                    @foreach($markGroups as $markGroup)
                                        <div class="exam-mark-group d-flex flex-wrap align-items-center gap-2">
                                            <span class="exam-tag">{{ !empty($markGroup['unit']) ? $markGroup['unit'] : '-' }}</span>
                                            <span class="text-muted">/</span>
                                            <span class="exam-tag">{{ !empty($markGroup['class']) ? $markGroup['class'] : '-' }}</span>
                                            <span class="text-muted">/</span>
                                            <span class="exam-tags">
                                                @foreach($markGroup['subjects'] as $subjectLabel)
                                                    <span class="exam-tag">{{ $subjectLabel }}</span>
                                                @endforeach
                                            </span>
                                            @foreach($markGroup['marks'] as $marksValue)
                                                <span class="exam-status-pill exam-status-active">{{ $marksValue }}</span>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($row->status == 1)
                                <span class="exam-status-pill exam-status-active">
                                    <i class="fa-solid fa-circle-check"></i> Active
                                </span>
                            @else
                                <span class="exam-status-pill exam-status-blocked">
                                    <i class="fa-solid fa-circle-exclamation"></i> Blocked
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="exam-action-group">
                                <a href="{{ url($controllerRoute . '/edit/' . $encodedId) }}" class="btn btn-outline-primary btn-sm exam-action-btn" title="Edit {{ $module['title'] }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </a>
                                @if($row->status == 1)
                                    <a href="javascript:void(0);" onclick="showConfirmBox('{{ $encodedId }}', '{{ $statusUrl }}', 'Are you sure you want to deactivate this record?')" class="btn btn-outline-warning btn-sm exam-action-btn" title="Deactivate {{ $module['title'] }}">
                                        <i class="fa-solid fa-ban me-1"></i> Block
                                    </a>
                                @else
                                    <a href="javascript:void(0);" onclick="showConfirmBox('{{ $encodedId }}', '{{ $statusUrl }}', 'Are you sure you want to activate this record?')" class="btn btn-outline-success btn-sm exam-action-btn" title="Activate {{ $module['title'] }}">
                                        <i class="fa-solid fa-circle-check me-1"></i> Activate
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="exam-empty-state">
                                <div class="fw-bold mb-1">No exam blueprints found</div>
                                <div>Create the first exam above to start mapping unit and class-wise full marks.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
