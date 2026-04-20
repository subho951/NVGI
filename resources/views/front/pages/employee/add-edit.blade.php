@extends('front.layouts.afterlogin')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap');

    .employee-shell {
        --employee-ink: #10263d;
        --employee-muted: #66788b;
        --employee-border: #dbe6f1;
        --employee-surface: #ffffff;
        --employee-bg: #f5f8fc;
        --employee-navy: #14283e;
        --employee-blue: #1d5f8b;
        --employee-teal: #1d7f7d;
        --employee-gold: #d9b66a;
        font-family: 'Manrope', sans-serif;
        color: var(--employee-ink);
    }

    .employee-shell a {
        text-decoration: none;
    }

    .employee-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 28px;
        color: #fff;
        background: linear-gradient(135deg, #10263d 0%, #173b5d 54%, #1f6a78 100%);
        box-shadow: 0 24px 50px rgba(13, 30, 50, 0.22);
    }

    .employee-hero::before,
    .employee-hero::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .employee-hero::before {
        width: 220px;
        height: 220px;
        right: -70px;
        top: -90px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 72%);
    }

    .employee-hero::after {
        width: 180px;
        height: 180px;
        left: -60px;
        bottom: -92px;
        background: radial-gradient(circle, rgba(217, 182, 106, 0.22) 0%, rgba(217, 182, 106, 0) 72%);
    }

    .employee-hero-content {
        position: relative;
        z-index: 1;
    }

    .employee-hero-kicker {
        margin-bottom: 10px;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.72);
    }

    .employee-hero h2 {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: 2.15rem;
        line-height: 1.08;
    }

    .employee-hero p {
        margin: 12px 0 0;
        max-width: 720px;
        font-size: 0.98rem;
        color: rgba(255, 255, 255, 0.88);
    }

    .employee-hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .employee-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 13px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        backdrop-filter: blur(6px);
    }

    .btn-employee-primary {
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #d9b66a 0%, #f0d08b 100%);
        color: #10263d;
        font-weight: 800;
        box-shadow: 0 14px 28px rgba(16, 38, 61, 0.18);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .btn-employee-primary:hover {
        color: #0c2033;
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(16, 38, 61, 0.22);
    }

    .btn-employee-secondary {
        border: 1px solid #d5dee8;
        border-radius: 12px;
        background: #fff;
        color: var(--employee-ink);
        font-weight: 700;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .btn-employee-secondary:hover {
        background: #f6f9fc;
        color: var(--employee-ink);
        transform: translateY(-1px);
    }

    .employee-form-card,
    .employee-summary-card {
        overflow: hidden;
        border: 1px solid var(--employee-border);
        border-radius: 22px;
        background: var(--employee-surface);
        box-shadow: 0 16px 35px rgba(18, 33, 53, 0.08);
    }

    .employee-form-card .card-body {
        padding: 28px;
    }

    .employee-form-section {
        margin-bottom: 1.35rem;
        padding-bottom: 1.35rem;
        border-bottom: 1px solid #e7edf5;
    }

    .employee-form-section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: 0;
    }

    .employee-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1rem;
        color: var(--employee-ink);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .employee-section-title span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #eef4fa;
        color: #234766;
    }

    .employee-form-card .form-label {
        margin-bottom: 6px;
        color: #516476;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .employee-form-card .form-control,
    .employee-form-card .form-select {
        min-height: 48px;
        border: 1px solid #d9e3ee;
        border-radius: 12px;
        background-color: #fff;
        box-shadow: none;
    }

    .employee-form-card textarea.form-control {
        min-height: 130px;
    }

    .employee-form-card .form-control:focus,
    .employee-form-card .form-select:focus {
        border-color: #6e99c1;
        box-shadow: 0 0 0 0.2rem rgba(54, 106, 160, 0.12);
    }

    .employee-readonly {
        background: linear-gradient(180deg, #f3f7fb 0%, #eef4fa 100%);
        font-weight: 800;
        letter-spacing: 0.06em;
    }

    .employee-hint {
        margin-top: 8px;
        color: #72879a;
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .employee-summary-card {
        position: sticky;
        top: 18px;
    }

    .employee-summary-card .card-body {
        padding: 22px;
    }

    .employee-summary-kicker {
        margin-bottom: 6px;
        color: #6d8094;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
    }

    .employee-summary-title {
        margin: 0;
        color: var(--employee-ink);
        font-size: 1.18rem;
        font-weight: 800;
    }

    .employee-summary-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eef4fa;
        color: #234766;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .employee-preview-frame {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        border: 1px solid var(--employee-border);
        border-radius: 24px;
        background: linear-gradient(180deg, #f7fbff 0%, #edf3f8 100%);
    }

    .employee-preview-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .employee-upload {
        padding: 14px;
        border: 1px dashed #bfd0e2;
        border-radius: 18px;
        background: #f8fbfe;
    }

    .employee-summary-grid {
        border-top: 1px solid #eef3f8;
        margin-top: 18px;
    }

    .employee-summary-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 0;
        border-top: 1px solid #eef3f8;
    }

    .employee-summary-item:first-child {
        border-top: 0;
    }

    .employee-summary-label {
        color: #72879a;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .employee-summary-value {
        color: var(--employee-ink);
        font-size: 0.92rem;
        font-weight: 700;
        text-align: right;
    }

    .employee-branch-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .employee-branch-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border: 1px solid #d8e4ef;
        border-radius: 999px;
        background: #eef4fa;
        color: #234766;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .employee-summary-note {
        display: flex;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 16px;
        background: #f3f7fb;
        color: #516476;
        font-size: 0.84rem;
        line-height: 1.5;
    }

    .employee-actions .btn {
        min-height: 48px;
        border-radius: 12px;
        font-weight: 800;
    }

    .choices__inner {
        min-height: 48px;
        padding: 8px 12px;
        border: 1px solid #d9e3ee;
        border-radius: 12px !important;
        background: #fff;
    }

    .choices__list--multiple .choices__item {
        border-color: #173f63;
        background-color: #173f63;
    }

    .choices[data-type*=select-multiple] .choices__button {
        border-left-color: rgba(255, 255, 255, 0.25);
    }

    .choices.is-focused .choices__inner,
    .choices.is-open .choices__inner {
        border-color: #6e99c1;
    }

    @media (max-width: 991px) {
        .employee-hero {
            padding: 22px;
        }

        .employee-hero h2 {
            font-size: 1.8rem;
        }

        .employee-summary-card {
            position: static;
        }
    }

    @media (max-width: 767px) {
        .employee-hero {
            padding: 18px;
        }

        .employee-hero h2 {
            font-size: 1.55rem;
        }

        .employee-form-card .card-body {
            padding: 18px;
        }

        .employee-summary-card .card-body {
            padding: 18px;
        }
    }
</style>
@endsection

@section('content')
<?php

use App\Models\Unit;

$controllerRoute = $module['controller_route'];
$isEdit = isset($row) && is_object($row) && isset($row->id);
$employeeNo = old('employee_no', (($isEdit) ? $row->employee_no : ($employee_no_preview ?? '')));
$firstName = old('first_name', (($isEdit) ? $row->first_name : ''));
$middleName = old('middle_name', (($isEdit) ? $row->middle_name : ''));
$lastName = old('last_name', (($isEdit) ? $row->last_name : ''));
$email = old('email', (($isEdit) ? $row->email : ''));
$phone = old('phone', (($isEdit) ? $row->phone : ''));
$address = old('address', (($isEdit) ? $row->address : ''));
$pincode = old('pincode', (($isEdit) ? $row->pincode : ''));
$dob = old('dob', (($isEdit) ? $row->dob : ''));
$doj = old('doj', (($isEdit) ? $row->doj : ''));
$age = old('age', (($isEdit) ? $row->age : ''));
$salary = old('salary', (($isEdit) ? $row->salary : ''));
$gender = old('gender', (($isEdit) ? $row->gender : ''));
$image = (($isEdit) ? $row->image : '');
$selectedBranches = old('branch', (($isEdit && !empty($row->branch)) ? json_decode((string)$row->branch, true) : []));

if (!is_array($selectedBranches)) {
    $selectedBranches = [];
}

$selectedBranches = array_map('strval', $selectedBranches);
$unitNames = Unit::where('status', '!=', 3)->pluck('name', 'id')->toArray();
$branchLookup = [];

if (!empty($branchOptions)) {
    foreach ($branchOptions as $branchOption) {
        $unitLabel = $unitNames[$branchOption->unit_id] ?? '';
        $branchLookup[(string)$branchOption->id] = trim($branchOption->name . ($unitLabel !== '' ? ' (' . $unitLabel . ')' : ''));
    }
}

$selectedBranchLabels = [];
foreach ($selectedBranches as $selectedBranchId) {
    if (isset($branchLookup[$selectedBranchId])) {
        $selectedBranchLabels[] = $branchLookup[$selectedBranchId];
    }
}

$displayNameParts = array_values(array_filter([
    trim((string)$firstName),
    trim((string)$middleName),
    trim((string)$lastName),
], function ($value) {
    return $value !== '';
}));

$displayName = !empty($displayNameParts) ? implode(' ', $displayNameParts) : 'Employee name preview';
$previewImageSrc = (!empty($image)) ? url('public' . $image) : env('NO_IMAGE_AVATAR');
$selectedBranchCount = count($selectedBranches);
$contactPreviewParts = array_values(array_filter([
    trim((string)$email),
    trim((string)$phone),
], function ($value) {
    return $value !== '';
}));
$contactPreviewText = !empty($contactPreviewParts) ? implode(' | ', $contactPreviewParts) : 'Add contact details';
$genderPreview = !empty($gender) ? $gender : 'Select gender';
$agePreview = ($age !== '' && $age !== null) ? $age : '--';
$profileStateLabel = $isEdit ? 'Updating existing profile' : 'Creating new profile';
$submitLabel = $isEdit ? 'Update Employee' : 'Save Employee';
?>

<div class="employee-shell">
    <div class="employee-hero mb-4">
        <div class="employee-hero-content">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-end">
                <div class="pe-xl-4">
                    <div class="employee-hero-kicker">Employee Profile</div>
                    <h2><?= $action ?> <?= $module['title'] ?></h2>
                    <p>
                        Build or refine a polished staff record with structured identity, contact, branch, and photo
                        details presented in a clean professional layout.
                    </p>
                    <div class="employee-hero-badges">
                        <span class="employee-hero-badge"><i class="fa-solid fa-id-card-clip"></i> Auto employee number</span>
                        <span class="employee-hero-badge"><i class="fa-solid fa-diagram-project"></i> Multi-branch assignment</span>
                        <span class="employee-hero-badge"><i class="fa-solid fa-camera-retro"></i> Live photo preview</span>
                    </div>
                </div>
                <a href="<?= url($controllerRoute . '/list') ?>" class="btn btn-outline-light btn-lg">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
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

    <form method="POST" action="" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card employee-form-card">
                    <div class="card-body">
                        <div class="employee-form-section">
                            <div class="employee-section-title">
                                <span><i class="fa-solid fa-id-card"></i> Identity</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="employee_no" class="form-label">Employee No</label>
                                    <input type="text" class="form-control employee-readonly" name="employee_no" id="employee_no" value="<?= e($employeeNo) ?>" readonly>
                                    <div class="employee-hint">This number is assigned automatically and stays locked for audit consistency.</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" value="<?= e($firstName) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="middle_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name" value="<?= e($middleName) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="<?= e($lastName) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="employee-form-section">
                            <div class="employee-section-title">
                                <span><i class="fa-solid fa-address-book"></i> Contact & Personal</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Email Address" value="<?= e($email) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone Number" value="<?= e($phone) ?>" minlength="10" maxlength="10" inputmode="numeric" onkeypress="return isNumber(event)">
                                </div>
                                <div class="col-md-4">
                                    <label for="pincode" class="form-label">Pincode</label>
                                    <input type="text" class="form-control" name="pincode" id="pincode" placeholder="Pincode" value="<?= e($pincode) ?>" minlength="6" maxlength="6" inputmode="numeric" onkeypress="return isNumber(event)">
                                </div>
                                <div class="col-md-4">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" name="gender" id="gender">
                                        <option value="">Select</option>
                                        <option value="Male" <?= (($gender == 'Male') ? 'selected' : '') ?>>Male</option>
                                        <option value="Female" <?= (($gender == 'Female') ? 'selected' : '') ?>>Female</option>
                                        <option value="Others" <?= (($gender == 'Others') ? 'selected' : '') ?>>Others</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="employee-form-section">
                            <div class="employee-section-title">
                                <span><i class="fa-solid fa-briefcase"></i> Work Details</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="dob" id="dob" max="<?= date('Y-m-d') ?>" value="<?= e($dob) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="text" class="form-control employee-readonly" name="age" id="age" value="<?= e($age) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label for="doj" class="form-label">Date of Joining <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="doj" id="doj" max="<?= date('Y-m-d') ?>" value="<?= e($doj) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="salary" class="form-label">Salary <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="salary" id="salary" placeholder="Salary" value="<?= e($salary) ?>" required>
                                </div>
                                <div class="col-md-8">
                                    <label for="branch" class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select class="form-select" name="branch[]" id="branch" multiple required>
                                        <?php if ($branchOptions) {
                                            foreach ($branchOptions as $loop_row) {
                                                $branchLabel = $loop_row->name . (isset($unitNames[$loop_row->unit_id]) && $unitNames[$loop_row->unit_id] !== '' ? ' (' . $unitNames[$loop_row->unit_id] . ')' : '');
                                            ?>
                                                <option value="<?= $loop_row->id ?>" <?= (in_array((string)$loop_row->id, $selectedBranches) ? 'selected' : '') ?>><?= $branchLabel ?></option>
                                        <?php }
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="employee-hint mt-md-4 pt-md-3">
                                        Choose one or more branches to define the employee's work access and reporting scope.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="employee-form-section mb-0 pb-0 border-0">
                            <div class="employee-section-title">
                                <span><i class="fa-solid fa-location-dot"></i> Address</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" name="address" id="address" rows="4" placeholder="Address"><?= e($address) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card employee-summary-card">
                    <div class="card-body">
                        <div class="employee-summary-kicker">Profile Snapshot</div>
                        <h3 class="employee-summary-title"><?= $profileStateLabel ?></h3>
                        <div class="employee-summary-badge">
                            <i class="fa-solid fa-lock"></i>
                            Employee No locked for audit trail
                        </div>

                        <div class="employee-preview-frame mt-3 mb-3">
                            <img
                                src="<?= e($previewImageSrc) ?>"
                                alt="Employee Image"
                                id="employee_image_preview"
                                data-default-src="<?= e($previewImageSrc) ?>"
                            >
                        </div>

                        <div class="employee-upload">
                            <label for="image" class="form-label mb-2">Employee Photo</label>
                            <input type="file" class="form-control" name="image" id="image" accept="image/*">
                            <div class="employee-hint mb-0">
                                Accepted formats: JPG, JPEG, PNG, GIF, WEBP, SVG, and AVIF. A square crop gives the cleanest result.
                            </div>
                        </div>

                        <div class="employee-summary-grid">
                            <div class="employee-summary-item">
                                <span class="employee-summary-label">Employee No</span>
                                <span class="employee-summary-value" id="employee_preview_no"><?= e($employeeNo ?: 'Auto-generated on save') ?></span>
                            </div>
                            <div class="employee-summary-item">
                                <span class="employee-summary-label">Full Name</span>
                                <span class="employee-summary-value" id="employee_preview_name"><?= e($displayName) ?></span>
                            </div>
                            <div class="employee-summary-item">
                                <span class="employee-summary-label">Contact</span>
                                <span class="employee-summary-value" id="employee_preview_contact"><?= e($contactPreviewText) ?></span>
                            </div>
                            <div class="employee-summary-item">
                                <span class="employee-summary-label">Age / Gender</span>
                                <span class="employee-summary-value">
                                    <span id="employee_preview_age"><?= e($agePreview) ?></span>
                                    /
                                    <span id="employee_preview_gender"><?= e($genderPreview) ?></span>
                                </span>
                            </div>
                            <div class="employee-summary-item">
                                <span class="employee-summary-label">Branches</span>
                                <span class="employee-summary-value" id="employee_preview_branch_count"><?= e($selectedBranchCount) ?></span>
                            </div>
                        </div>

                        <div class="employee-branch-list" id="employee_branch_preview">
                            <?php if (!empty($selectedBranchLabels)) { ?>
                                <?php foreach ($selectedBranchLabels as $selectedBranchLabel) { ?>
                                    <span class="employee-branch-pill"><?= e($selectedBranchLabel) ?></span>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="employee-branch-pill">No branches selected yet</span>
                            <?php } ?>
                        </div>

                        <div class="employee-summary-note mt-4">
                            <i class="fa-solid fa-circle-info mt-1"></i>
                            <span>
                                Keep the details accurate. Employee number is assigned at save time, while branch selection
                                and photo preview help create a polished profile before submission.
                            </span>
                        </div>

                        <div class="employee-actions d-grid gap-2 mt-4">
                            <a href="<?= url($controllerRoute . '/list') ?>" class="btn btn-employee-secondary btn-lg">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-employee-primary btn-lg">
                                <i class="fa-solid fa-floppy-disk me-1"></i> <?= $submitLabel ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.js"></script>
<script>
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    function getPreviewText(id) {
        var element = document.getElementById(id);
        return element ? element.textContent.trim() : '';
    }

    function setPreviewText(id, value) {
        var element = document.getElementById(id);
        if (!element) {
            return;
        }

        element.textContent = value;
    }

    function renderBranchPreview(selectedOptions) {
        var container = document.getElementById('employee_branch_preview');
        if (!container) {
            return;
        }

        container.innerHTML = '';

        if (!selectedOptions || !selectedOptions.length) {
            var emptyTag = document.createElement('span');
            emptyTag.className = 'employee-branch-pill';
            emptyTag.textContent = 'No branches selected yet';
            container.appendChild(emptyTag);
            return;
        }

        selectedOptions.forEach(function(option) {
            var tag = document.createElement('span');
            tag.className = 'employee-branch-pill';
            tag.textContent = option.text;
            container.appendChild(tag);
        });
    }

    function syncEmployeePreview() {
        var firstName = getFieldValue('first_name');
        var middleName = getFieldValue('middle_name');
        var lastName = getFieldValue('last_name');
        var displayNameParts = [firstName, middleName, lastName].filter(function(value) {
            return value !== '';
        });
        var displayName = displayNameParts.length ? displayNameParts.join(' ') : 'Employee name preview';

        setPreviewText('employee_preview_name', displayName);
        setPreviewText('employee_preview_contact', buildContactPreview());
        setPreviewText('employee_preview_gender', getFieldValue('gender') || 'Select gender');
        setPreviewText('employee_preview_age', getFieldValue('age') || '--');
        setPreviewText('employee_preview_no', getFieldValue('employee_no') || 'Auto-generated on save');

        var branchSelect = document.getElementById('branch');
        var selectedOptions = branchSelect ? Array.prototype.slice.call(branchSelect.selectedOptions || []) : [];
        setPreviewText('employee_preview_branch_count', String(selectedOptions.length));
        renderBranchPreview(selectedOptions);
    }

    function getFieldValue(id) {
        var element = document.getElementById(id);
        return element ? element.value.trim() : '';
    }

    function buildContactPreview() {
        var email = getFieldValue('email');
        var phone = getFieldValue('phone');
        var contactParts = [];

        if (email) {
            contactParts.push(email);
        }

        if (phone) {
            contactParts.push(phone);
        }

        return contactParts.length ? contactParts.join(' | ') : 'Add contact details';
    }

    function calculateAge() {
        var dobInput = document.getElementById('dob');
        var ageInput = document.getElementById('age');

        if (!dobInput || !ageInput) {
            return;
        }

        if (!dobInput.value) {
            ageInput.value = '';
            syncEmployeePreview();
            return;
        }

        var dobParts = dobInput.value.split('-');
        if (dobParts.length !== 3) {
            ageInput.value = '';
            syncEmployeePreview();
            return;
        }

        var dob = new Date(
            parseInt(dobParts[0], 10),
            parseInt(dobParts[1], 10) - 1,
            parseInt(dobParts[2], 10)
        );

        if (isNaN(dob.getTime())) {
            ageInput.value = '';
            syncEmployeePreview();
            return;
        }

        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var monthDiff = today.getMonth() - dob.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        ageInput.value = (age >= 0) ? age : '';
        syncEmployeePreview();
    }

    function updateEmployeePhotoPreview(file) {
        var previewImage = document.getElementById('employee_image_preview');

        if (!previewImage) {
            return;
        }

        if (!file) {
            previewImage.src = previewImage.getAttribute('data-default-src') || previewImage.src;
            return;
        }

        var reader = new FileReader();
        reader.onload = function(event) {
            previewImage.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var dobInput = document.getElementById('dob');
        var branchSelect = document.getElementById('branch');
        var imageInput = document.getElementById('image');
        var previewFields = ['first_name', 'middle_name', 'last_name', 'email', 'phone', 'gender', 'employee_no'];

        previewFields.forEach(function(fieldId) {
            var element = document.getElementById(fieldId);
            if (!element) {
                return;
            }

            element.addEventListener('input', syncEmployeePreview);
            element.addEventListener('change', syncEmployeePreview);
        });

        if (dobInput) {
            dobInput.addEventListener('change', calculateAge);
            dobInput.addEventListener('input', calculateAge);
            calculateAge();
        }

        if (branchSelect) {
            branchSelect.addEventListener('change', syncEmployeePreview);
        }

        if (imageInput) {
            imageInput.addEventListener('change', function(event) {
                var file = event.target.files && event.target.files[0];
                updateEmployeePhotoPreview(file || null);
            });
        }

        if (branchSelect && typeof Choices !== 'undefined') {
            new Choices(branchSelect, {
                removeItemButton: true,
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Select branches',
                itemSelectText: ''
            });
        }

        syncEmployeePreview();
    });
</script>
@endsection
