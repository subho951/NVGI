@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    .form-check-input:checked {
        background-color: #1e293b;
        border-color: #1e293b;
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

<?php
if ($row) {
    $sales_person_id = $row->sales_person_id;
    $student_name = $row->student_name;
    $guardian_name = $row->guardian_name;
    $phone = $row->phone;
    $remarks1 = $row->remarks1;
    $remarks2 = $row->remarks2;
    $remarks3 = $row->remarks3;
    $remarks4 = $row->remarks4;
    $remarks5 = $row->remarks5;
} else {
    $sales_person_id = '';
    $student_name = '';
    $guardian_name = '';
    $phone = '';
    $remarks1 = '';
    $remarks2 = '';
    $remarks3 = '';
    $remarks4 = '';
    $remarks5 = '';
}
?>

<h6 class="text-center alert alert-info alert-sm py-2 px-2"><?= $action ?> <?= $module['title'] ?></h6>
<span class="text-danger">Star (*) marks fields are mandatory</span>
<form class="row g-3" method="POST" enctype="multipart/form-data" action="">
    @csrf

    <div class="col-md-6">
        <label for="sales_person_id">Sales Person <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="sales_person_id" id="sales_person_id" required>
            <option selected value="">Select</option>
            <?php if($salesPersons){ foreach($salesPersons as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($sales_person_id == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-6">
        <label for="student_name">Student Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="student_name" id="student_name" placeholder="Student Name" value="<?= $student_name ?>" required>
    </div>

    <div class="col-md-4">
        <label for="guardian_name">Guardian Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="guardian_name" id="guardian_name" placeholder="Guardian Name" value="<?= $guardian_name ?>" required>
    </div>
    <div class="col-md-4">
        <label for="phone">Phone <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="phone" id="phone" placeholder="Phone" value="<?= $phone ?>" minlength="10" maxlength="10" onkeypress="return isNumber(event)" required>
    </div>
    <div class="col-md-4">
        <label for="remarks1">Remarks 1 <span class="text-danger">*</span></label>
        <textarea class="form-control form-control-sm" name="remarks1" id="remarks1" placeholder="Remarks 1" rows="3" required><?= $remarks1 ?></textarea>
    </div>

    <div class="col-md-6">
        <label for="remarks2">Remarks 2</label>
        <textarea class="form-control form-control-sm" name="remarks2" id="remarks2" placeholder="Remarks 2" rows="3"><?= $remarks2 ?></textarea>
    </div>
    <div class="col-md-6">
        <label for="remarks3">Remarks 3</label>
        <textarea class="form-control form-control-sm" name="remarks3" id="remarks3" placeholder="Remarks 3" rows="3"><?= $remarks3 ?></textarea>
    </div>

    <div class="col-md-6">
        <label for="remarks4">Remarks 4</label>
        <textarea class="form-control form-control-sm" name="remarks4" id="remarks4" placeholder="Remarks 4" rows="3"><?= $remarks4 ?></textarea>
    </div>
    <div class="col-md-6">
        <label for="remarks5">Remarks 5</label>
        <textarea class="form-control form-control-sm" name="remarks5" id="remarks5" placeholder="Remarks 5" rows="3"><?= $remarks5 ?></textarea>
    </div>

    <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
        <button type="submit" class="btn btn-success btn-sm w-100"><?= $action ?></button>
    </div>
</form>
@endsection
@section('scripts')
    <script>
        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }
    </script>
@endsection