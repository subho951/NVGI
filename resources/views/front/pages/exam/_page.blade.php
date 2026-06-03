@php
    $examRows = collect($rows ?? []);
    $totalExams = $examRows->count();
    $activeExams = $examRows->where('status', 1)->count();
    $blockedExams = $examRows->where('status', 0)->count();
    $mappingCount = $examRows->sum(function ($exam) {
        return ($exam->fullMarks) ? $exam->fullMarks->count() : 0;
    });
    $isEditMode = (($action ?? '') === 'Edit');
    $heroTitle = $isEditMode ? 'Update exam blueprint' : 'Manage exam blueprint';
    $heroCopy = $isEditMode
        ? 'Refine the exam setup and its subject-wise full marks matrix in a polished single-screen workflow.'
        : 'Create, review, and maintain subject-wise exam blueprints with a premium management experience.';
@endphp

@include('front.pages.exam._theme')

<div class="exam-ui">
    <div class="exam-hero-card">
        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start position-relative">
            <div class="flex-grow-1">
                <span class="exam-kicker">
                    <i class="fa-solid fa-graduation-cap"></i>
                    Exams Module
                </span>
                <h2>{{ $heroTitle }}</h2>
                <p>{{ $heroCopy }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2 exam-hero-actions">
                <a href="#exam-builder" class="btn btn-light btn-sm">
                    <i class="fa-solid fa-pen-ruler me-1"></i> Go to form
                </a>
                <a href="#exam-register" class="btn btn-outline-light btn-sm">
                    <i class="fa-solid fa-table-list me-1"></i> View register
                </a>
            </div>
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
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="exam-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="exam-stat-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="exam-stat-label">Total Exams</div>
                        <div class="exam-stat-value">{{ number_format((int) $totalExams) }}</div>
                        <div class="exam-stat-mini">All blueprint records</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="exam-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="exam-stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="exam-stat-label">Active Exams</div>
                        <div class="exam-stat-value exam-stat-positive">{{ number_format((int) $activeExams) }}</div>
                        <div class="exam-stat-mini">Visible in the module</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="exam-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="exam-stat-icon">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <div class="exam-stat-label">Blocked Exams</div>
                        <div class="exam-stat-value exam-stat-warning">{{ number_format((int) $blockedExams) }}</div>
                        <div class="exam-stat-mini">Temporarily hidden</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="exam-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="exam-stat-icon">
                        <i class="fa-solid fa-diagram-project"></i>
                    </div>
                    <div>
                        <div class="exam-stat-label">Unit/Class Mappings</div>
                        <div class="exam-stat-value">{{ number_format((int) $mappingCount) }}</div>
                        <div class="exam-stat-mini">Blueprint rows configured</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card exam-card mb-4" id="exam-builder">
        <div class="card-header exam-card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5>{{ $action }} {{ $module['title'] }}</h5>
                    <div class="exam-card-subtitle">Create or refine the exam blueprint and its class subject marks matrix.</div>
                </div>
                <span class="exam-section-chip">
                    <i class="fa-solid fa-sparkles"></i>
                    {{ $isEditMode ? 'Editing mode' : 'Setup mode' }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="exam-note">
                <i class="fa-solid fa-circle-info mt-1"></i>
                <div>
                    <strong>Blueprint guidance</strong>
                    <div class="mt-1">Each row pairs one unit, class, and assigned subject with a full marks value. Duplicate combinations are blocked automatically and used by the marks entry screen.</div>
                </div>
            </div>
            @include('front.pages.exam._form')
        </div>
    </div>

    <div class="card exam-card" id="exam-register">
        <div class="card-header exam-card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5>Exam Register</h5>
                    <div class="exam-card-subtitle">Review all subject-wise exam blueprints from one place.</div>
                </div>
                <span class="exam-section-chip">
                    <i class="fa-solid fa-table-list"></i>
                    {{ number_format((int) $totalExams) }} records
                </span>
            </div>
        </div>
        <div class="card-body">
            @include('front.pages.exam._table')
        </div>
    </div>
</div>
