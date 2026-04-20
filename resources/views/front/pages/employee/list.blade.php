@extends('front.layouts.afterlogin')

@section('styles')
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
        font-size: 2.25rem;
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

    .employee-stats {
        margin: 18px 0 20px;
    }

    .employee-stat-card {
        height: 100%;
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 16px 18px;
        border: 1px solid var(--employee-border);
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfe 100%);
        box-shadow: 0 14px 30px rgba(18, 33, 53, 0.07);
    }

    .employee-stat-icon {
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #fff;
        box-shadow: 0 12px 20px rgba(16, 38, 61, 0.14);
    }

    .employee-stat-icon.total {
        background: linear-gradient(135deg, #173b5d 0%, #245f86 100%);
    }

    .employee-stat-icon.active {
        background: linear-gradient(135deg, #11805a 0%, #25a36d 100%);
    }

    .employee-stat-icon.inactive {
        background: linear-gradient(135deg, #8b5a2b 0%, #c78433 100%);
    }

    .employee-stat-icon.branches {
        background: linear-gradient(135deg, #3f4f85 0%, #6a7fd1 100%);
    }

    .employee-stat-label {
        margin-bottom: 6px;
        color: #6f8194;
        font-size: 0.73rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .employee-stat-value {
        margin: 0;
        color: var(--employee-ink);
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .employee-stat-note {
        margin-top: 4px;
        color: #6b7f93;
        font-size: 0.82rem;
    }

    .employee-card {
        overflow: hidden;
        border: 1px solid var(--employee-border);
        border-radius: 22px;
        background: var(--employee-surface);
        box-shadow: 0 16px 35px rgba(18, 33, 53, 0.08);
    }

    .employee-card-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid #e7edf5;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfe 100%);
    }

    .employee-card-title {
        margin: 0;
        color: var(--employee-ink);
        font-size: 1.03rem;
        font-weight: 800;
    }

    .employee-card-subtitle {
        margin-top: 4px;
        color: #637689;
        font-size: 0.88rem;
    }

    .employee-mini-note {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #eef4fa;
        color: #234766;
        font-size: 0.76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .employee-table-wrap {
        padding: 0 18px 18px;
    }

    .employee-table {
        margin-bottom: 0 !important;
    }

    .employee-table thead th {
        padding: 0.92rem 0.75rem;
        border-bottom: 0;
        background: var(--employee-navy);
        color: #fff;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .employee-table tbody td {
        color: #21384d;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .employee-table tbody tr:hover {
        background: #f6fbff;
    }

    .employee-name {
        color: var(--employee-ink);
        font-weight: 700;
    }

    .employee-empty {
        color: #95a3b1;
        font-style: italic;
    }

    .employee-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }

    .employee-badge.active {
        background: rgba(20, 132, 81, 0.1);
        color: #12744a;
        border-color: rgba(20, 132, 81, 0.16);
    }

    .employee-badge.inactive {
        background: rgba(180, 83, 9, 0.1);
        color: #9a5714;
        border-color: rgba(180, 83, 9, 0.18);
    }

    .employee-branch-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0 6px 6px 0;
        padding: 4px 9px;
        border: 1px solid #d8e4ef;
        border-radius: 999px;
        background: #eef4fa;
        color: #234766;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .employee-action {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 6px;
        border: 1px solid transparent;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.18s ease;
    }

    .employee-action:last-child {
        margin-right: 0;
    }

    .employee-action.edit {
        background: rgba(18, 71, 112, 0.08);
        color: #15527f;
    }

    .employee-action.edit:hover {
        background: rgba(18, 71, 112, 0.14);
        transform: translateY(-1px);
    }

    .employee-action.toggle.active {
        background: rgba(20, 132, 81, 0.1);
        color: #12744a;
    }

    .employee-action.toggle.active:hover {
        background: rgba(20, 132, 81, 0.16);
        transform: translateY(-1px);
    }

    .employee-action.toggle.inactive {
        background: rgba(180, 83, 9, 0.1);
        color: #9a5714;
    }

    .employee-action.toggle.inactive:hover {
        background: rgba(180, 83, 9, 0.16);
        transform: translateY(-1px);
    }

    .employee-empty-state {
        padding: 44px 18px;
        text-align: center;
        color: #6c7f92;
    }

    .employee-empty-state i {
        display: block;
        margin-bottom: 12px;
        color: #b7c4d1;
        font-size: 2rem;
    }

    .employee-empty-state h6 {
        margin-bottom: 6px;
        color: #12314d;
        font-weight: 800;
    }

    .employee-shell .dataTables_wrapper .dt-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .employee-shell .dataTables_wrapper .dt-buttons .btn {
        padding: 0.45rem 0.9rem;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(16, 38, 61, 0.08);
        font-weight: 700;
    }

    .employee-shell .dataTables_wrapper .dataTables_filter,
    .employee-shell .dataTables_wrapper .dataTables_length {
        margin-bottom: 12px;
    }

    .employee-shell .dataTables_wrapper .dataTables_filter label,
    .employee-shell .dataTables_wrapper .dataTables_length label {
        color: #5f7286;
        font-weight: 700;
    }

    .employee-shell .dataTables_wrapper .dataTables_filter input,
    .employee-shell .dataTables_wrapper .dataTables_length select {
        min-height: 38px;
        padding: 0.45rem 0.75rem;
        border: 1px solid #d8e3ee;
        border-radius: 10px;
        box-shadow: none;
    }

    .employee-shell .dataTables_wrapper .dataTables_filter input:focus,
    .employee-shell .dataTables_wrapper .dataTables_length select:focus {
        border-color: #6e99c1;
        box-shadow: 0 0 0 0.2rem rgba(54, 106, 160, 0.12);
    }

    .employee-shell .dataTables_wrapper .paginate_button {
        border-radius: 10px !important;
    }

    @media (max-width: 991px) {
        .employee-hero {
            padding: 22px;
        }

        .employee-hero h2 {
            font-size: 1.8rem;
        }

        .employee-card-head {
            flex-direction: column;
        }
    }

    @media (max-width: 767px) {
        .employee-hero {
            padding: 18px;
        }

        .employee-hero h2 {
            font-size: 1.55rem;
        }

        .employee-stat-card {
            padding: 14px 15px;
        }

        .employee-table-wrap {
            padding: 0 12px 12px;
        }
    }
</style>
@endsection

@section('content')
<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
$employeeRows = collect($rows ?? []);
$hasEmployeeRows = $employeeRows->isNotEmpty();
$employeeStats = [
    'total' => $employeeRows->count(),
    'active' => $employeeRows->where('status', 1)->count(),
    'inactive' => $employeeRows->where('status', 0)->count(),
    'branches' => $employeeRows->flatMap(function ($row) {
        return $row->branch_names ?? [];
    })->unique()->count(),
];
?>

<div class="employee-shell">
    <div class="employee-hero mb-4">
        <div class="employee-hero-content">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-end">
                <div class="pe-xl-4">
                    <div class="employee-hero-kicker">Human Resources</div>
                    <h2>Employee Directory</h2>
                    <p>
                        Maintain a polished staff directory with quick access to employee numbers, branch
                        assignments, contact details, and status controls from one refined workspace.
                    </p>
                    <div class="employee-hero-badges">
                        <span class="employee-hero-badge"><i class="fa-solid fa-id-badge"></i> Auto-generated employee numbers</span>
                        <span class="employee-hero-badge"><i class="fa-solid fa-diagram-project"></i> Multi-branch mapping</span>
                        <span class="employee-hero-badge"><i class="fa-solid fa-shield-heart"></i> Status-controlled access</span>
                    </div>
                </div>
                <a href="{{ url($controllerRoute . '/add') }}" class="btn btn-employee-primary btn-lg">
                    <i class="fa-solid fa-plus me-1"></i> Add Employee
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

    <div class="row g-3 employee-stats">
        <div class="col-sm-6 col-xl-3">
            <div class="employee-stat-card">
                <div class="employee-stat-icon total">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="employee-stat-label">Total Employees</div>
                    <p class="employee-stat-value">{{ $employeeStats['total'] }}</p>
                    <div class="employee-stat-note">Profiles in this directory</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="employee-stat-card">
                <div class="employee-stat-icon active">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="employee-stat-label">Active</div>
                    <p class="employee-stat-value">{{ $employeeStats['active'] }}</p>
                    <div class="employee-stat-note">Currently enabled profiles</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="employee-stat-card">
                <div class="employee-stat-icon inactive">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div>
                    <div class="employee-stat-label">Inactive</div>
                    <p class="employee-stat-value">{{ $employeeStats['inactive'] }}</p>
                    <div class="employee-stat-note">Temporarily disabled profiles</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="employee-stat-card">
                <div class="employee-stat-icon branches">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div>
                    <div class="employee-stat-label">Branches Covered</div>
                    <p class="employee-stat-value">{{ $employeeStats['branches'] }}</p>
                    <div class="employee-stat-note">Unique branch assignments</div>
                </div>
            </div>
        </div>
    </div>

    <div class="employee-card">
        <div class="employee-card-head">
            <div>
                <h3 class="employee-card-title">List of {{ $module['title'] }}</h3>
                <div class="employee-card-subtitle">
                    Use the built-in search and export tools to review or share employee records quickly.
                </div>
            </div>
            <div class="employee-mini-note">
                <i class="fa-solid fa-filter"></i>
                Search, sort, and export ready
            </div>
        </div>
        <div class="employee-table-wrap">
            @if ($hasEmployeeRows)
                <div class="table-responsive">
                    <table id="example" class="table table-hover align-middle employee-table datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee No</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Branches</th>
                                <th>DOB</th>
                                <th>Age</th>
                                <th>DOJ</th>
                                <th>Salary</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sl = 1;
                            foreach ($employeeRows as $row) {
                                $encodedId = Helper::encoded($row->id);
                                $statusUrl = $controllerRoute . '/change-status/';
                                $editUrl = $controllerRoute . '/edit/' . $encodedId;
                            ?>
                                <tr>
                                    <td><?= $sl++ ?></td>
                                    <td><?= e($row->employee_no) ?></td>
                                    <td class="employee-name"><?= e($row->employee_name) ?></td>
                                    <td><?= (!empty($row->gender) ? e($row->gender) : '<span class="employee-empty">--</span>') ?></td>
                                    <td><?= (!empty($row->email) ? e($row->email) : '<span class="employee-empty">--</span>') ?></td>
                                    <td><?= (!empty($row->phone) ? e($row->phone) : '<span class="employee-empty">--</span>') ?></td>
                                    <td>
                                        <?php if (!empty($row->branch_names)) { ?>
                                            <?php foreach ($row->branch_names as $branchName) { ?>
                                                <span class="employee-branch-tag"><?= e($branchName) ?></span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span class="employee-empty">--</span>
                                        <?php } ?>
                                    </td>
                                    <td><?= (!empty($row->dob) ? date('d-m-Y', strtotime($row->dob)) : '<span class="employee-empty">--</span>') ?></td>
                                    <td><?= ($row->age !== null ? e($row->age) : '<span class="employee-empty">--</span>') ?></td>
                                    <td><?= (!empty($row->doj) ? date('d-m-Y', strtotime($row->doj)) : '<span class="employee-empty">--</span>') ?></td>
                                    <td><?= number_format((float)$row->salary, 2) ?></td>
                                    <td>
                                        <?php if ((int)$row->status === 1) { ?>
                                            <span class="employee-badge active"><i class="fa-solid fa-circle-check"></i> Active</span>
                                        <?php } else { ?>
                                            <span class="employee-badge inactive"><i class="fa-solid fa-circle-xmark"></i> Inactive</span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="<?= url($editUrl) ?>" class="employee-action edit" title="Edit <?= $module['title'] ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <?php if ((int)$row->status === 1) { ?>
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to deactivate this record?')" class="employee-action toggle active" title="Deactivate <?= $module['title'] ?>">
                                                <i class="fa-solid fa-toggle-on"></i>
                                            </a>
                                        <?php } else { ?>
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to activate this record?')" class="employee-action toggle inactive" title="Activate <?= $module['title'] ?>">
                                                <i class="fa-solid fa-toggle-off"></i>
                                            </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="employee-empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <h6>No employee records yet</h6>
                    <p class="mb-3">Add the first employee to start building the directory.</p>
                    <a href="<?= url($controllerRoute . '/add') ?>" class="btn btn-employee-primary">
                        <i class="fa-solid fa-plus me-1"></i> Add Employee
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
