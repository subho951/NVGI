@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];

$isEdit = (!empty($row));
$bankName = old('bank_name', (($isEdit) ? $row->bank_name : ''));
$bankBranch = old('bank_branch', (($isEdit) ? $row->bank_branch : ''));
$accountNo = old('account_no', (($isEdit) ? $row->account_no : ''));
$ifscCode = old('ifsc_code', (($isEdit) ? $row->ifsc_code : ''));
$accountType = old('account_type', (($isEdit) ? $row->account_type : ''));
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

    .bank-form-space {
        --ink: #172b40;
        --ink-soft: #5b7086;
        --line: #dce6f1;
        --surface: #ffffff;
        font-family: 'Sora', sans-serif;
    }

    .entry-header,
    .bank-form-card {
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--surface);
        box-shadow: 0 12px 26px rgba(17, 37, 57, .08);
    }

    .entry-header {
        background: linear-gradient(125deg, #16293d 0%, #234362 62%, #315b82 100%);
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
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    .bank-form-card .card-body {
        padding: 20px;
    }

    .section-title {
        color: var(--ink);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 14px;
    }

    .bank-form-space .form-label {
        color: var(--ink-soft);
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 6px;
    }

    .bank-form-space .form-control,
    .bank-form-space .form-select {
        border-color: #d4dfeb;
        border-radius: 10px;
        min-height: 40px;
    }

    .bank-form-space .form-control:focus,
    .bank-form-space .form-select:focus {
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

        .bank-form-card .card-body {
            padding: 15px;
        }
    }
</style>

<div class="bank-form-space">
    <div class="entry-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2>{{ $action }} Bank Account</h2>
                <p>Keep bank information accurate so finance records stay clean and easy to manage.</p>
            </div>
            @if($isEdit)
                <span class="meta-chip">Current Status: {{ ((int) $row->status === 1) ? 'Active' : 'Blocked' }}</span>
            @else
                <span class="meta-chip">New bank accounts start in active status</span>
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

    <div class="card bank-form-card">
        <div class="card-body">
            <div class="section-title">Bank Account Information</div>
            <div class="mandatory-note">Fields marked with * are mandatory.</div>
            <form method="POST" action="" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="bank_name" id="bank_name" placeholder="Enter bank name" value="{{ $bankName }}" required>
                </div>

                <div class="col-md-6">
                    <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="account_type" id="account_type" required>
                        <option value="" {{ (($accountType === '') ? 'selected' : '') }}>Select Type</option>
                        <option value="CURRENT" {{ (($accountType === 'CURRENT') ? 'selected' : '') }}>CURRENT</option>
                        <option value="SAVINGS" {{ (($accountType === 'SAVINGS') ? 'selected' : '') }}>SAVINGS</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="bank_branch" class="form-label">Bank Branch <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="bank_branch" id="bank_branch" placeholder="Enter bank branch" value="{{ $bankBranch }}" required>
                </div>

                <div class="col-md-6">
                    <label for="account_no" class="form-label">Account Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="account_no" id="account_no" placeholder="Enter account number" value="{{ $accountNo }}">
                </div>

                <div class="col-md-6">
                    <label for="ifsc_code" class="form-label">IFSC Code <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           name="ifsc_code"
                           id="ifsc_code"
                           placeholder="Enter IFSC code"
                           value="{{ $ifscCode }}"
                           style="text-transform: uppercase;"
                           oninput="this.value = this.value.toUpperCase()"
                           maxlength="11">
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 justify-content-end mt-2">
                    <a href="{{ url($controllerRoute . '/list') }}" class="btn btn-outline-secondary back-btn px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn submit-btn px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ (($action == 'Edit') ? 'Update' : 'Save') }} Bank Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
