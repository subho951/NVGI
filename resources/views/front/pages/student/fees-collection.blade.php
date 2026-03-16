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
</style>
<h2>Manage <?= $module['title'] ?></h2>
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

<div class="card shadow bg-light">
    <!-- <div class="card-header">
        <h5>
            <a href="<?= url('student/add') ?>" class="btn btn-success btn-sm">Add New <?= $module['title'] ?></a>
        </h5>
    </div> -->
    <div class="card-body">
        <h6 class="text-center alert alert-info alert-sm py-2 px-2"><?= $module['title'] ?> Fees Collection</h6>

        <form method="GET" action="<?= url('lead/generate-report') ?>" target="_blank">
            <div class="row" style="border: 1px solid #1e293b24;padding: 10px;border-radius: 10px;margin-bottom: 15px;">
                <div class="col-md-3">
                    <label for="unit_id">Unit</label>
                    <select class="form-select form-select-sm" name="unit_id" id="unit_id" required>
                        <option selected value="">Select</option>
                        <?php if($units){ foreach($units as $loop_row){?>
                            <option value="<?= $loop_row->id ?>"><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="branch_id">Branch</label>
                    <select class="form-select form-select-sm" name="branch_id" id="branch_id" required>
                        <option selected value="">Select</option>
                        <?php if($branches){ foreach($branches as $loop_row){?>
                            <option value="<?= $loop_row->id ?>"><?= $loop_row->name ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="collection_year">Year</label>
                    <select class="form-select form-select-sm" name="collection_year" id="collection_year" required>
                        <option selected value="">Select</option>
                        <?php for($y=date('Y');$y<2020;$y--){?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success btn-sm w-100" style="margin-top: 18px;"><i class="fa-solid fa-search"></i>Search</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle text-center" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Student Info</th>
                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                            <th class="text-center"><?= date("F", mktime(0, 0, 0, $m, 1)) . "<br>"; ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows) {
                        $sl = 1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $sl++ ?></td>
                                <td>
                                    <small><?= $row->student_id_serial ?></small><br>
                                    <?php if ($row->photo != '') { ?>
                                        <img src="<?= config('constants.app_url') . config('constants.uploads_url_path') . $row->photo ?>" style="width:50px; height:50px; border:1px solid #CCCCCC;">
                                    <?php } else { ?>
                                        <img src="{{ config('constants.no_image_avatar') }}" style="width:50px; height:50px; border:1px solid #CCCCCC; border-radius:50%;">
                                    <?php } ?>
                                    <br>
                                    <small><?= $row->full_name ?><br>
                                        <?= $row->father_mobile ?><br>
                                        <!-- <?= $row->unit_name ?><br>
                                        <?= $row->branch_name ?><br> -->
                                        <?= $row->class_name ?></small>
                                </td>
                                <?php for ($m = 1; $m <= 12; $m++) { ?>
                                    <td>
                                        <h6 style="font-size:10px; text-align:left;font-weight:bold;">Payable: 1000.00</h6>
                                        <form method="POST" action="javascript:void();" class="d-flex">
                                            <input type="text"
                                                name="payment_amount"
                                                class="form-control form-control-sm"
                                                placeholder="Amount"
                                                oninput="allowNumberDot(this)">

                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </button>
                                        </form>
                                    </td>
                                <?php } ?>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection
@section('scripts')
<script>
    function validateNumber(input) {
        input.value = input.value
            .replace(/[^0-9.]/g, '') // remove non-numeric
            .replace(/(\..*)\./g, '$1'); // allow only one dot
    }
</script>
@endsection