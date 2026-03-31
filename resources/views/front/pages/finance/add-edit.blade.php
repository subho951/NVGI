@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];

$isEdit = (!empty($row));
$type = old('type', (($isEdit) ? $row->type : ''));
$transactionTimestamp = old('transaction_timestamp', (($isEdit && $row->transaction_timestamp) ? date('Y-m-d\TH:i', strtotime($row->transaction_timestamp)) : date('Y-m-d\TH:i')));
$transactionAmount = old('transaction_amount', (($isEdit) ? number_format((float)$row->transaction_amount, 2, '.', '') : ''));
$unitId = old('unit_id', (($isEdit) ? $row->unit_id : ''));
$branchId = old('branch_id', (($isEdit) ? $row->branch_id : ''));
$paymentMode = old('payment_mode', (($isEdit) ? $row->payment_mode : ''));
$paymentReference = old('payment_reference', (($isEdit) ? $row->payment_reference : ''));
$particulars = old('particulars', (($isEdit) ? $row->particulars : ''));
$note = old('note', (($isEdit) ? $row->note : ''));
$createdBy = old('created_by', (($isEdit) ? $row->created_by : $default_created_by));
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

    .finance-form-space {
        --ink: #172b40;
        --ink-soft: #5b7086;
        --line: #dce6f1;
        --surface: #ffffff;
        --accent: #c49c4f;
        font-family: 'Sora', sans-serif;
    }
    .entry-header {
        background: linear-gradient(125deg, #16293d 0%, #234362 62%, #315b82 100%);
        border-radius: 16px;
        color: #fff;
        padding: 20px 24px;
        box-shadow: 0 18px 38px rgba(16, 36, 57, .22);
        margin-bottom: 18px;
    }
    .entry-header h2 {
        font-family: 'Playfair Display', serif;
        margin: 0;
        font-size: 1.75rem;
    }
    .entry-header p {
        margin: 7px 0 0;
        opacity: .88;
        font-size: .92rem;
    }
    .meta-chip {
        background: rgba(255, 255, 255, .17);
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 999px;
        color: #fff;
        font-size: .75rem;
        padding: 7px 12px;
        display: inline-block;
        margin-top: 8px;
    }
    .finance-form-card {
        border: 1px solid var(--line);
        border-radius: 14px;
        background: var(--surface);
        box-shadow: 0 12px 26px rgba(17, 37, 57, .08);
    }
    .finance-form-card .card-body {
        padding: 20px;
    }
    .section-title {
        color: var(--ink);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 14px;
    }
    .finance-form-space .form-label {
        color: var(--ink-soft);
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 6px;
    }
    .finance-form-space .form-control,
    .finance-form-space .form-select,
    .finance-form-space textarea {
        border-color: #d4dfeb;
        border-radius: 10px;
        min-height: 40px;
    }
    .finance-form-space .form-control:focus,
    .finance-form-space .form-select:focus,
    .finance-form-space textarea:focus {
        border-color: #84a5c8;
        box-shadow: 0 0 0 .2rem rgba(73, 128, 183, .16);
    }
    .submit-btn {
        border: 0;
        background: #18395a;
        color: #fff;
        min-height: 42px;
        border-radius: 10px;
        font-weight: 600;
        letter-spacing: .3px;
    }
    .submit-btn:hover {
        background: #102a43;
        color: #fff;
    }
    .back-btn {
        min-height: 42px;
        border-radius: 10px;
        font-weight: 600;
    }
    .mandatory-note {
        color: #a62f2f;
        font-size: .86rem;
        margin-bottom: 10px;
    }
    @media (max-width: 767px) {
        .entry-header {
            padding: 16px;
        }
        .entry-header h2 {
            font-size: 1.45rem;
        }
        .finance-form-card .card-body {
            padding: 15px;
        }
    }
</style>

<div class="finance-form-space">
    <div class="entry-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2>{{ $action }} Transaction</h2>
                <p>Record and maintain financial movements with complete audit-ready information.</p>
            </div>
            @if($isEdit)
                <div class="text-md-end">
                    <span class="meta-chip me-2">SL NO: {{ $row->sl_no }}</span>
                    <span class="meta-chip">TXN NO: {{ $row->txn_no }}</span>
                </div>
            @else
                <span class="meta-chip">Txn No auto-generates in 8 digits</span>
            @endif
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

    <div class="card finance-form-card">
        <div class="card-body">
            <div class="section-title">Transaction Information</div>
            <div class="mandatory-note">Fields marked with * are mandatory.</div>
            <form method="POST" action="" class="row g-3">
                @csrf
                <input type="hidden" name="created_by" value="<?= session('user_data')['user_id'] ?>">
                <div class="col-md-4">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="type" id="type" required>
                        <option value="" selected>Select Type</option>
                        <option value="INCOME" {{ (($type == 'INCOME')?'selected':'') }}>INCOME</option>
                        <option value="EXPENSE" {{ (($type == 'EXPENSE')?'selected':'') }}>EXPENSE</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="transaction_timestamp" class="form-label">Transaction Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local"
                           class="form-control"
                           name="transaction_timestamp"
                           id="transaction_timestamp"
                           value="{{ $transactionTimestamp }}"
                           required>
                </div>
                <div class="col-md-4">
                    <label for="transaction_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number"
                           step="1"
                           min="1"
                           class="form-control"
                           name="transaction_amount"
                           id="transaction_amount"
                           placeholder="0.00"
                           value="{{ $transactionAmount }}"
                           required>
                </div>
                <div class="col-md-4">
                    <label for="unit_id" class="form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select" name="unit_id" id="unit_id" required>
                        <option value="" {{ (($unitId === '') ? 'selected' : '') }}>Select Unit</option>
                        @foreach($units as $loop_row)
                            <option value="{{ $loop_row->id }}" {{ ((string)$unitId === (string)$loop_row->id) ? 'selected' : '' }}>
                                {{ $loop_row->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                    <select class="form-select" name="branch_id" id="branch_id" required>
                        <option value="" {{ (($branchId === '') ? 'selected' : '') }}>Select Branch</option>
                        @foreach($branches as $loop_row)
                            <option class="branch unit{{ $loop_row->unit_id }}" value="{{ $loop_row->id }}" {{ ((string)$branchId === (string)$loop_row->id) ? 'selected' : '' }}>
                                {{ $loop_row->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select class="form-select" name="payment_mode" id="payment_mode" required>
                        <option value="" {{ (($paymentMode === '') ? 'selected' : '') }}>Select Payment Mode</option>
                        <option value="Cash" {{ ((string)$paymentMode === 'Cash') ? 'selected' : '' }}>Cash</option>
                        <option value="Online" {{ ((string)$paymentMode === 'Online') ? 'selected' : '' }}>Online</option>
                        <option value="Cheque" {{ ((string)$paymentMode === 'Cheque') ? 'selected' : '' }}>Cheque</option>
                    </select>
                </div>

                <div class="col-md-4 payment-reference-group {{ (!in_array($paymentMode, ['Online', 'Cheque'])) ? 'd-none' : '' }}" id="payment_reference_group">
                    <label for="payment_reference" class="form-label">Payment Reference</label>
                    <input type="text"
                           class="form-control"
                           name="payment_reference"
                           id="payment_reference"
                           value="{{ $paymentReference }}"
                           placeholder="UTR, cheque number, transaction id"
                           {{ (!in_array($paymentMode, ['Online', 'Cheque'])) ? 'disabled' : '' }}>
                </div>

                <div class="col-md-4">
                    <label for="particulars" class="form-label">Particulars <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="particulars" id="particulars" rows="4" placeholder="Enter a clear transaction description..." required>{{ $particulars }}</textarea>
                </div>

                <div class="col-4">
                    <label for="note" class="form-label">Note</label>
                    <textarea class="form-control" name="note" id="note" rows="4" placeholder="Optional internal note">{{ $note }}</textarea>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 justify-content-end mt-2">
                    <a href="{{ url('finance/list') }}" class="btn btn-outline-secondary back-btn px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn submit-btn px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ (($action == 'Edit')?'Update':'Save') }} Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script>
        $(function () {
            function bindUnitWiseBranch(unitId, branchSelector, optionClass, selectedBranch) {
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

            function togglePaymentReference(paymentMode) {
                var group = $('#payment_reference_group');
                var input = $('#payment_reference');
                var shouldShow = (paymentMode === 'Online' || paymentMode === 'Cheque');

                if (shouldShow) {
                    group.removeClass('d-none');
                    input.prop('disabled', false);
                } else {
                    group.addClass('d-none');
                    input.prop('disabled', true);
                }
            }

            bindUnitWiseBranch($('#unit_id').val(), '#branch_id', 'branch', $('#branch_id').val());
            togglePaymentReference($('#payment_mode').val());

            $('#unit_id').on('change', function () {
                bindUnitWiseBranch($(this).val(), '#branch_id', 'branch', '');
            });

            $('#payment_mode').on('change', function () {
                togglePaymentReference($(this).val());
            });
        });
    </script>
@endsection
