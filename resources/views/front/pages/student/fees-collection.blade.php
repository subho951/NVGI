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
        overflow-x: hidden;
    }
    .fees-table {
        margin-bottom: 0;
        width: 100%;
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
        width: 160px;
    }
    .fees-col-total {
        width: 115px;
    }
    .fees-table tbody tr:hover td {
        background: #f8fbff;
    }
    .student-meta {
        text-align: left;
        width: 160px;
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
        width: 78px;
        min-width: 78px;
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
    .fee-payment-form .payment-mode-select {
        font-size: 10px;
        border-radius: 6px;
        height: 26px;
        padding: 2px 5px;
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
        font-size: 10px;
        border-radius: 6px;
        height: 26px;
        padding: 2px 5px;
    }
    .fee-payment-form .payment-submit-btn {
        border-radius: 6px;
        min-width: 26px;
        height: 26px;
        padding: 0;
        font-size: 10px;
    }
    .due-cleared-badge {
        margin-top: 5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 9px;
        font-weight: 700;
        color: #0f6b3f;
        background: #ebfbf1;
        border: 1px solid #bde6cb;
        border-radius: 999px;
        padding: 2px 6px;
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
            min-width: 1240px;
            table-layout: auto;
        }
        .month-cell,
        .total-cell,
        .student-meta {
            min-width: auto;
            width: auto;
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
                <div class="col-lg-3 col-md-6">
                    <label class="fees-label" for="unit_id">Unit</label>
                    <select class="form-select form-select-sm fees-select-control" name="unit_id" id="unit_id" required>
                        <option selected value="">Select</option>
                        <?php if($units){ foreach($units as $loop_row){?>
                        <option value="<?= $loop_row->id ?>" <?= (($search_unit == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="fees-label" for="branch_id">Branch</label>
                    <select class="form-select form-select-sm fees-select-control" name="branch_id" id="branch_id" required>
                        <option selected value="">Select</option>
                        <?php if($branches){ foreach($branches as $loop_row){?>
                        <option class="branch unit<?= $loop_row->unit_id ?>" value="<?= $loop_row->id ?>" <?= (($search_branch == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="fees-label" for="collection_session_id">Session</label>
                    <select class="form-select form-select-sm fees-select-control" name="collection_session_id" id="collection_session_id" required>
                        <option selected value="">Select</option>
                        <?php if($sessions){ foreach($sessions as $loop_row){?>
                        <option value="<?= $loop_row->id ?>" <?= (($search_session == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-lg-4 col-md-8">
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
                        <th class="text-center">
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
                            $monthName     = date("F", mktime(0, 0, 0, $monthInfo['month'], 1));
                            $isMonthPaid   = ((float)$month_due <= 0);
                        ?>
                        <td class="month-cell" data-student-id="<?= $row->id ?>" data-month="<?= $monthInfo['month'] ?>">
                            <div class="fee-line payable">Payable <span class="month-payable"><?= number_format((float)$month_payable,2) ?></span></div>
                            <div class="fee-line paid">Paid <span class="month-paid"><?= number_format((float)$month_paid,2) ?></span></div>
                            <div class="fee-line due">Due <span class="month-due"><?= number_format((float)$month_due,2) ?></span></div>

                            <form method="POST"
                                action="javascript:void(0);"
                                class="fee-payment-form <?= ($isMonthPaid ? 'd-none' : '') ?>"
                                data-student-id="<?= $row->id ?>"
                                data-student-name="<?= htmlspecialchars($row->full_name, ENT_QUOTES) ?>"
                                data-month="<?= $monthInfo['month'] ?>"
                                data-month-name="<?= $monthName ?>"
                                data-year="<?= $monthInfo['year'] ?>"
                                data-payable="<?= number_format((float)$month_payable, 2, '.', '') ?>"
                                data-due="<?= number_format((float)$month_due, 2, '.', '') ?>">
                                @csrf
                                <select class="form-select form-select-sm payment-mode-select" name="payment_mode" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank" selected>Bank</option>
                                </select>
                                <input type="hidden" name="ledger_id" value="1">
                                <div class="payment-actions">
                                    <input type="text"
                                        name="payment_amount"
                                        class="form-control form-control-sm payment-amount-input"
                                        placeholder="Amount"
                                        autocomplete="off"
                                        oninput="allowNumberDot(this)">

                                    <button type="submit" class="btn btn-success btn-sm payment-submit-btn" title="Submit">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </button>
                                </div>
                            </form>

                            <div class="due-cleared-badge <?= ($isMonthPaid ? '' : 'd-none') ?>">
                                <i class="fa-solid fa-circle-check"></i> Paid
                            </div>
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
        var report_unit = '<?= $report_unit ?>';
        var report_branch = '<?= $report_branch ?>';
        var report_class = '<?= $report_class ?>';

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

        bindUnitWiseBranch(search_unit, '#branch_id', 'branch', search_branch);
        bindUnitWiseBranch(report_unit, '#report_branch_id', 'report-branch', report_branch);
        bindUnitWiseClass(report_unit, '#report_class_id', 'report-class', report_class);

        $('#unit_id').on('change', function(){
            bindUnitWiseBranch($('#unit_id').val(), '#branch_id', 'branch', '');
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

        $(document).on('submit', '.fee-payment-form', function(e){
            e.preventDefault();

            var form = $(this);
            var amountInput = form.find('.payment-amount-input');
            var submitButton = form.find('.payment-submit-btn');
            var enteredAmount = $.trim(amountInput.val());
            var amountNumber = parseFloat(enteredAmount);

            var payableAmount = parseFloat(form.data('payable')) || 0;
            var dueAmount = parseFloat(form.data('due')) || 0;
            var studentName = form.data('student-name') || 'Student';
            var monthName = form.data('month-name') || 'Month';
            var year = form.data('year');

            if (enteredAmount === '' || isNaN(amountNumber) || amountNumber <= 0) {
                showFeesToast('Please enter a valid amount.', 'error');
                return;
            }

            if (amountNumber > payableAmount) {
                showFeesToast('Payment amount cannot be greater than payable amount for ' + studentName + ' (' + monthName + ' ' + year + ').', 'error');
                return;
            }

            if (dueAmount <= 0) {
                showFeesToast('No due left for ' + studentName + ' (' + monthName + ' ' + year + ').', 'error');
                form.addClass('d-none');
                form.closest('.month-cell').find('.due-cleared-badge').removeClass('d-none');
                return;
            }

            if (amountNumber > dueAmount) {
                showFeesToast('Payment amount cannot be greater than due amount for ' + studentName + ' (' + monthName + ' ' + year + ').', 'error');
                return;
            }

            $.ajax({
                url: "{{ route('student.fees-collection.update') }}",
                method: "POST",
                dataType: "json",
                data: {
                    _token: form.find('input[name="_token"]').val(),
                    student_id: form.data('student-id'),
                    payable_month: form.data('month'),
                    payable_year: form.data('year'),
                    payment_mode: form.find('select[name="payment_mode"]').val(),
                    ledger_id: form.find('input[name="ledger_id"]').val(),
                    payment_amount: enteredAmount
                },
                beforeSend: function(){
                    submitButton.prop('disabled', true);
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
                    form.attr('data-payable', response.month.payable_numeric);
                    form.attr('data-due', response.month.due_numeric);

                    if (parseFloat(response.month.due_numeric) <= 0) {
                        form.addClass('d-none');
                        monthCell.find('.due-cleared-badge').removeClass('d-none');
                    }

                    amountInput.val('');
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
                    submitButton.prop('disabled', false);
                }
            });
        });
    })
</script>
@endsection

