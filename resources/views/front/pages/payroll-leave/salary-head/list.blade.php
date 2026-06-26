@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    .salary-head-space {
        --ink: #183247;
        --muted: #66788a;
        --line: #dbe5ee;
        --brand: #1f5f73;
        --brand-soft: #e8f4f6;
    }

    .salary-head-header,
    .salary-head-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .salary-head-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .salary-head-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .salary-head-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .btn-salary-primary {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        border-radius: 6px;
        font-weight: 600;
    }

    .btn-salary-primary:hover {
        background: #174b5b;
        border-color: #174b5b;
        color: #fff;
    }

    .salary-head-card .card-body {
        padding: 18px;
    }

    .salary-head-table thead th {
        background: var(--brand-soft);
        color: var(--ink);
        font-size: .76rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .salary-head-table td {
        vertical-align: middle;
        font-size: .88rem;
    }

    .salary-pill {
        display: inline-flex;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        padding: 5px 10px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .salary-pill-earning {
        background: #e5f7ed;
        color: #167648;
    }

    .salary-pill-deduction {
        background: #ffe8e5;
        color: #b94032;
    }

    .status-active {
        background: #e5f7ed;
        color: #167648;
    }

    .status-inactive {
        background: #fff0df;
        color: #a05b10;
    }

    .payslip-yes {
        background: #e8f4f6;
        color: #1f5f73;
    }

    .payslip-no {
        background: #f2f4f7;
        color: #66788a;
    }

    .formula-chip {
        background: #f6f9fb;
        border: 1px solid #edf2f6;
        border-radius: 6px;
        color: var(--ink);
        display: inline-block;
        font-weight: 600;
        padding: 5px 8px;
        white-space: nowrap;
    }

    .salary-action {
        font-weight: 600;
        margin-right: 8px;
        white-space: nowrap;
    }
</style>

<div class="salary-head-space">
    <div class="salary-head-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="salary-head-title">Salary Heads</h2>
                <p class="salary-head-subtitle">Manage earning and deduction heads with payroll calculation formulas.</p>
            </div>
            <a href="{{ url($controllerRoute . '/add') }}" class="btn btn-salary-primary px-3">
                <i class="fa-solid fa-plus me-1"></i> Add Salary Head
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

    <div class="card salary-head-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-bordered table-hover salary-head-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Salary Head</th>
                            <th>Calculation Type</th>
                            <th>Formula</th>
                            <th>Payslip Show</th>
                            <th>Description</th>
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
                                    <td>
                                        <span class="salary-pill {{ $row->type === 'DEDUCTION' ? 'salary-pill-deduction' : 'salary-pill-earning' }}">
                                            {{ $row->type_label }}
                                        </span>
                                    </td>
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->calculation_type_label }}</td>
                                    <td><span class="formula-chip">{{ $row->formula_label }}</span></td>
                                    <td>
                                        @if($row->is_payslip_show)
                                            <span class="salary-pill payslip-yes">YES</span>
                                        @else
                                            <span class="salary-pill payslip-no">NO</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->short_description ?: '--' }}</td>
                                    <td>
                                        @if((int) $row->status === 1)
                                            <span class="salary-pill status-active">Active</span>
                                        @else
                                            <span class="salary-pill status-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url($controllerRoute . '/edit/' . Helper::encoded($row->id)) }}" class="salary-action text-primary" title="Edit Salary Head">Edit</a>
                                        @if((int) $row->status === 1)
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to deactivate this salary head?')" class="salary-action text-success" title="Deactivate Salary Head">Active</a>
                                        @else
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to activate this salary head?')" class="salary-action text-warning" title="Activate Salary Head">Inactive</a>
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
