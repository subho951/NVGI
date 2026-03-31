@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

    .ledger-space {
        --ink: #14263a;
        --ink-soft: #557089;
        --surface: #ffffff;
        --line: #dbe5ef;
        --active: #12814d;
        --blocked: #a94230;
        font-family: 'Sora', sans-serif;
    }
    .ledger-header-card,
    .ledger-card {
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--surface);
        box-shadow: 0 12px 26px rgba(19, 38, 58, .08);
    }
    .ledger-header-card {
        background: linear-gradient(135deg, #13263a 0%, #21415f 55%, #2c567e 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(19, 38, 58, .18);
        padding: 20px 24px;
        margin-bottom: 18px;
    }
    .ledger-header-card h2 {
        font-family: 'Playfair Display', serif;
        margin: 0;
        font-size: 1.8rem;
    }
    .ledger-header-card p {
        margin: 8px 0 0;
        opacity: .9;
        font-size: .92rem;
    }
    .btn-ledger {
        border: 0;
        border-radius: 10px;
        min-height: 40px;
        font-weight: 600;
        letter-spacing: .2px;
    }
    .btn-ledger-add {
        background: #caa35a;
        color: #1d2531;
    }
    .btn-ledger-add:hover {
        background: #bc9446;
        color: #111;
    }
    .ledger-card .card-body {
        padding: 18px;
    }
    .ledger-title {
        color: var(--ink);
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 14px;
    }
    .ledger-table thead th {
        white-space: nowrap;
        border-bottom: 0;
        background: #172e45;
        color: #fff;
        font-size: .78rem;
        letter-spacing: .5px;
        text-transform: uppercase;
        padding: .78rem .62rem;
    }
    .ledger-table td {
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
        background: rgba(18, 129, 77, .12);
        color: var(--active);
    }
    .status-blocked {
        background: rgba(169, 66, 48, .12);
        color: var(--blocked);
    }
    .action-link {
        margin: 0 4px;
        font-size: .96rem;
        white-space: nowrap;
    }
    @media (max-width: 767px) {
        .ledger-header-card {
            padding: 16px;
        }
        .ledger-header-card h2 {
            font-size: 1.4rem;
        }
        .ledger-card .card-body {
            padding: 14px;
        }
    }
</style>

<div class="ledger-space">
    <div class="ledger-header-card">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h2>Ledgers</h2>
                <p>Maintain finance ledgers with quick activate, deactivate, edit controls.</p>
            </div>
            <a href="{{ url($controllerRoute . '/add') }}" class="btn btn-ledger btn-ledger-add px-4">
                <i class="fa-solid fa-plus me-1"></i> Add Ledger
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

    <div class="card ledger-card">
        <div class="card-body">
            <div class="ledger-title">Ledger Register</div>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered ledger-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Name</th>
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
                                    $deleteUrl = $controllerRoute . '/delete/';
                                    $statusUrl = $controllerRoute . '/change-status/';
                                @endphp
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>{{ $row->type }}</td>
                                    <td>{{ $row->name }}</td>
                                    <td>
                                        @if($row->status == 1)
                                            <span class="status-pill status-active">Active</span>
                                        @else
                                            <span class="status-pill status-blocked">Blocked</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url($controllerRoute . '/edit/' . Helper::encoded($row->id)) }}" class="action-link text-primary" title="Edit Ledger">Edit</a>
                                        |
                                        @if($row->status == 1)
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to deactivate this ledger?')" class="action-link text-success" title="Deactivate Ledger">Active</a>
                                        @else
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to activate this ledger?')" class="action-link text-warning" title="Activate Ledger">Blocked</a>
                                        @endif
                                        |
                                        <!-- <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $deleteUrl ?>', 'This ledger will be removed from the active list. Do you want to proceed?')" class="action-link text-danger" title="Delete Ledger">Delete</a> -->
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-danger text-center py-4">No records found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
