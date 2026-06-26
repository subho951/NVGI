@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    .leave-type-space {
        --ink: #183247;
        --muted: #66788a;
        --line: #dbe5ee;
        --brand: #1f5f73;
        --brand-soft: #e8f4f6;
    }

    .leave-type-header,
    .leave-type-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .leave-type-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .leave-type-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .leave-type-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .btn-leave-primary {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        border-radius: 6px;
        font-weight: 600;
    }

    .btn-leave-primary:hover {
        background: #174b5b;
        border-color: #174b5b;
        color: #fff;
    }

    .leave-type-card .card-body {
        padding: 18px;
    }

    .leave-table thead th {
        background: var(--brand-soft);
        color: var(--ink);
        font-size: .78rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .leave-table td {
        vertical-align: middle;
    }

    .status-pill {
        display: inline-flex;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        padding: 5px 11px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-active {
        background: #e5f7ed;
        color: #167648;
    }

    .status-inactive {
        background: #fff0df;
        color: #a05b10;
    }

    .leave-action {
        font-weight: 600;
        margin-right: 8px;
        white-space: nowrap;
    }
</style>

<div class="leave-type-space">
    <div class="leave-type-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="leave-type-title">Leave Types</h2>
                <p class="leave-type-subtitle">Manage leave type names, descriptions, and active status.</p>
            </div>
            <!-- <a href="{{ url($controllerRoute . '/add') }}" class="btn btn-leave-primary px-3">
                <i class="fa-solid fa-plus me-1"></i> Add Leave Type
            </a> -->
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

    <div class="card leave-type-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-bordered table-hover leave-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Leave Type Name</th>
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
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->description ?: '--' }}</td>
                                    <td>
                                        @if((int) $row->status === 1)
                                            <span class="status-pill status-active">Active</span>
                                        @else
                                            <span class="status-pill status-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url($controllerRoute . '/edit/' . Helper::encoded($row->id)) }}" class="leave-action text-primary" title="Edit Leave Type">Edit</a>
                                        @if((int) $row->status === 1)
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to deactivate this leave type?')" class="leave-action text-success" title="Deactivate Leave Type">Active</a>
                                        @else
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to activate this leave type?')" class="leave-action text-warning" title="Activate Leave Type">Inactive</a>
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
