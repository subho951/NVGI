@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
$financialMonths = ((isset($financial_months) && is_array($financial_months) && count($financial_months) > 0) ? $financial_months : []);
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.css">
<script src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.js"></script>

<style>
    .choices__list--multiple .choices__item {
        background-color: #48974e;
        border: 1px solid #48974e;
    }
    .fees-months-group .choices {
        margin-bottom: 0;
        transition: margin-bottom .2s ease;
    }
    .fees-months-group .choices.is-open {
        margin-bottom: 195px;
    }
    .fees-months-group .choices__inner {
        min-height: 40px;
        height: 40px;
        border-radius: 10px;
        border: 1px solid #dbe5ef;
        padding: 3px 8px 3px 6px;
        display: flex;
        align-items: center;
        overflow: hidden;
    }
    .fees-months-group .choices__list--multiple {
        display: flex;
        flex-wrap: nowrap;
        gap: 5px;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        scrollbar-width: thin;
    }
    .fees-months-group .choices__list--multiple .choices__item {
        margin: 0;
        flex: 0 0 auto;
        font-size: 12px;
        line-height: 1;
    }
    .fees-months-group .choices__input {
        min-width: 56px !important;
        margin: 0 !important;
        padding: 0 4px !important;
        font-size: 12px;
    }
    .fees-months-group .choices__list--dropdown,
    .fees-months-group .choices__list[aria-expanded] {
        max-height: 190px;
        overflow-y: auto;
        z-index: 5;
    }
    a {
        text-decoration: none;
    }
    .fees-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .fees-hero {
        background: linear-gradient(120deg, #0f4c81, #1e6f9f 58%, #1a8a7c);
        color: #ffffff;
        border-radius: 16px;
        padding: 22px 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 14px;
        box-shadow: 0 14px 30px rgba(15, 76, 129, 0.25);
    }
    .fees-eyebrow {
        margin: 0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.85;
    }
    .fees-hero h2 {
        margin: 6px 0 8px;
        font-size: 30px;
        font-weight: 700;
        line-height: 1.15;
    }
    .fees-subtitle {
        margin: 0;
        opacity: 0.94;
        font-size: 14px;
    }
    .fees-hero-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .fees-stat {
        min-width: 135px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 10px 12px;
    }
    .fees-stat-label {
        margin: 0;
        font-size: 10px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        opacity: 0.86;
    }
    .fees-stat-value {
        margin: 4px 0 0;
        font-size: 20px;
        font-weight: 700;
    }
    .fees-panel,
    .fees-table-panel {
        background: #ffffff;
        border: 1px solid #dbe5ef;
        border-radius: 14px;
        box-shadow: 0 10px 28px rgba(19, 52, 82, 0.08);
    }
    .fees-panel {
        padding: 18px;
    }
    .fees-panel-title {
        margin: 0 0 14px;
        color: #0e2d46;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .fees-label {
        font-size: 12px;
        font-weight: 600;
        color: #234463;
        margin-bottom: 6px;
        display: inline-block;
    }
    .fees-select-control,
    .fees-action-btn {
        height: 40px;
        border-radius: 10px !important;
    }
    .fees-multi-select {
        height: 40px !important;
        min-height: 40px;
    }
    .fees-select-control {
        font-size: 13px;
        padding-top: 0;
        padding-bottom: 0;
    }
    .student-id-autocomplete {
        position: relative;
    }
    .student-suggest-list {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        z-index: 30;
        max-height: 238px;
        overflow-y: auto;
        border: 1px solid #cfe0f1;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(18, 50, 76, 0.16);
        padding: 5px;
    }
    .student-suggest-item {
        width: 100%;
        border: 0;
        border-radius: 8px;
        background: #ffffff;
        text-align: left;
        padding: 7px 8px;
        display: block;
        color: #122f49;
    }
    .student-suggest-item:hover,
    .student-suggest-item.is-active {
        background: #ecf5ff;
    }
    .student-suggest-serial {
        display: block;
        font-size: 12px;
        font-weight: 800;
        color: #0f5b99;
        line-height: 1.2;
    }
    .student-suggest-name {
        display: block;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.25;
        margin-top: 2px;
    }
    .student-suggest-meta,
    .student-suggest-empty {
        display: block;
        font-size: 10px;
        color: #5a7086;
        line-height: 1.25;
        margin-top: 2px;
    }
    .student-suggest-empty {
        padding: 7px 8px;
    }
    .fees-actions {
        display: flex;
        gap: 8px;
        width: 100%;
    }
    .fees-actions .btn {
        flex: 1;
        font-size: 13px;
        font-weight: 600;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .fees-table-header {
        padding: 16px 18px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .fees-table-header h5 {
        margin: 0;
        color: #102a43;
        font-size: 18px;
        font-weight: 700;
    }
    .fees-table-header p {
        margin: 3px 0 0;
        color: #5a7086;
        font-size: 12px;
    }
    .fees-count-badge {
        background: #ecf5ff;
        color: #0f5b99;
        border: 1px solid #cae3fb;
        border-radius: 999px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 700;
    }
    .fees-table-wrap {
        padding: 14px 14px 12px;
        overflow-x: auto;
    }
    .fees-table {
        margin-bottom: 0;
        width: 100%;
        min-width: 1540px;
        table-layout: fixed;
    }
    .fees-table thead th {
        background: #12324c;
        color: #f5faff;
        font-size: 10px;
        font-weight: 700;
        border-color: #1f496a;
        white-space: normal;
        line-height: 1.1;
        padding: 6px 4px;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .fees-table tbody td {
        border-color: #e5edf5;
        background: #ffffff;
        vertical-align: top;
        padding: 4px;
    }
    .fees-col-index {
        width: 32px;
    }
    .fees-col-student {
        width: 120px;
        min-width: 120px;
    }
    .fees-col-total {
        width: 115px;
    }
    .fees-col-month,
    .month-cell {
        width: 106px;
        min-width: 106px;
    }
    .fees-table tbody tr:hover td {
        background: #f8fbff;
    }
    .student-meta {
        text-align: left;
        width: 120px;
        min-width: 120px;
    }
    .student-id {
        font-size: 11px;
        color: #1d5e92;
        font-weight: 700;
    }
    .student-avatar {
        width: 30px;
        height: 30px;
        border: 1px solid #ccdae7;
        border-radius: 50%;
        object-fit: cover;
        margin: 4px 0;
    }
    .student-name {
        display: block;
        font-size: 11px;
        color: #122f49;
        font-weight: 700;
    }
    .student-extra {
        display: block;
        font-size: 10px;
        color: #4f6479;
        line-height: 1.2;
    }
    .month-cell {
        position: relative;
        padding: 6px !important;
        background: #f8fbff;
    }
    .fees-table tbody tr:hover td.month-cell {
        background: #f2f7fc;
    }
    .month-fee-box {
        min-height: 122px;
        border: 1px solid #cfe0f1;
        border-radius: 8px;
        background: #ffffff;
        padding: 6px;
        box-shadow: 0 1px 4px rgba(18, 50, 76, 0.08);
    }
    .month-fee-box.paid {
        border-color: #bde6cb;
        background: #f8fff9;
    }
    .month-fee-box.pending {
        border-color: #d7e6f5;
        background: #ffffff;
    }
    .month-fee-box.warning {
        border-color: #e9bd69;
        background: #fffaf0;
    }
    .month-fee-box.error {
        border-color: #f0b8bf;
        background: #fffafa;
    }
    .month-fee-box.neutral {
        border-color: #d7e1ea;
        background: #f8fafc;
    }
    .fee-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 3px;
        border-radius: 6px;
        padding: 3px 4px;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.05;
    }
    .fee-line.payable {
        background: #edf5ff;
        color: #0e4d89;
    }
    .fee-line.paid {
        background: #ebfbf1;
        color: #0f6b3f;
    }
    .fee-line.due {
        background: #fff1f1;
        color: #b42331;
    }
    .fee-payment-form {
        display: flex;
        flex-direction: column;
        gap: 3px;
        margin-top: 5px;
    }
    .fee-payment-form.is-processing {
        opacity: 0.64;
        pointer-events: none;
    }
    .fee-payment-form .payment-mode-select {
        font-size: 9px;
        border-radius: 6px;
        height: 26px;
        padding: 2px 4px;
    }
    .fee-payment-form .bank-account-select {
        font-size: 9px;
        border-radius: 6px;
        height: 26px;
        padding: 2px 4px;
    }
    .fee-payment-form .payment-reference-input {
        font-size: 9px;
        border-radius: 6px;
        height: 26px;
        padding: 2px 4px;
    }
    .fee-payment-form .payment-actions {
        display: flex;
        gap: 3px;
    }
    .fee-payment-form .payment-actions .payment-amount-input {
        flex: 1 1 auto;
        min-width: 0;
    }
    .fee-payment-form .payment-amount-input {
        font-size: 9px;
        border-radius: 6px;
        height: 26px;
        padding: 2px 4px;
    }
    .fee-payment-form .payment-submit-btn {
        border-radius: 6px;
        min-width: 26px;
        height: 26px;
        padding: 0;
        font-size: 10px;
    }
    .collection-status-badge {
        margin-top: 5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 9px;
        font-weight: 700;
        border-radius: 999px;
        padding: 2px 6px;
        line-height: 1.15;
        white-space: normal;
    }
    .collection-status-badge.paid {
        color: #0f6b3f;
        background: #ebfbf1;
        border: 1px solid #bde6cb;
    }
    .collection-status-badge.pending {
        color: #815500;
        background: #fff8e6;
        border: 1px solid #f4d58d;
    }
    .collection-status-badge.warning {
        color: #7a4b00;
        background: #fff3db;
        border: 1px solid #e9bd69;
    }
    .collection-status-badge.error {
        color: #b42331;
        background: #fff1f1;
        border: 1px solid #f0b8bf;
    }
    .collection-status-badge.neutral {
        color: #4f6479;
        background: #eef3f7;
        border: 1px solid #d7e1ea;
    }
    .fee-cell-loader {
        position: absolute;
        inset: 0;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.72);
        color: #0f5b99;
        font-size: 18px;
    }
    .fee-cell-loader.d-none {
        display: none !important;
    }
    .total-cell {
        width: 115px;
        min-width: 115px;
        text-align: left;
    }
    .total-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 5px;
        margin-bottom: 3px;
        border-radius: 6px;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.05;
    }
    .total-line.payable {
        background: #edf5ff;
        color: #0e4d89;
    }
    .total-line.paid {
        background: #ebfbf1;
        color: #0f6b3f;
    }
    .total-line.due {
        background: #fff1f1;
        color: #b42331;
        margin-bottom: 0;
    }
    .no-data-message {
        color: #b42331;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        padding: 14px !important;
    }
    #fees-toast {
        position: fixed;
        left: 20px;
        bottom: 20px;
        z-index: 9999;
        min-width: 290px;
        max-width: 480px;
        color: #ffffff;
        border-radius: 10px;
        padding: 10px 14px;
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.22);
        display: none;
        font-size: 13px;
        font-weight: 600;
    }
    #fees-toast.success {
        background: #198754;
    }
    #fees-toast.error {
        background: #c12e3f;
    }
    @media (max-width: 991px) {
        .fees-hero {
            padding: 18px;
        }
        .fees-hero h2 {
            font-size: 24px;
        }
        .fees-panel,
        .fees-table-panel {
            border-radius: 12px;
        }
        .fees-actions {
            margin-top: 2px;
        }
        .fees-table-wrap {
            overflow-x: auto;
        }
        .fees-table {
            min-width: 1540px;
            table-layout: fixed;
        }
        .month-cell,
        .fees-col-month {
            min-width: 106px;
            width: 106px;
        }
        .fees-col-student,
        .student-meta {
            min-width: 120px;
            width: 120px;
        }
    }
</style>

<div class="fees-page">
    <div class="fees-hero">
        <div>
            <p class="fees-eyebrow">Student Finance Desk</p>
            <h2><?= $module['title'] ?> Fees Collection</h2>
            <p class="fees-subtitle">Collect monthly fees, track due balances, and monitor session totals in one place.</p>
        </div>
        <div class="fees-hero-stats">
            <div class="fees-stat">
                <p class="fees-stat-label">Students Listed</p>
                <p class="fees-stat-value"><?= count($rows) ?></p>
            </div>
            <div class="fees-stat">
                <p class="fees-stat-label">Collection Session</p>
                <p class="fees-stat-value"><?= (($search_session_name != '') ? $search_session_name : '-') ?></p>
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

    <div class="fees-panel">
        <h6 class="fees-panel-title">Search Students</h6>
        <form method="POST" action="">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label class="fees-label" for="student_id_serial">Student Serial No</label>
                    <div class="student-id-autocomplete">
                        <input type="text"
                               class="form-control form-control-sm fees-select-control"
                               name="student_id_serial"
                               id="student_id_serial"
                               value="<?= htmlspecialchars(($search_student_id ?? ''), ENT_QUOTES) ?>"
                               placeholder="Student Serial No"
                               autocomplete="off"
                               aria-autocomplete="list"
                               aria-expanded="false"
                               aria-controls="studentSerialSuggestions">
                        <div id="studentSerialSuggestions" class="student-suggest-list d-none"></div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label class="fees-label" for="unit_id">Unit</label>
                    <select class="form-select form-select-sm fees-select-control" name="unit_id" id="unit_id" <?= (($search_student_id ?? '') == '' ? 'required' : '') ?>>
                        <option selected value="">Select</option>
                        <?php if($units){ foreach($units as $loop_row){?>
                        <option value="<?= $loop_row->id ?>" <?= (($search_unit == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label class="fees-label" for="branch_id">Branch</label>
                    <select class="form-select form-select-sm fees-select-control" name="branch_id" id="branch_id" <?= (($search_student_id ?? '') == '' ? 'required' : '') ?>>
                        <option selected value="">Select</option>
                        <?php if($branches){ foreach($branches as $loop_row){?>
                        <option class="branch unit<?= $loop_row->unit_id ?>" value="<?= $loop_row->id ?>" <?= (($search_branch == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label class="fees-label" for="collection_session_id">Session</label>
                    <select class="form-select form-select-sm fees-select-control" name="collection_session_id" id="collection_session_id" required>
                        <option selected value="">Select</option>
                        <?php if($sessions){ foreach($sessions as $loop_row){?>
                        <option value="<?= $loop_row->id ?>" <?= (($search_session == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-xl-4 col-lg-12 col-md-12">
                    <div class="fees-actions">
                        <button type="submit" class="btn btn-success fees-action-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                        <?php if($is_search){?>
                        <a href="<?= url('student/fees-collection') ?>" class="btn btn-secondary fees-action-btn"><i class="fa-solid fa-rotate-right"></i> Reset</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="fees-panel">
        <h6 class="fees-panel-title">Due Students Report (Excel)</h6>
        <form method="POST" action="{{ route('student.fees-collection.due-report') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-lg-2 col-md-6">
                    <label class="fees-label" for="report_unit_id">Unit</label>
                    <select class="form-select form-select-sm fees-select-control" name="report_unit_id" id="report_unit_id" required>
                        <option selected value="">Select</option>
                        <?php if($units){ foreach($units as $loop_row){?>
                        <option value="<?= $loop_row->id ?>" <?= (($report_unit == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="fees-label" for="report_branch_id">Branch</label>
                    <select class="form-select form-select-sm fees-select-control" name="report_branch_id" id="report_branch_id" required>
                        <option selected value="">Select</option>
                        <?php if($branches){ foreach($branches as $loop_row){?>
                        <option class="report-branch unit<?= $loop_row->unit_id ?>" value="<?= $loop_row->id ?>" <?= (($report_branch == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="fees-label" for="report_class_id">Class</label>
                    <select class="form-select form-select-sm fees-select-control" name="report_class_id" id="report_class_id">
                        <option class="all-option" selected value="">ALL</option>
                        <?php if($classes){ foreach($classes as $loop_row){?>
                        <option class="report-class unit<?= $loop_row->unit_id ?>" value="<?= $loop_row->id ?>" <?= (($report_class == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="fees-label" for="report_session_id">Session</label>
                    <select class="form-select form-select-sm fees-select-control" name="report_session_id" id="report_session_id" required>
                        <option selected value="">Select</option>
                        <?php if($sessions){ foreach($sessions as $loop_row){?>
                        <option value="<?= $loop_row->id ?>" <?= (($report_session == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-12 fees-months-group">
                    <label class="fees-label" for="choices-multiple-remove-button">Collection Months (Multiple)</label>
                    <select class="form-select form-select-sm fees-select-control fees-multi-select fees-months-input" name="report_months[]" id="choices-multiple-remove-button" multiple required>
                        <?php foreach ($financialMonths as $monthInfo) { ?>
                        <option value="<?= $monthInfo['month'] ?>" <?= ((in_array($monthInfo['month'], $report_months))?'selected':'') ?>><?= date("F", mktime(0, 0, 0, $monthInfo['month'], 1)) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-lg-1 col-md-12">
                    <button type="submit" class="btn btn-primary fees-action-btn w-100">
                        <i class="fa-solid fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="fees-table-panel">
        <div class="fees-table-header">
            <div>
                <h5>Student Collection Ledger</h5>
                <p>Submit fee payments month-wise and see totals update instantly.</p>
            </div>
            <div class="fees-count-badge"><?= count($rows) ?> students found</div>
        </div>

        <div class="table-responsive fees-table-wrap">
            <table class="table table-bordered fees-table align-middle text-center" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center fees-col-index">#</th>
                        <th class="text-center fees-col-student">Student Info</th>
                        <?php foreach ($financialMonths as $monthInfo) { ?>
                        <th class="text-center fees-col-month">
                            <?= $monthInfo['short'] ?><br>
                            <small><?= $monthInfo['year'] ?></small>
                        </th>
                        <?php } ?>
                        <th class="text-center fees-col-total">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rows) > 0) {
                        $sl = 1;
                        foreach ($rows as $row) { ?>
                    <tr>
                        <td class="fw-semibold fees-col-index"><?= $sl++ ?></td>
                        <td class="student-meta">
                            <span class="student-id"><?= $row->student_id_serial ?></span>
                            <br>
                            <?php if ($row->photo != '') { ?>
                            <img class="student-avatar" src="<?= config('constants.app_url') . config('constants.uploads_url_path') . $row->photo ?>">
                            <?php } else { ?>
                            <img class="student-avatar" src="{{ config('constants.no_image_avatar') }}">
                            <?php } ?>
                            <span class="student-name"><?= $row->full_name ?></span>
                            <span class="student-extra"><?= $row->father_mobile ?></span>
                            <span class="student-extra"><?= $row->class_name ?></span>
                        </td>

                        <?php foreach ($financialMonths as $monthInfo) { ?>
                        <?php
                            $monthAlias  = $monthInfo['alias'];
                            $month_payable = (isset($row->{$monthAlias . '_payable'}) ? $row->{$monthAlias . '_payable'} : 0);
                            $month_paid    = (isset($row->{$monthAlias . '_paid'}) ? $row->{$monthAlias . '_paid'} : 0);
                            $month_due     = (isset($row->{$monthAlias . '_due'}) ? $row->{$monthAlias . '_due'} : 0);
                            $month_txn_count  = (isset($row->{$monthAlias . '_txn_count'}) ? (int)$row->{$monthAlias . '_txn_count'} : 0);
                            $month_txn_amount = (isset($row->{$monthAlias . '_txn_amount'}) ? (float)$row->{$monthAlias . '_txn_amount'} : 0);
                            $monthName     = date("F", mktime(0, 0, 0, $monthInfo['month'], 1));
                            $hasTransactionMismatch = (abs((float)$month_paid - $month_txn_amount) > 0.009);
                            $canCollect    = ((float)$month_payable > 0 && (float)$month_due > 0 && !$hasTransactionMismatch);
                            $statusClass   = 'pending';
                            $statusLabel   = 'Pending';
                            $statusIcon    = 'fa-clock';

                            if ((float)$month_payable <= 0) {
                                $statusClass = 'neutral';
                                $statusLabel = 'No Fee';
                                $statusIcon  = 'fa-minus';
                            } elseif ((float)$month_paid > 0 && $month_txn_count <= 0) {
                                $statusClass = 'error';
                                $statusLabel = 'Txn Missing';
                                $statusIcon  = 'fa-triangle-exclamation';
                            } elseif ($hasTransactionMismatch) {
                                $statusClass = 'error';
                                $statusLabel = 'Txn Mismatch';
                                $statusIcon  = 'fa-triangle-exclamation';
                            } elseif ((float)$month_paid - (float)$month_payable > 0.009) {
                                $statusClass = 'error';
                                $statusLabel = 'Overpaid';
                                $statusIcon  = 'fa-triangle-exclamation';
                            } elseif ((float)$month_paid > 0 && (float)$month_due <= 0) {
                                $statusClass = 'paid';
                                $statusLabel = 'Paid';
                                $statusIcon  = 'fa-circle-check';
                            } elseif ((float)$month_paid > 0 && (float)$month_due > 0) {
                                $statusClass = 'warning';
                                $statusLabel = 'Partially Paid';
                                $statusIcon  = 'fa-circle-half-stroke';
                            }
                        ?>
                        <td class="month-cell" data-student-id="<?= $row->id ?>" data-month="<?= $monthInfo['month'] ?>">
                            <div class="month-fee-box <?= $statusClass ?>">
                                <div class="fee-line payable">Payable <span class="month-payable"><?= number_format((float)$month_payable,2) ?></span></div>
                                <div class="fee-line paid">Paid <span class="month-paid"><?= number_format((float)$month_paid,2) ?></span></div>
                                <div class="fee-line due">Due <span class="month-due"><?= number_format((float)$month_due,2) ?></span></div>

                                <form method="POST"
                                    action="javascript:void(0);"
                                    class="fee-payment-form <?= (!$canCollect ? 'd-none' : '') ?>"
                                    data-student-id="<?= $row->id ?>"
                                    data-student-name="<?= htmlspecialchars($row->full_name, ENT_QUOTES) ?>"
                                    data-month="<?= $monthInfo['month'] ?>"
                                    data-month-name="<?= $monthName ?>"
                                    data-year="<?= $monthInfo['year'] ?>"
                                    data-payable="<?= number_format((float)$month_payable, 2, '.', '') ?>"
                                    data-due="<?= number_format((float)$month_due, 2, '.', '') ?>"
                                    data-transaction-count="<?= $month_txn_count ?>"
                                    data-transaction-amount="<?= number_format((float)$month_txn_amount, 2, '.', '') ?>">
                                    @csrf
                                    <select class="form-select form-select-sm payment-mode-select" name="payment_mode" required>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank" selected>Bank</option>
                                    </select>
                                    <div class="bank-account-group">
                                        <select class="form-select form-select-sm bank-account-select"
                                                name="bank_account_id"
                                                required>
                                            <option value="">Bank Account</option>
                                            @forelse($bankAccounts as $bankAccountRow)
                                                <option value="{{ $bankAccountRow->id }}">
                                                    {{ $bankAccountRow->bank_name }}{{ ($bankAccountRow->account_no != '') ? ' (' . $bankAccountRow->account_no . ')' : '' }}
                                                </option>
                                            @empty
                                                <option value="">No bank accounts available</option>
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="payment-reference-group">
                                        <input type="text"
                                               class="form-control form-control-sm payment-reference-input"
                                               name="payment_reference"
                                               placeholder="UTR / cheque number / transaction ID"
                                               autocomplete="off"
                                               required>
                                    </div>
                                    <input type="hidden" name="ledger_id" value="1">
                                    <div class="payment-actions">
                                        <input type="text"
                                            name="payment_amount"
                                            class="form-control form-control-sm payment-amount-input"
                                            placeholder="Amount"
                                            value="<?= number_format((float)$month_due, 2, '.', '') ?>"
                                            autocomplete="off"
                                            inputmode="decimal"
                                            oninput="allowNumberDot(this)">

                                        <button type="submit" class="btn btn-success btn-sm payment-submit-btn" title="Submit">
                                            <i class="fa-solid fa-arrow-right payment-btn-icon"></i>
                                            <i class="fa-solid fa-spinner fa-spin payment-btn-loader d-none"></i>
                                        </button>
                                    </div>
                                </form>

                                <div class="collection-status-badge <?= $statusClass ?>" data-status-badge>
                                    <i class="fa-solid <?= $statusIcon ?>"></i> <span><?= $statusLabel ?></span>
                                </div>
                            </div>
                            <div class="fee-cell-loader d-none"><i class="fa-solid fa-spinner fa-spin"></i></div>
                        </td>
                        <?php } ?>

                        <td class="total-cell" data-student-id="<?= $row->id ?>">
                            <div class="total-line payable">Payable <span class="total-payable"><?= number_format((float)$row->total_payable,2) ?></span></div>
                            <div class="total-line paid">Paid <span class="total-paid"><?= number_format((float)$row->total_paid,2) ?></span></div>
                            <div class="total-line due">Due <span class="total-due"><?= number_format((float)$row->total_due,2) ?></span></div>
                        </td>
                    </tr>
                    <?php }
                    } else { ?>
                    <tr>
                        <td colspan="<?= 3 + count($financialMonths) ?>" class="no-data-message">No students found !!!</td>
                    </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="fees-toast"></div>

@endsection
@section('scripts')
<script>
    function allowNumberDot(input) {
        input.value = input.value
            .replace(/[^0-9.]/g, '')
            .replace(/(\..*)\./g, '$1');
    }
</script>
<script>
    $(function(){
        var search_unit = '<?= $search_unit ?>';
        var search_branch = '<?= $search_branch ?>';
        var search_student_id = @json($search_student_id ?? '');
        var report_unit = '<?= $report_unit ?>';
        var report_branch = '<?= $report_branch ?>';
        var report_class = '<?= $report_class ?>';
        var studentSuggestUrl = @json(route('student.fees-collection.student-suggestions'));
        var studentSuggestTimer = null;
        var studentSuggestRequest = null;
        var studentSuggestActiveIndex = -1;

        function bindUnitWiseBranch(unitId, branchSelector, optionClass, selectedBranch){
            var branchSelect = $(branchSelector);
            branchSelect.val('');
            branchSelect.find('.' + optionClass).hide();
            if (unitId !== '') {
                branchSelect.find('.unit' + unitId).show();
            }
            if (selectedBranch !== '') {
                branchSelect.val(selectedBranch);
            }
        }

        function bindUnitWiseClass(unitId, classSelector, optionClass, selectedClass){
            var classSelect = $(classSelector);
            classSelect.val('');
            classSelect.find('.' + optionClass).hide();
            classSelect.find('.all-option').show();
            if (unitId !== '') {
                classSelect.find('.unit' + unitId).show();
            }
            if (selectedClass !== '') {
                classSelect.val(selectedClass);
            }
        }

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function hideStudentSuggestions() {
            studentSuggestActiveIndex = -1;
            $('#studentSerialSuggestions').addClass('d-none').empty();
            $('#student_id_serial').attr('aria-expanded', 'false');
        }

        function setActiveStudentSuggestion(index) {
            var items = $('#studentSerialSuggestions .student-suggest-item');
            if (!items.length) {
                studentSuggestActiveIndex = -1;
                return;
            }

            if (index < 0) {
                index = items.length - 1;
            }
            if (index >= items.length) {
                index = 0;
            }

            studentSuggestActiveIndex = index;
            items.removeClass('is-active');
            $(items.get(index)).addClass('is-active');
        }

        function renderStudentSuggestions(students) {
            if (!$('#student_id_serial').is(':focus')) {
                return;
            }

            var suggestionBox = $('#studentSerialSuggestions');
            students = Array.isArray(students) ? students : Object.values(students || {});
            studentSuggestActiveIndex = -1;

            if (!students.length) {
                suggestionBox.html('<div class="student-suggest-empty">No matching student found</div>');
                suggestionBox.removeClass('d-none');
                $('#student_id_serial').attr('aria-expanded', 'true');
                return;
            }

            var html = students.map(function(student){
                var metaParts = [];
                if (student.class) {
                    metaParts.push(student.class);
                }
                if (student.mobile) {
                    metaParts.push(student.mobile);
                }

                return '<button type="button" class="student-suggest-item" data-student-serial="' + escapeHtml(student.serial) + '">' +
                    '<span class="student-suggest-serial">' + escapeHtml(student.serial) + '</span>' +
                    '<span class="student-suggest-name">' + escapeHtml(student.name) + '</span>' +
                    '<span class="student-suggest-meta">' + escapeHtml(metaParts.join(' | ')) + '</span>' +
                '</button>';
            }).join('');

            suggestionBox.html(html).removeClass('d-none');
            $('#student_id_serial').attr('aria-expanded', 'true');
        }

        function loadStudentSuggestions() {
            var sessionId = $('#collection_session_id').val();
            if (!sessionId) {
                hideStudentSuggestions();
                return;
            }

            if (studentSuggestRequest) {
                studentSuggestRequest.abort();
            }

            studentSuggestRequest = $.ajax({
                url: studentSuggestUrl,
                method: 'GET',
                dataType: 'json',
                data: {
                    term: $.trim($('#student_id_serial').val()),
                    session_id: sessionId
                },
                success: function(response){
                    renderStudentSuggestions(response.students || []);
                },
                error: function(xhr){
                    if (xhr.statusText !== 'abort') {
                        hideStudentSuggestions();
                    }
                },
                complete: function(){
                    studentSuggestRequest = null;
                }
            });
        }

        function scheduleStudentSuggestions(delay) {
            clearTimeout(studentSuggestTimer);
            studentSuggestTimer = setTimeout(function(){
                loadStudentSuggestions();
            }, delay);
        }

        function selectStudentSuggestion(button) {
            var serial = button.attr('data-student-serial');
            $('#student_id_serial').val(serial).trigger('change');
            hideStudentSuggestions();
            syncStudentSearchMode();
        }

        bindUnitWiseBranch(search_unit, '#branch_id', 'branch', search_branch);
        bindUnitWiseBranch(report_unit, '#report_branch_id', 'report-branch', report_branch);
        bindUnitWiseClass(report_unit, '#report_class_id', 'report-class', report_class);

        function syncStudentSearchMode(){
            var hasStudentId = $.trim($('#student_id_serial').val()) !== '';
            $('#unit_id, #branch_id')
                .prop('required', !hasStudentId)
                .prop('disabled', hasStudentId);
        }

        $('#student_id_serial').val(search_student_id);
        syncStudentSearchMode();

        $('#student_id_serial').on('focus', function(){
            scheduleStudentSuggestions(0);
        });

        $('#student_id_serial').on('input', function(){
            syncStudentSearchMode();
            scheduleStudentSuggestions(220);
        });

        $('#student_id_serial').on('change', function(){
            syncStudentSearchMode();
        });

        $('#student_id_serial').on('keydown', function(event){
            var suggestionBox = $('#studentSerialSuggestions');
            var items = suggestionBox.find('.student-suggest-item');

            if (suggestionBox.hasClass('d-none') || !items.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setActiveStudentSuggestion(studentSuggestActiveIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActiveStudentSuggestion(studentSuggestActiveIndex - 1);
            } else if (event.key === 'Enter' && studentSuggestActiveIndex >= 0) {
                event.preventDefault();
                selectStudentSuggestion($(items.get(studentSuggestActiveIndex)));
            } else if (event.key === 'Escape') {
                hideStudentSuggestions();
            }
        });

        $(document).on('mousedown', '.student-suggest-item', function(event){
            event.preventDefault();
            selectStudentSuggestion($(this));
        });

        $(document).on('mousedown', function(event){
            if (!$(event.target).closest('.student-id-autocomplete').length) {
                hideStudentSuggestions();
            }
        });

        $('#unit_id').on('change', function(){
            bindUnitWiseBranch($('#unit_id').val(), '#branch_id', 'branch', '');
            syncStudentSearchMode();
        });

        $('#collection_session_id').on('change', function(){
            if ($('#student_id_serial').is(':focus')) {
                scheduleStudentSuggestions(0);
            }
        });

        $('#report_unit_id').on('change', function(){
            var selectedUnit = $('#report_unit_id').val();
            bindUnitWiseBranch(selectedUnit, '#report_branch_id', 'report-branch', '');
            bindUnitWiseClass(selectedUnit, '#report_class_id', 'report-class', '');
        });

        if ($('#choices-multiple-remove-button').length) {
            new Choices('#choices-multiple-remove-button', {
                removeItemButton: true,
                maxItemCount: 12,
                searchResultLimit: 12,
                renderChoiceLimit: 12,
                closeDropdownOnSelect: true,
                shouldSort: false,
                itemSelectText: '',
                searchEnabled: false
            });
        }

        function showFeesToast(message, type){
            var toast = $('#fees-toast');
            toast.removeClass('success error').addClass(type);
            toast.text(message);
            toast.stop(true, true).fadeIn(200);

            if (window.feesToastTimer) {
                clearTimeout(window.feesToastTimer);
            }
            window.feesToastTimer = setTimeout(function () {
                toast.fadeOut(300);
            }, 3800);
        }

        function toggleBankPaymentFields(form, paymentMode) {
            var group = form.find('.bank-account-group');
            var input = form.find('select[name="bank_account_id"]');
            var referenceGroup = form.find('.payment-reference-group');
            var referenceInput = form.find('input[name="payment_reference"]');
            var shouldShow = (paymentMode === 'Bank');

            if (shouldShow) {
                group.removeClass('d-none');
                input.prop('disabled', false);
                input.prop('required', true);
                referenceGroup.removeClass('d-none');
                referenceInput.prop('disabled', false);
                referenceInput.prop('required', true);
            } else {
                group.addClass('d-none');
                input.prop('disabled', true);
                input.prop('required', false);
                referenceGroup.addClass('d-none');
                referenceInput.prop('disabled', true);
                referenceInput.prop('required', false);
            }
        }

        function setFeeFormProcessing(form, isProcessing) {
            var monthCell = form.closest('.month-cell');
            form.toggleClass('is-processing', isProcessing);
            monthCell.find('.fee-cell-loader').toggleClass('d-none', !isProcessing);
            form.find('input, select, button').prop('disabled', isProcessing);
            form.find('.payment-btn-icon').toggleClass('d-none', isProcessing);
            form.find('.payment-btn-loader').toggleClass('d-none', !isProcessing);

            if (!isProcessing) {
                toggleBankPaymentFields(form, form.find('select[name="payment_mode"]').val());
            }
        }

        function updateCollectionStatus(monthCell, statusClass, statusLabel, statusIcon) {
            var badge = monthCell.find('[data-status-badge]');
            var box = monthCell.find('.month-fee-box');
            box.removeClass('paid pending warning error neutral').addClass(statusClass);
            badge.removeClass('paid pending warning error neutral').addClass(statusClass);
            badge.html('<i class="fa-solid ' + statusIcon + '"></i> <span>' + statusLabel + '</span>');
        }

        $('.fee-payment-form').each(function () {
            var form = $(this);
            toggleBankPaymentFields(form, form.find('select[name="payment_mode"]').val());
        });

        $(document).on('change', '.fee-payment-form .payment-mode-select', function () {
            var form = $(this).closest('.fee-payment-form');
            toggleBankPaymentFields(form, $(this).val());
        });

        $(document).on('submit', '.fee-payment-form', function(e){
            e.preventDefault();

            var form = $(this);
            if (form.hasClass('is-processing')) {
                return;
            }

            var amountInput = form.find('.payment-amount-input');
            var enteredAmount = $.trim(amountInput.val());
            var amountNumber = parseFloat(enteredAmount);
            var paymentMode = form.find('select[name="payment_mode"]').val();
            var bankAccountId = $.trim(form.find('select[name="bank_account_id"]').val());
            var paymentReference = $.trim(form.find('input[name="payment_reference"]').val());

            var dueAmount = parseFloat(form.data('due')) || 0;
            var studentName = form.data('student-name') || 'Student';
            var monthName = form.data('month-name') || 'Month';
            var year = form.data('year');

            if (enteredAmount === '' || isNaN(amountNumber) || amountNumber <= 0) {
                showFeesToast('Please enter a valid amount.', 'error');
                return;
            }

            if (dueAmount <= 0) {
                showFeesToast('No due left for ' + studentName + ' (' + monthName + ' ' + year + ').', 'error');
                form.addClass('d-none');
                updateCollectionStatus(form.closest('.month-cell'), 'paid', 'Paid', 'fa-circle-check');
                return;
            }

            if (amountNumber - dueAmount > 0.009) {
                showFeesToast('Payment amount cannot be greater than the remaining due for ' + studentName + ' (' + monthName + ' ' + year + ').', 'error');
                return;
            }

            if (paymentMode === 'Bank' && bankAccountId === '') {
                showFeesToast('Please select a bank account for ' + studentName + ' (' + monthName + ' ' + year + ').', 'error');
                return;
            }

            if (paymentMode === 'Bank' && paymentReference === '') {
                showFeesToast('Please enter a payment reference for ' + studentName + ' (' + monthName + ' ' + year + ').', 'error');
                return;
            }

            var requestData = {
                _token: form.find('input[name="_token"]').val(),
                student_id: form.data('student-id'),
                payable_month: form.data('month'),
                payable_year: form.data('year'),
                payment_mode: paymentMode,
                ledger_id: form.find('input[name="ledger_id"]').val(),
                payment_amount: enteredAmount
            };

            if (paymentMode === 'Bank') {
                requestData.bank_account_id = bankAccountId;
                requestData.payment_reference = paymentReference;
            }

            $.ajax({
                url: "{{ route('student.fees-collection.update') }}",
                method: "POST",
                dataType: "json",
                data: requestData,
                beforeSend: function(){
                    setFeeFormProcessing(form, true);
                },
                success: function(response){
                    var monthCell = form.closest('.month-cell');
                    var totalCell = form.closest('tr').find('.total-cell');

                    monthCell.find('.month-payable').text(response.month.payable);
                    monthCell.find('.month-paid').text(response.month.paid);
                    monthCell.find('.month-due').text(response.month.due);

                    totalCell.find('.total-payable').text(response.total.payable);
                    totalCell.find('.total-paid').text(response.total.paid);
                    totalCell.find('.total-due').text(response.total.due);

                    form.data('payable', response.month.payable_numeric);
                    form.data('due', response.month.due_numeric);
                    form.data('transaction-count', response.month.transaction_count);
                    form.data('transaction-amount', response.month.transaction_amount);
                    form.attr('data-payable', response.month.payable_numeric);
                    form.attr('data-due', response.month.due_numeric);
                    form.attr('data-transaction-count', response.month.transaction_count);
                    form.attr('data-transaction-amount', response.month.transaction_amount);

                    if (parseFloat(response.month.due_numeric) <= 0) {
                        form.addClass('d-none');
                        updateCollectionStatus(monthCell, 'paid', 'Paid', 'fa-circle-check');
                        amountInput.val('');
                    } else {
                        form.removeClass('d-none');
                        amountInput.val(response.month.due_numeric);
                        form.find('.payment-reference-input').val('');
                        updateCollectionStatus(monthCell, 'warning', 'Partially Paid', 'fa-circle-half-stroke');
                    }

                    showFeesToast(response.message, 'success');
                },
                error: function(xhr){
                    var message = 'Something went wrong. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showFeesToast(message, 'error');
                },
                complete: function(){
                    setFeeFormProcessing(form, false);
                }
            });
        });
    })
</script>
@endsection
