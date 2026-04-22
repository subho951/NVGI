@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

    .bank-space {
        --ink: #13283d;
        --ink-soft: #58718b;
        --surface: #ffffff;
        --line: #d9e4ef;
        --active: #13814d;
        --blocked: #a93b2d;
        font-family: 'Sora', sans-serif;
    }

    .bank-header-card,
    .bank-card {
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--surface);
        box-shadow: 0 12px 26px rgba(17, 37, 57, .08);
    }

    .bank-header-card {
        background: linear-gradient(135deg, #13283d 0%, #21415f 55%, #2b5b84 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(17, 37, 57, .18);
        padding: 20px 24px;
        margin-bottom: 18px;
    }

    .bank-header-card h2 {
        font-family: 'Playfair Display', serif;
        margin: 0;
        font-size: 1.8rem;
    }

    .bank-header-card p {
        margin: 8px 0 0;
        opacity: .9;
        font-size: .92rem;
    }

    .btn-bank {
        border: 0;
        border-radius: 10px;
        min-height: 40px;
        font-weight: 600;
        letter-spacing: .2px;
    }

    .btn-bank-add {
        background: #caa35a;
        color: #1d2531;
    }

    .btn-bank-add:hover {
        background: #bc9446;
        color: #111;
    }

    .bank-card .card-body {
        padding: 18px;
    }

    .bank-title {
        color: var(--ink);
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 14px;
    }

    .bank-table thead th {
        white-space: nowrap;
        border-bottom: 0;
        background: #172e45;
        color: #fff;
        font-size: .78rem;
        letter-spacing: .5px;
        text-transform: uppercase;
        padding: .78rem .62rem;
    }

    .bank-table td {
        vertical-align: middle;
        font-size: .9rem;
        color: #203549;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 5px 11px;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-active {
        background: rgba(19, 129, 77, .12);
        color: var(--active);
    }

    .status-blocked {
        background: rgba(169, 59, 45, .12);
        color: var(--blocked);
    }

    .action-link {
        margin: 0 4px;
        font-size: .96rem;
        white-space: nowrap;
    }

    @media (max-width: 767px) {
        .bank-header-card {
            padding: 16px;
        }

        .bank-header-card h2 {
            font-size: 1.4rem;
        }

        .bank-card .card-body {
            padding: 14px;
        }
    }
</style>

<div class="bank-space">
    <div class="bank-header-card">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h2>Bank Accounts</h2>
                <p>Maintain bank details and quickly switch accounts between active and blocked states.</p>
            </div>
            <a href="{{ url($controllerRoute . '/add') }}" class="btn btn-bank btn-bank-add px-4">
                <i class="fa-solid fa-plus me-1"></i> Add Bank Account
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

    <div class="card bank-card">
        <div class="card-body">
            <div class="bank-title">Bank Account Register</div>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered bank-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bank Name</th>
                            <th>Branch</th>
                            <th>Account No.</th>
                            <th>IFSC Code</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($rows && count($rows) > 0)
                            @php $sl = 1; @endphp
                            @foreach($rows as $row)
                                @php
                                    $encodedId = Helper::encoded($row->id);
                                    $statusUrl = $controllerRoute . '/change-status/';
                                @endphp
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>{{ $row->bank_name ?: '--' }}</td>
                                    <td>{{ $row->bank_branch ?: '--' }}</td>
                                    <td class="font-monospace">{{ $row->account_no ?: '--' }}</td>
                                    <td class="font-monospace">{{ $row->ifsc_code ?: '--' }}</td>
                                    <td>{{ $row->account_type ? ucfirst(strtolower($row->account_type)) : '--' }}</td>
                                    <td>
                                        @if($row->status == 1)
                                            <span class="status-pill status-active">Active</span>
                                        @else
                                            <span class="status-pill status-blocked">Blocked</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url($controllerRoute . '/edit/' . Helper::encoded($row->id)) }}" class="action-link text-primary" title="Edit Bank Account">Edit</a>
                                        |
                                        @if($row->status == 1)
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to deactivate this bank account?')" class="action-link text-success" title="Deactivate Bank Account">Active</a>
                                        @else
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to activate this bank account?')" class="action-link text-warning" title="Activate Bank Account">Blocked</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
