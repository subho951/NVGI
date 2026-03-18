@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
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
        gap: 3px;
        margin-top: 5px;
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
            <p class="fees-subtitle">Collect monthly fees, track due balances, and monitor yearly totals in one place.</p>
        </div>
        <div class="fees-hero-stats">
            <div class="fees-stat">
                <p class="fees-stat-label">Students Listed</p>
                <p class="fees-stat-value"><?= count($rows) ?></p>
            </div>
            <div class="fees-stat">
                <p class="fees-stat-label">Collection Year</p>
                <p class="fees-stat-value"><?= $search_collection_year ?></p>
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
                    <label class="fees-label" for="collection_year">Year</label>
                    <select class="form-select form-select-sm fees-select-control" name="collection_year" id="collection_year" required>
                        <option selected value="">Select</option>
                        <?php for($y=date('Y');$y>=2020;$y--){?>
                        <option value="<?= $y ?>" <?= (($search_collection_year == $y)?'selected':'') ?>><?= $y ?></option>
                        <?php } ?>
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
                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                        <th class="text-center"><?= date("M", mktime(0, 0, 0, $m, 1)) ?></th>
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

                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                        <?php
                            $month_payable  = 0.00;
                            $month_paid     = 0.00;
                            $month_due      = 0.00;
                            if($m == 1){
                                $month_payable  = $row->jan_payable;
                                $month_paid     = $row->jan_paid;
                                $month_due      = $row->jan_due;
                            }
                            if($m == 2){
                                $month_payable  = $row->feb_payable;
                                $month_paid     = $row->feb_paid;
                                $month_due      = $row->feb_due;
                            }
                            if($m == 3){
                                $month_payable  = $row->mar_payable;
                                $month_paid     = $row->mar_paid;
                                $month_due      = $row->mar_due;
                            }
                            if($m == 4){
                                $month_payable  = $row->apr_payable;
                                $month_paid     = $row->apr_paid;
                                $month_due      = $row->apr_due;
                            }
                            if($m == 5){
                                $month_payable  = $row->may_payable;
                                $month_paid     = $row->may_paid;
                                $month_due      = $row->may_due;
                            }
                            if($m == 6){
                                $month_payable  = $row->jun_payable;
                                $month_paid     = $row->jun_paid;
                                $month_due      = $row->jun_due;
                            }
                            if($m == 7){
                                $month_payable  = $row->jul_payable;
                                $month_paid     = $row->jul_paid;
                                $month_due      = $row->jul_due;
                            }
                            if($m == 8){
                                $month_payable  = $row->aug_payable;
                                $month_paid     = $row->aug_paid;
                                $month_due      = $row->aug_due;
                            }
                            if($m == 9){
                                $month_payable  = $row->sep_payable;
                                $month_paid     = $row->sep_paid;
                                $month_due      = $row->sep_due;
                            }
                            if($m == 10){
                                $month_payable  = $row->oct_payable;
                                $month_paid     = $row->oct_paid;
                                $month_due      = $row->oct_due;
                            }
                            if($m == 11){
                                $month_payable  = $row->nov_payable;
                                $month_paid     = $row->nov_paid;
                                $month_due      = $row->nov_due;
                            }
                            if($m == 12){
                                $month_payable  = $row->dec_payable;
                                $month_paid     = $row->dec_paid;
                                $month_due      = $row->dec_due;
                            }

                            $monthName   = date("F", mktime(0, 0, 0, $m, 1));
                            $isMonthPaid = ((float)$month_due <= 0);
                        ?>
                        <td class="month-cell" data-student-id="<?= $row->id ?>" data-month="<?= $m ?>">
                            <div class="fee-line payable">Payable <span class="month-payable"><?= number_format((float)$month_payable,2) ?></span></div>
                            <div class="fee-line paid">Paid <span class="month-paid"><?= number_format((float)$month_paid,2) ?></span></div>
                            <div class="fee-line due">Due <span class="month-due"><?= number_format((float)$month_due,2) ?></span></div>

                            <form method="POST"
                                action="javascript:void(0);"
                                class="fee-payment-form <?= ($isMonthPaid ? 'd-none' : '') ?>"
                                data-student-id="<?= $row->id ?>"
                                data-student-name="<?= htmlspecialchars($row->full_name, ENT_QUOTES) ?>"
                                data-month="<?= $m ?>"
                                data-month-name="<?= $monthName ?>"
                                data-year="<?= $search_collection_year ?>"
                                data-payable="<?= number_format((float)$month_payable, 2, '.', '') ?>"
                                data-due="<?= number_format((float)$month_due, 2, '.', '') ?>">
                                @csrf
                                <input type="text"
                                    name="payment_amount"
                                    class="form-control form-control-sm payment-amount-input"
                                    placeholder="Amount"
                                    autocomplete="off"
                                    oninput="allowNumberDot(this)">

                                <button type="submit" class="btn btn-success btn-sm payment-submit-btn" title="Submit">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </button>
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
                        <td colspan="15" class="no-data-message">No students found !!!</td>
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

        $('#branch_id').val('');
        $('#branch_id .branch').hide();
        $('#branch_id .unit' + search_unit).show();
        $('#branch_id').val(search_branch);

        $('#unit_id').on('change', function(){
            var unit_id = $('#unit_id').val();
            $('#branch_id').val('');
            $('#branch_id .branch').hide();
            $('#branch_id .unit' + unit_id).show();
        });

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

