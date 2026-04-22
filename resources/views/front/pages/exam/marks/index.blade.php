@extends('front.layouts.afterlogin')
@section('content')
@php
    $controllerRoute = $module['controller_route'];
    $selectedUnitId = (string) old('unit_id', $selected_unit_id ?? '');
    $selectedBranchId = (string) old('branch_id', $selected_branch_id ?? '');
    $selectedClassId = (string) old('class_id', $selected_class_id ?? '');
    $selectedSessionId = (string) old('session_id', $selected_session_id ?? '');
    $selectedExamIds = old('exam_ids', $selected_exam_ids ?? []);

    if (!is_array($selectedExamIds)) {
        $selectedExamIds = (($selectedExamIds !== null && $selectedExamIds !== '') ? [$selectedExamIds] : []);
    }

    $selectedExamIds = array_map('strval', $selectedExamIds);
    $hasGeneratedResults = (!empty($generated) && !empty($exam_sections));
    $selectedExamNames = $selected_exam_names ?? [];
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.css">
<script src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Sora:wght@400;500;600;700&display=swap');

    .exam-marks-page {
        --exam-ink: #13263a;
        --exam-ink-soft: #4b6177;
        --exam-bg: #f5f8fc;
        --exam-surface: #ffffff;
        --exam-line: #d9e3ee;
        --exam-primary: #143b5f;
        --exam-primary-2: #1b5c7d;
        --exam-accent: #1a8d76;
        --exam-warm: #c79d4b;
        --exam-shadow: 0 16px 36px rgba(17, 34, 52, 0.08);
        font-family: 'Sora', sans-serif;
        color: var(--exam-ink);
    }

    .exam-marks-page a {
        text-decoration: none;
    }

    .exam-marks-hero {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        padding: 24px 26px;
        background: linear-gradient(135deg, #10253a 0%, #183d5f 50%, #0f6b67 100%);
        color: #ffffff;
        box-shadow: 0 18px 42px rgba(16, 37, 58, 0.24);
        margin-bottom: 18px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .exam-marks-hero::before,
    .exam-marks-hero::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        filter: blur(8px);
        pointer-events: none;
    }

    .exam-marks-hero::before {
        width: 180px;
        height: 180px;
        right: -40px;
        top: -30px;
    }

    .exam-marks-hero::after {
        width: 120px;
        height: 120px;
        right: 110px;
        bottom: -40px;
        opacity: 0.5;
    }

    .exam-marks-eyebrow {
        margin: 0 0 8px;
        font-size: 11px;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        opacity: 0.82;
    }

    .exam-marks-hero h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        line-height: 1.1;
        margin: 0 0 10px;
    }

    .exam-marks-hero p {
        margin: 0;
        max-width: 860px;
        font-size: 0.95rem;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.9);
    }

    .exam-marks-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .exam-hero-btn {
        border: 0;
        border-radius: 10px;
        min-height: 40px;
        padding: 0 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        letter-spacing: 0.2px;
    }

    .exam-hero-btn.btn-light {
        color: #10253a;
        background: #ffffff;
    }

    .exam-hero-btn.btn-light:hover {
        background: #ecf4fb;
        color: #10253a;
    }

    .exam-hero-btn.btn-outline-light {
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: #ffffff;
        background: rgba(255, 255, 255, 0.08);
    }

    .exam-hero-btn.btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
    }

    .exam-marks-card {
        border: 1px solid var(--exam-line);
        border-radius: 16px;
        background: var(--exam-surface);
        box-shadow: var(--exam-shadow);
    }

    .exam-marks-card .card-body {
        padding: 18px;
    }

    .exam-marks-section-title {
        margin: 0 0 14px;
        color: var(--exam-ink);
        font-size: 0.9rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .exam-form-note {
        border-radius: 12px;
        border: 1px solid #cae3fb;
        background: linear-gradient(135deg, #eff8ff 0%, #e9fbf7 100%);
        color: #17405e;
        font-size: 0.9rem;
        padding: 11px 14px;
        margin-top: 6px;
    }

    .exam-form-note strong {
        color: #10253a;
    }

    .exam-marks-label {
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--exam-ink-soft);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 6px;
        display: inline-block;
    }

    .exam-marks-page .form-select,
    .exam-marks-page .form-control {
        border-color: #d5deea;
        border-radius: 10px;
        min-height: 40px;
    }

    .exam-marks-page .form-select:focus,
    .exam-marks-page .form-control:focus {
        border-color: #7aa7ca;
        box-shadow: 0 0 0 .2rem rgba(31, 88, 130, 0.12);
    }

    .exam-select-control {
        min-height: 40px;
    }

    .choices__inner {
        min-height: 40px !important;
        border-radius: 10px !important;
        border-color: #d5deea !important;
        background: #ffffff !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
    }

    .choices__list--multiple .choices__item {
        background-color: #1a8d76;
        border: 1px solid #1a8d76;
    }

    .choices__list--dropdown,
    .choices__list[aria-expanded] {
        z-index: 12;
    }

    .exam-marks-helper {
        margin-top: 10px;
        font-size: 0.85rem;
        color: #5b7087;
    }

    .exam-marks-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .exam-summary-card {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #dbe5f0;
        border-radius: 14px;
        padding: 14px 15px;
        min-height: 100%;
        box-shadow: 0 10px 22px rgba(17, 34, 52, 0.05);
    }

    .exam-summary-label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: #5b7087;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    .exam-summary-value {
        margin: 0;
        color: var(--exam-ink);
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        word-break: break-word;
    }

    .exam-summary-value small {
        display: block;
        margin-top: 2px;
        color: #5b7087;
        font-size: 0.76rem;
        font-weight: 600;
    }

    .exam-marks-chip-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .exam-marks-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border-radius: 999px;
        border: 1px solid #cfe0ef;
        background: #f6fbff;
        color: #143b5f;
        padding: 7px 12px;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .exam-results-card {
        margin-top: 18px;
    }

    .exam-results-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        padding: 18px 18px 0;
    }

    .exam-results-head h3 {
        margin: 0;
        color: var(--exam-ink);
        font-size: 1.15rem;
        font-weight: 800;
    }

    .exam-results-head p {
        margin: 4px 0 0;
        color: #5a7086;
        font-size: 0.88rem;
    }

    .exam-results-subnote {
        border: 1px solid #cce0f2;
        background: linear-gradient(135deg, #edf7ff 0%, #eefbf8 100%);
        color: #17405e;
        border-radius: 12px;
        padding: 11px 14px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .marks-tabs-wrap {
        padding: 16px 18px 0;
    }

    .marks-tabs {
        display: flex;
        flex-wrap: nowrap;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: thin;
    }

    .marks-tabs .nav-item {
        flex: 0 0 auto;
    }

    .marks-tab-btn {
        min-width: 250px;
        text-align: left;
        border-radius: 14px !important;
        border: 1px solid #dbe5f0 !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        color: var(--exam-ink) !important;
        box-shadow: 0 8px 18px rgba(16, 37, 58, 0.05);
        padding: 12px 14px;
    }

    .marks-tab-btn.active {
        background: linear-gradient(135deg, #143b5f 0%, #1a8d76 100%) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 14px 28px rgba(20, 59, 95, 0.2);
    }

    .marks-tab-name {
        display: block;
        font-size: 0.95rem;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .marks-tab-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .marks-tab-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 9px;
        font-size: 0.74rem;
        font-weight: 700;
        background: rgba(20, 59, 95, 0.08);
        color: #143b5f;
    }

    .marks-tab-btn.active .marks-tab-chip {
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
    }

    .marks-tab-chip.success {
        background: rgba(26, 141, 118, 0.12);
        color: #11715f;
    }

    .marks-tab-chip.danger {
        background: rgba(191, 52, 69, 0.12);
        color: #b43747;
    }

    .marks-tab-btn.active .marks-tab-chip.success,
    .marks-tab-btn.active .marks-tab-chip.danger {
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
    }

    .marks-tab-content {
        padding: 16px 18px 18px;
    }

    .exam-pane-summary {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        background: linear-gradient(135deg, #f8fbff 0%, #f1fbf9 100%);
        border: 1px solid #dce8f2;
        border-radius: 14px;
        padding: 14px 15px;
        margin-bottom: 14px;
    }

    .exam-pane-title {
        color: var(--exam-ink);
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
    }

    .exam-pane-title small {
        display: block;
        margin-top: 3px;
        color: #5b7087;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .exam-pane-metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .marks-info-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border-radius: 999px;
        border: 1px solid #cfe0ef;
        background: #ffffff;
        color: #143b5f;
        padding: 7px 11px;
        font-size: 0.79rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .marks-table-shell {
        border-radius: 14px;
        border: 1px solid #dbe5f0;
        overflow-x: auto;
        overflow-y: hidden;
        background: #ffffff;
    }

    .marks-table {
        width: 100%;
        margin-bottom: 0;
        min-width: 1180px;
    }

    .marks-table thead th {
        background: linear-gradient(135deg, #12324c 0%, #163b57 100%);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.08);
        font-size: 0.74rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
        padding: 10px 8px;
        vertical-align: middle;
    }

    .marks-table tbody td {
        border-color: #e5edf5;
        vertical-align: middle;
        padding: 10px 8px;
        color: #203549;
        font-size: 0.88rem;
    }

    .marks-table tbody tr:hover td {
        background: #f8fbff;
    }

    .exam-mark-row[data-state="saved"] td {
        background: #f8fff9;
    }

    .exam-mark-row[data-state="dirty"] td {
        background: #fff9ef;
    }

    .exam-mark-row[data-state="empty"] td {
        background: #ffffff;
    }

    .student-photo-wrap {
        display: flex;
        justify-content: center;
    }

    .student-photo {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border-radius: 14px;
        border: 2px solid #d9e5f0;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(17, 34, 52, 0.08);
    }

    .student-meta {
        min-width: 210px;
    }

    .student-name {
        display: block;
        color: #12283a;
        font-weight: 800;
        font-size: 0.92rem;
        line-height: 1.35;
    }

    .student-serial {
        display: block;
        margin-top: 3px;
        color: #1b5c7d;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .student-light {
        color: #587087;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.35;
    }

    .marks-full-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        border-radius: 999px;
        background: #eef5ff;
        border: 1px solid #d6e4f4;
        color: #143b5f;
        padding: 6px 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .marks-obtain-input,
    .marks-percentage-input {
        text-align: center;
        font-weight: 700;
        border-radius: 10px !important;
    }

    .marks-percentage-group .input-group-text {
        border-radius: 0 10px 10px 0;
        background: #f0f6fb;
        color: #17344d;
        font-weight: 700;
        border-color: #d5deea;
    }

    .marks-save-btn {
        min-width: 108px;
        border-radius: 10px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .marks-row-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 92px;
        font-size: 0.72rem;
        padding: 5px 8px;
        border-radius: 999px;
        font-weight: 700;
    }

    .marks-row-status.bg-success {
        background: #daf6e6 !important;
        color: #0f6b3f !important;
        border: 1px solid #bde6cb;
    }

    .marks-row-status.bg-warning {
        background: #fff1cd !important;
        color: #8a5b00 !important;
        border: 1px solid #f3d79d;
    }

    .marks-row-status.bg-secondary {
        background: #edf2f7 !important;
        color: #4b647c !important;
        border: 1px solid #d7e0ea;
    }

    .marks-table .input-group {
        min-width: 120px;
    }

    .exam-marks-empty {
        text-align: center;
        padding: 26px 20px;
        border-radius: 14px;
        border: 1px dashed #c9d7e5;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .exam-marks-empty .icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        margin: 0 auto 14px;
        background: linear-gradient(135deg, #eaf4ff 0%, #e8faf7 100%);
        color: #1a5e7c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        box-shadow: inset 0 0 0 1px rgba(26, 141, 118, 0.08);
    }

    .exam-marks-empty h4 {
        margin: 0 0 8px;
        color: var(--exam-ink);
        font-weight: 800;
        font-size: 1.05rem;
    }

    .exam-marks-empty p {
        margin: 0 auto;
        max-width: 640px;
        color: #587087;
        font-size: 0.92rem;
        line-height: 1.6;
    }

    .exam-marks-guides {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .exam-guide-card {
        background: #ffffff;
        border: 1px solid #dbe5f0;
        border-radius: 14px;
        padding: 14px;
        text-align: left;
    }

    .exam-guide-card .step {
        color: #1b5c7d;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 6px;
    }

    .exam-guide-card h5 {
        margin: 0 0 6px;
        color: #12283a;
        font-size: 0.92rem;
        font-weight: 800;
    }

    .exam-guide-card p {
        margin: 0;
        color: #5b7087;
        font-size: 0.84rem;
        line-height: 1.5;
    }

    .exam-marks-toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 480px;
        display: none;
        border-radius: 12px;
        padding: 12px 14px;
        color: #ffffff;
        font-size: 0.92rem;
        font-weight: 700;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.18);
    }

    .exam-marks-toast.success {
        background: #15824b;
    }

    .exam-marks-toast.error {
        background: #b73949;
    }

    .exam-mark-row.is-flash td {
        animation: examFlash 1.2s ease;
    }

    @keyframes examFlash {
        0% {
            background: #ecfff2;
        }
        60% {
            background: #ecfff2;
        }
        100% {
            background: inherit;
        }
    }

    @media (max-width: 991px) {
        .exam-marks-hero {
            padding: 20px;
        }

        .exam-marks-hero h2 {
            font-size: 1.7rem;
        }

        .marks-tab-btn {
            min-width: 220px;
        }
    }

    @media (max-width: 767px) {
        .exam-marks-card .card-body {
            padding: 14px;
        }

        .exam-results-head,
        .marks-tabs-wrap,
        .marks-tab-content {
            padding-left: 14px;
            padding-right: 14px;
        }

        .exam-pane-summary {
            padding: 12px;
        }

        .exam-marks-toast {
            left: 14px;
            right: 14px;
            min-width: auto;
            max-width: none;
        }
    }
</style>

<div class="exam-marks-page">
    <div class="exam-marks-hero">
        <div>
            <p class="exam-marks-eyebrow">Exam Marks Center</p>
            <h2>Student Wise Marks Entry</h2>
            <p>
                Generate a focused marks entry sheet by unit, branch, class, session, and one or more exams.
                Marks are saved inline without reloading the page, so the workflow stays fast and clean.
            </p>
        </div>
        <div class="exam-marks-hero-actions">
            @if($hasGeneratedResults)
                <a href="{{ url($controllerRoute) }}" class="exam-hero-btn btn-light">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filters
                </a>
            @endif
            <a href="{{ url('exam/list') }}" class="exam-hero-btn btn-outline-light">
                <i class="fa-solid fa-clipboard-list"></i> Exam Setup
            </a>
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

    <div class="exam-marks-card">
        <div class="card-body">
            <h6 class="exam-marks-section-title">Search and Generate</h6>
            <form method="POST" action="{{ route('exam.marks.index') }}" id="examMarksFilterForm">
                @csrf
                <div class="row g-3">
                    <div class="col-lg-2 col-md-6">
                        <label for="unit_id" class="exam-marks-label">Unit</label>
                        <select class="form-select form-select-sm exam-select-control" name="unit_id" id="unit_id" required>
                            <option value="">Select</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ ($selectedUnitId === (string) $unit->id ? 'selected' : '') }}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="branch_id" class="exam-marks-label">Branch</label>
                        <select class="form-select form-select-sm exam-select-control" name="branch_id" id="branch_id" required {{ ($selectedUnitId === '' ? 'disabled' : '') }}>
                            <option value="">Select</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" data-unit-id="{{ $branch->unit_id }}" {{ ($selectedBranchId === (string) $branch->id ? 'selected' : '') }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="class_id" class="exam-marks-label">Class</label>
                        <select class="form-select form-select-sm exam-select-control" name="class_id" id="class_id" required {{ ($selectedUnitId === '' ? 'disabled' : '') }}>
                            <option value="">Select</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" data-unit-id="{{ $class->unit_id }}" {{ ($selectedClassId === (string) $class->id ? 'selected' : '') }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="session_id" class="exam-marks-label">Session</label>
                        <select class="form-select form-select-sm exam-select-control" name="session_id" id="session_id" required>
                            <option value="">Select</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ ($selectedSessionId === (string) $session->id ? 'selected' : '') }}>{{ $session->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <label for="exam_ids" class="exam-marks-label">Exam(s)</label>
                        <select class="form-select form-select-sm exam-select-control exam-multi-select" name="exam_ids[]" id="exam_ids" multiple required>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ (in_array((string) $exam->id, $selectedExamIds, true) ? 'selected' : '') }}>{{ $exam->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" style="min-height:40px; border-radius:10px; font-weight:700; padding:0 16px;">
                                <i class="fa-solid fa-bolt"></i> Generate
                            </button>
                            @if($hasGeneratedResults)
                                <a href="{{ url($controllerRoute) }}" class="btn btn-outline-secondary" style="min-height:40px; border-radius:10px; font-weight:700; padding:0 16px;">
                                    <i class="fa-solid fa-rotate-right"></i> Clear
                                </a>
                            @endif
                        </div>
                        <div class="exam-form-note">
                            <strong>Tip:</strong> select a unit first to reveal the matching branch and class options. Session defaults to the current active academic session.
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($hasGeneratedResults)
        <div class="exam-marks-card exam-results-card">
            <div class="exam-results-head">
                <div>
                    <h3>Generated Marks Grid</h3>
                    <p>Every exam opens in its own tab so the entry flow stays clear, even when multiple exams are selected.</p>
                </div>
                <div class="exam-results-subnote">
                    <i class="fa-solid fa-circle-info"></i>
                    {{ $student_count }} active students found for the selected filters.
                </div>
            </div>

            <div class="card-body pt-3">
                <div class="exam-marks-summary-grid">
                    <div class="exam-summary-card">
                        <div class="exam-summary-label"><i class="fa-solid fa-sitemap"></i> Unit</div>
                        <p class="exam-summary-value">{{ ($selected_unit_name !== '' ? $selected_unit_name : '-') }}</p>
                    </div>
                    <div class="exam-summary-card">
                        <div class="exam-summary-label"><i class="fa-solid fa-building"></i> Branch</div>
                        <p class="exam-summary-value">{{ ($selected_branch_name !== '' ? $selected_branch_name : '-') }}</p>
                    </div>
                    <div class="exam-summary-card">
                        <div class="exam-summary-label"><i class="fa-solid fa-chalkboard-user"></i> Class</div>
                        <p class="exam-summary-value">{{ ($selected_class_name !== '' ? $selected_class_name : '-') }}</p>
                    </div>
                    <div class="exam-summary-card">
                        <div class="exam-summary-label"><i class="fa-solid fa-calendar-days"></i> Session</div>
                        <p class="exam-summary-value">{{ ($selected_session_name !== '' ? $selected_session_name : '-') }}</p>
                    </div>
                    <div class="exam-summary-card">
                        <div class="exam-summary-label"><i class="fa-solid fa-users"></i> Students</div>
                        <p class="exam-summary-value">{{ $student_count }}<small>Active student count for this filter set</small></p>
                    </div>
                    <div class="exam-summary-card">
                        <div class="exam-summary-label"><i class="fa-solid fa-file-lines"></i> Exams</div>
                        <p class="exam-summary-value">{{ count($selectedExamNames) }}<small>Selected exam count</small></p>
                    </div>
                </div>

                @if(count($selectedExamNames) > 0)
                    <div class="exam-marks-chip-row">
                        @foreach($selectedExamNames as $selectedExamName)
                            <span class="exam-marks-pill"><i class="fa-solid fa-tag"></i> {{ $selectedExamName }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($student_count > 0)
                <div class="marks-tabs-wrap">
                    <ul class="nav nav-pills marks-tabs" id="examMarksTabs" role="tablist">
                        @foreach($exam_sections as $section)
                            @php
                                $examId = $section['exam']->id;
                            @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link marks-tab-btn {{ ($loop->first ? 'active' : '') }}"
                                        id="exam-tab-{{ $examId }}"
                                        data-bs-toggle="pill"
                                        data-bs-target="#exam-pane-{{ $examId }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="exam-pane-{{ $examId }}"
                                        aria-selected="{{ ($loop->first ? 'true' : 'false') }}"
                                        data-exam-tab="{{ $examId }}">
                                    <span class="marks-tab-name">{{ $section['exam']->name }}</span>
                                    <span class="marks-tab-meta">
                                        <span class="marks-tab-chip">{{ $section['student_count'] }} students</span>
                                        <span class="marks-tab-chip success"><span data-tab-entered>{{ $section['entered_count'] }}</span> entered</span>
                                        <span class="marks-tab-chip danger"><span data-tab-pending>{{ $section['pending_count'] }}</span> pending</span>
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="tab-content marks-tab-content" id="examMarksTabContent">
                    @foreach($exam_sections as $section)
                        @php
                            $examId = $section['exam']->id;
                        @endphp
                        <div class="tab-pane fade {{ ($loop->first ? 'show active' : '') }}" id="exam-pane-{{ $examId }}" role="tabpanel" aria-labelledby="exam-tab-{{ $examId }}">
                            <div class="exam-pane-summary">
                                <div>
                                    <p class="exam-pane-title">
                                        {{ $section['exam']->name }}
                                        <small>Marks entry table for {{ $section['student_count'] }} active students</small>
                                    </p>
                                </div>
                                <div class="exam-pane-metrics">
                                    <span class="marks-info-chip"><i class="fa-solid fa-users"></i> Students: <strong data-exam-summary-students>{{ $section['student_count'] }}</strong></span>
                                    <span class="marks-info-chip"><i class="fa-solid fa-circle-check"></i> Entered: <strong data-exam-summary-entered>{{ $section['entered_count'] }}</strong></span>
                                    <span class="marks-info-chip"><i class="fa-solid fa-hourglass-half"></i> Pending: <strong data-exam-summary-pending>{{ $section['pending_count'] }}</strong></span>
                                    <span class="marks-info-chip"><i class="fa-solid fa-star"></i> Full Marks: <strong data-exam-summary-full>{{ $section['full_marks_label'] }}</strong></span>
                                </div>
                            </div>

                            <div class="marks-table-shell">
                                <table class="table table-bordered align-middle marks-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:60px;">#</th>
                                            <th class="text-center" style="width:220px;">Student</th>
                                            <th class="text-center" style="width:90px;">Photo</th>
                                            <th class="text-center" style="width:210px;">Father Name</th>
                                            <th class="text-center" style="width:140px;">Father Mobile</th>
                                            <th class="text-center" style="width:100px;">Full Marks</th>
                                            <th class="text-center" style="width:140px;">Obtain Marks</th>
                                            <th class="text-center" style="width:140px;">Marks %</th>
                                            <th class="text-center" style="width:150px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($section['rows'] as $row)
                                            @php
                                                $student = $row['student'];
                                                $studentName = (!empty(trim((string) $student->full_name)) && strcasecmp(trim((string) $student->full_name), 'No Name') !== 0) ? $student->full_name : '-';
                                                $fatherName = (!empty(trim((string) $student->father_name)) && strcasecmp(trim((string) $student->father_name), 'No Name') !== 0) ? $student->father_name : '-';
                                                $fatherMobile = (!empty(trim((string) $student->father_mobile)) ? $student->father_mobile : '-');
                                                $photoUrl = (($student->photo != '') ? (config('constants.app_url') . config('constants.uploads_url_path') . $student->photo) : config('constants.no_image_avatar'));
                                                $savedState = ($row['has_value'] ? 'saved' : 'empty');
                                                $rowButtonText = ($row['has_value'] ? 'Update' : 'Save');
                                                $rowStatusText = ($row['has_value'] ? 'Saved' : 'Pending');
                                                $rowStatusClass = ($row['has_value'] ? 'bg-success' : 'bg-secondary');
                                            @endphp
                                            <tr class="exam-mark-row"
                                                data-exam-id="{{ $examId }}"
                                                data-student-id="{{ $student->id }}"
                                                data-unit-id="{{ $selectedUnitId }}"
                                                data-branch-id="{{ $selectedBranchId }}"
                                                data-class-id="{{ $selectedClassId }}"
                                                data-session-id="{{ $selectedSessionId }}"
                                                data-full-marks="{{ $row['full_marks'] }}"
                                                data-original-marks="{{ $row['obtain_marks'] }}"
                                                data-state="{{ $savedState }}">
                                                <td class="fw-bold text-center">{{ $loop->iteration }}</td>
                                                <td class="student-meta">
                                                    <span class="student-name">{{ $studentName }}</span>
                                                    <span class="student-serial">{{ $student->student_id_serial }}</span>
                                                </td>
                                                <td>
                                                    <div class="student-photo-wrap">
                                                        <img src="{{ $photoUrl }}" alt="{{ $studentName }}" class="student-photo">
                                                    </div>
                                                </td>
                                                <td class="student-light">{{ $fatherName }}</td>
                                                <td class="student-light">{{ $fatherMobile }}</td>
                                                <td class="text-center">
                                                    <span class="marks-full-chip">{{ $row['full_marks_label'] }}</span>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control form-control-sm marks-obtain-input"
                                                           value="{{ $row['obtain_marks'] }}"
                                                           inputmode="numeric"
                                                           autocomplete="off"
                                                           pattern="[0-9]*"
                                                           maxlength="6"
                                                           placeholder="0">
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm marks-percentage-group">
                                                        <input type="text" class="form-control form-control-sm marks-percentage-input" value="{{ $row['percentage'] }}" readonly placeholder="0.00">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-success btn-sm marks-save-btn exam-mark-save-btn">
                                                        <i class="fa-solid fa-floppy-disk"></i> {{ $rowButtonText }}
                                                    </button>
                                                    <div class="mt-2">
                                                        <span class="marks-row-status {{ $rowStatusClass }}">{{ $rowStatusText }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card-body pt-0">
                    <div class="exam-marks-empty">
                        <div class="icon"><i class="fa-solid fa-user-graduate"></i></div>
                        <h4>No active students found</h4>
                        <p>
                            The selected unit, branch, class, session, and exam combination did not return any active students.
                            Adjust the filters and generate the marks grid again.
                        </p>
                        <div class="exam-marks-guides">
                            <div class="exam-guide-card">
                                <div class="step">Step 1</div>
                                <h5>Pick the unit</h5>
                                <p>Branch and class options will automatically narrow down to match the selected unit.</p>
                            </div>
                            <div class="exam-guide-card">
                                <div class="step">Step 2</div>
                                <h5>Choose the session and exam(s)</h5>
                                <p>Select one or more exams, then generate the grid to load all active students.</p>
                            </div>
                            <div class="exam-guide-card">
                                <div class="step">Step 3</div>
                                <h5>Save inline</h5>
                                <p>Enter integer marks, let percentage calculate automatically, and save each row without page reloads.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @elseif($generated)
        <div class="exam-marks-card exam-results-card">
            <div class="card-body">
                <div class="exam-marks-empty">
                    <div class="icon"><i class="fa-solid fa-user-graduate"></i></div>
                    <h4>No active students found</h4>
                    <p>
                        The selected filters did not return any active students. Please adjust the unit, branch, class, or session and generate again.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="exam-marks-card exam-results-card">
            <div class="card-body">
                <div class="exam-marks-empty">
                    <div class="icon"><i class="fa-solid fa-clipboard-check"></i></div>
                    <h4>Ready to generate</h4>
                    <p>
                        Select the required filters above, choose one or more exams, and generate the marks grid.
                        Once generated, each student row can be updated inline with instant save feedback.
                    </p>
                    <div class="exam-marks-guides">
                        <div class="exam-guide-card">
                            <div class="step">1. Filter</div>
                            <h5>Choose unit, branch, class, and session</h5>
                            <p>The branch and class dropdowns are unit aware for faster selection.</p>
                        </div>
                        <div class="exam-guide-card">
                            <div class="step">2. Generate</div>
                            <h5>Open the exam tabs</h5>
                            <p>Each selected exam gets its own clean tab with student-wise marks inputs.</p>
                        </div>
                        <div class="exam-guide-card">
                            <div class="step">3. Save</div>
                            <h5>Type integer marks and save instantly</h5>
                            <p>The percentage field updates automatically and the row saves via AJAX without reloading.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div id="examMarksToast" class="exam-marks-toast" aria-live="polite" aria-atomic="true"></div>
@endsection

@section('scripts')
<script>
    $(function () {
        const csrfToken = @json(csrf_token());
        const selectedUnitId = @json($selectedUnitId);

        function showExamToast(message, type) {
            const $toast = $('#examMarksToast');
            $toast.removeClass('success error').addClass(type || 'success');
            $toast.text(message || '');
            $toast.stop(true, true).fadeIn(180);

            if (window.examMarksToastTimer) {
                clearTimeout(window.examMarksToastTimer);
            }

            window.examMarksToastTimer = setTimeout(function () {
                $toast.fadeOut(250);
            }, 3800);
        }

        function refreshDependentSelect($select, unitId) {
            const currentValue = String($select.val() || '');
            let hasVisibleSelection = false;

            $select.find('option[data-unit-id]').each(function () {
                const $option = $(this);
                const optionUnitId = String($option.data('unit-id') || '');
                const matches = (unitId !== '' && optionUnitId === String(unitId));
                $option.prop('hidden', !matches);
                $option.prop('disabled', !matches);

                if (matches && String($option.val()) === currentValue) {
                    hasVisibleSelection = true;
                }
            });

            if (unitId === '' || !hasVisibleSelection) {
                $select.val('');
            }

            $select.prop('disabled', unitId === '');
        }

        function getRowCurrentMarks($row) {
            return $.trim($row.find('.marks-obtain-input').val() || '');
        }

        function refreshRowPercentage($row) {
            const fullMarks = parseFloat($row.data('full-marks')) || 0;
            const currentValue = getRowCurrentMarks($row);
            const $percentageInput = $row.find('.marks-percentage-input');

            if (currentValue === '') {
                $percentageInput.val('');
                return;
            }

            const marksValue = parseInt(currentValue, 10);
            if (isNaN(marksValue)) {
                $percentageInput.val('');
                return;
            }

            const percentage = (fullMarks > 0)
                ? ((marksValue / fullMarks) * 100).toFixed(2)
                : '0.00';

            $percentageInput.val(percentage);
        }

        function refreshRowState($row) {
            const currentValue = getRowCurrentMarks($row);
            const originalValue = String($row.data('original-marks') || '');
            const $status = $row.find('.marks-row-status');
            let state = 'empty';
            let label = 'Pending';
            let badgeClass = 'bg-secondary';

            if (currentValue === '') {
                if (originalValue !== '') {
                    state = 'dirty';
                    label = 'Needs Save';
                    badgeClass = 'bg-warning';
                }
            } else if (currentValue === originalValue) {
                state = 'saved';
                label = 'Saved';
                badgeClass = 'bg-success';
            } else {
                state = 'dirty';
                label = (originalValue === '' ? 'Ready' : 'Unsaved');
                badgeClass = 'bg-warning';
            }

            $row.attr('data-state', state);
            $status.removeClass('bg-success bg-warning bg-secondary').addClass(badgeClass).text(label);
        }

        function updateExamCounts(examId, counts) {
            const $pane = $('#exam-pane-' + examId);
            const $tabButton = $('[data-exam-tab="' + examId + '"]');

            if ($pane.length) {
                $pane.find('[data-exam-summary-students]').text(counts.student_count);
                $pane.find('[data-exam-summary-entered]').text(counts.entered_count);
                $pane.find('[data-exam-summary-pending]').text(counts.pending_count);
            }

            if ($tabButton.length) {
                $tabButton.find('[data-tab-entered]').text(counts.entered_count);
                $tabButton.find('[data-tab-pending]').text(counts.pending_count);
            }
        }

        function applySavedRowState($row, response) {
            $row.attr('data-state', 'saved');
            $row.data('original-marks', response.data.obtain_marks);
            $row.attr('data-original-marks', response.data.obtain_marks);
            $row.find('.marks-obtain-input').val(response.data.obtain_marks);
            $row.find('.marks-percentage-input').val(response.data.marks_percentage);
            $row.find('.marks-row-status')
                .removeClass('bg-success bg-warning bg-secondary')
                .addClass('bg-success')
                .text('Saved');
            $row.find('.marks-save-btn')
                .html('<i class="fa-solid fa-floppy-disk"></i> Update')
                .prop('disabled', false);
            $row.addClass('is-flash');

            setTimeout(function () {
                $row.removeClass('is-flash');
            }, 1200);
        }

        refreshDependentSelect($('#branch_id'), selectedUnitId);
        refreshDependentSelect($('#class_id'), selectedUnitId);

        $('#unit_id').on('change', function () {
            const unitId = $(this).val();
            refreshDependentSelect($('#branch_id'), unitId);
            refreshDependentSelect($('#class_id'), unitId);
        });

        if ($('#exam_ids').length) {
            new Choices('#exam_ids', {
                removeItemButton: true,
                searchEnabled: true,
                searchResultLimit: 12,
                renderChoiceLimit: 12,
                closeDropdownOnSelect: false,
                shouldSort: false,
                itemSelectText: '',
                maxItemCount: -1
            });
        }

        $('.exam-mark-row').each(function () {
            const $row = $(this);
            refreshRowPercentage($row);
            refreshRowState($row);
        });

        $(document).on('input', '.marks-obtain-input', function () {
            const $input = $(this);
            const sanitizedValue = $input.val().replace(/[^0-9]/g, '');
            if ($input.val() !== sanitizedValue) {
                $input.val(sanitizedValue);
            }

            const $row = $input.closest('.exam-mark-row');
            refreshRowPercentage($row);
            refreshRowState($row);
        });

        $(document).on('keydown', '.marks-obtain-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('.exam-mark-row').find('.exam-mark-save-btn').trigger('click');
            }
        });

        $(document).on('click', '.exam-mark-save-btn', function () {
            const $btn = $(this);
            const $row = $btn.closest('.exam-mark-row');
            const obtainMarksText = getRowCurrentMarks($row);
            const obtainMarks = parseInt(obtainMarksText, 10);
            const fullMarks = parseFloat($row.data('full-marks')) || 0;

            if (obtainMarksText === '' || isNaN(obtainMarks) || obtainMarks < 0) {
                showExamToast('Please enter a valid integer mark.', 'error');
                return;
            }

            if (obtainMarks > fullMarks) {
                showExamToast('Obtain marks cannot be greater than full marks.', 'error');
                return;
            }

            $.ajax({
                url: "{{ route('exam.marks.save') }}",
                method: "POST",
                dataType: "json",
                data: {
                    _token: csrfToken,
                    student_id: $row.data('student-id'),
                    exam_id: $row.data('exam-id'),
                    unit_id: $row.data('unit-id'),
                    branch_id: $row.data('branch-id'),
                    class_id: $row.data('class-id'),
                    session_id: $row.data('session-id'),
                    obtain_marks: obtainMarks
                },
                beforeSend: function () {
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving');
                },
                success: function (response) {
                    if (response && response.data) {
                        applySavedRowState($row, response);
                        updateExamCounts($row.data('exam-id'), response.data);
                        showExamToast(response.message, 'success');
                    } else {
                        showExamToast('Saved successfully.', 'success');
                    }
                },
                error: function (xhr) {
                    let message = 'Something went wrong. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showExamToast(message, 'error');
                },
                complete: function () {
                    const originalValue = String($row.data('original-marks') || '');
                    const buttonLabel = (originalValue !== '' ? 'Update' : 'Save');
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i> ' + buttonLabel);
                    refreshRowState($row);
                }
            });
        });
    });
</script>
@endsection
