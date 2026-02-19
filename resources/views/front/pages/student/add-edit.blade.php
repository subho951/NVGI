@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
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
if($row){
    $unit_id = $row->unit_id;
    $branch_id = $row->branch_id;
    $session_id = $row->session_id;
    $admission_date = $row->admission_date;
    $first_name = $row->first_name;
    $middle_name = $row->middle_name;
    $last_name = $row->last_name;
    $gender = $row->gender;
    $religion_id = $row->religion_id;
    $caste = $row->caste;
    $dob = $row->dob;
    $is_ph = $row->is_ph;
    $permanent_address = $row->permanent_address;
    $permanent_pincode = $row->permanent_pincode;
    $vhs_class_id = $row->vhs_class_id;
    $vhs_daycare = $row->vhs_daycare;
    $tsa_class_id = $row->tsa_class_id;
    $tsa_board = $row->tsa_board;
    $tsa_subjects = (($row->tsa_subjects != '')?json_decode($row->tsa_subjects):[]);
    $tsa_medium = $row->tsa_medium;
    $father_name = $row->father_name;
    $father_occupation = $row->father_occupation;
    $father_mobile = $row->father_mobile;
    $mother_name = $row->mother_name;
    $mother_occupation = $row->mother_occupation;
    $mother_mobile = $row->mother_mobile;
    $emergency_name = $row->emergency_name;
    $emergency_phone = $row->emergency_phone;
    $emergency_relation = $row->emergency_relation;
    $know_about_us = $row->know_about_us;
    $photo = $row->photo;
} else {
    $unit_id = '';
    $branch_id = '';
    $session_id = '';
    $admission_date = '';
    $first_name = '';
    $middle_name = '';
    $last_name = '';
    $gender = '';
    $religion_id = '';
    $caste = '';
    $dob = '';
    $is_ph = '';
    $permanent_address = '';
    $permanent_pincode = '';
    $vhs_class_id = '';
    $vhs_daycare = '';
    $tsa_class_id = '';
    $tsa_board = '';
    $tsa_subjects = [];
    $tsa_medium = '';
    $father_name = '';
    $father_occupation = '';
    $father_mobile = '';
    $mother_name = '';
    $mother_occupation = '';
    $mother_mobile = '';
    $emergency_name = '';
    $emergency_phone = '';
    $emergency_relation = '';
    $know_about_us = '';
    $photo = '';
}
?>

<h6 class="text-center alert alert-info alert-sm py-2 px-2"><?= $action ?> <?= $module['title'] ?></h6>
<span class="text-danger">Star (*) marks fields are mandatory</span>
<form class="row g-3" method="POST" enctype="multipart/form-data" action="">
    @csrf
    <div class="col-md-3">
        <label for="unit_id">Unit <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="unit_id" id="unit_id" required>
            <option selected value="">Select</option>
            <?php if($units){ foreach($units as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($loop_row->id == $unit_id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="branch_id">Branch <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="branch_id" id="branch_id" required>
            <option selected value="">Select</option>
            <?php if($branches){ foreach($branches as $loop_row){?>
                <option class="branch unit<?= $loop_row->unit_id ?>" value="<?= $loop_row->id ?>" <?= (($loop_row->id == $branch_id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="session_id">Session <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="session_id" id="session_id" required>
            <option selected value="">Select</option>
            <?php if($sessions){ foreach($sessions as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($loop_row->id == $session_id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="admission_date">Date of Admission <span class="text-danger">*</span></label>
        <input type="Date" class="form-control form-control-sm" name="admission_date" id="admission_date" placeholder="First Name" value="<?= $admission_date ?>" required>
    </div>    

    <div class="col-md-2">
        <label for="first_name">First Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="first_name" id="first_name" placeholder="First Name" value="<?= $first_name ?>" required>
    </div>
    <div class="col-md-2">
        <label for="middle_name">Middle Name</label>
        <input type="text" class="form-control form-control-sm" name="middle_name" id="middle_name" placeholder="Middle Name" value="<?= $middle_name ?>">
    </div>
    <div class="col-md-2">
        <label for="last_name">Last Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="last_name" id="last_name" placeholder="Last Name" value="<?= $last_name ?>" required>
    </div>
    <div class="col-md-2">
        <label for="gender">Gender <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="gender" id="gender" required>
            <option selected value="">Select</option>
            <option value="Male" <?= (($gender == 'Male')?'selected':'') ?>>Male</option>
            <option value="Female" <?= (($gender == 'Female')?'selected':'') ?>>Female</option>
            <option value="Others" <?= (($gender == 'Others')?'selected':'') ?>>Others</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="religion_id">Religion <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="religion_id" id="religion_id" required>
            <option selected value="">Select</option>
            <?php if($religions){ foreach($religions as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($religion_id == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-2">
        <label for="caste">Caste <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="caste" id="caste" required>
            <option selected value="">Select</option>
            <option value="General" <?= (($caste == 'General')?'selected':'') ?>>General</option>
            <option value="SC" <?= (($caste == 'SC')?'selected':'') ?>>SC</option>
            <option value="ST" <?= (($caste == 'ST')?'selected':'') ?>>ST</option>
            <option value="OBC-A" <?= (($caste == 'OBC-A')?'selected':'') ?>>OBC-A</option>
            <option value="OBC-B" <?= (($caste == 'OBC-B')?'selected':'') ?>>OBC-B</option>
        </select>
    </div>

    <div class="col-md-2">
        <label for="dob">Date of Birth <span class="text-danger">*</span></label>
        <input type="date" class="form-control form-control-sm" name="dob" id="dob" max="<?= date('Y-m-d') ?>" placeholder="Date of Birth" value="<?= $dob ?>" required>
    </div>
    <div class="col-md-2">
        <label for="is_ph">Is PH ? <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="is_ph" id="is_ph" required>
            <option selected value="">Select</option>
            <option value="0" <?= (($is_ph == 0)?'selected':'') ?>>No</option>
            <option value="1" <?= (($is_ph == 1)?'selected':'') ?>>Yes</option>
        </select>
    </div>
    <div class="col-md-6">
        <label for="permanent_address">Permanent Address <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="permanent_address" id="permanent_address" placeholder="Permanent Address" value="<?= $permanent_address ?>" required>
    </div>
    <div class="col-md-2">
        <label for="permanent_pincode">Pincode <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="permanent_pincode" id="permanent_pincode" placeholder="Pincode" onkeypress="return isNumber(event)" minlength="6" maxlength="6" value="<?= $permanent_pincode ?>" required>
    </div>

    <div class="col-md-6 classunit vhs">
        <label for="vhs_class_id">Admitted in (VHS Only)</label>
        <select class="form-select form-select-sm" name="vhs_class_id" id="vhs_class_id">
            <option selected value="">Select</option>
            <?php if($vhs_classes){ foreach($vhs_classes as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($vhs_class_id == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-6 classunit vhs">
        <label for="vhs_daycare">Day Care (VHS Only)</label>
        <select class="form-select form-select-sm" name="vhs_daycare" id="vhs_daycare">
            <option selected value="">Select</option>
            <option value="0" <?= (($vhs_daycare == 0)?'selected':'') ?>>No</option>
            <option value="1" <?= (($vhs_daycare == 1)?'selected':'') ?>>Yes</option>
        </select>
    </div>

    <div class="col-md-3 classunit tsa">
        <label for="tsa_class_id">Admitted in (TSA Only)</label>
        <select class="form-select form-select-sm" name="tsa_class_id" id="tsa_class_id">
            <option selected value="">Select</option>
            <?php if($tsa_classes){ foreach($tsa_classes as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($tsa_class_id == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-3 classunit tsa">
        <label for="tsa_board">Board (TSA Only)</label>
        <select class="form-select form-select-sm" name="tsa_board" id="tsa_board">
            <option selected value="">Select</option>
            <?php if($boards){ foreach($boards as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($tsa_board == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-3 classunit tsa">
        <label for="tsa_subjects">Subject (TSA Only)</label>
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100" type="button" id="multiSelectDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Select
            </button>
            <ul class="dropdown-menu w-100" aria-labelledby="multiSelectDropdown">
                <?php if($subjects){ foreach($subjects as $loop_row){?>
                    <li><label class="dropdown-item"><input type="checkbox" name="tsa_subjects[]" value="<?= $loop_row->id ?>" class="form-check-input me-2" <?= ((in_array($loop_row->id, $tsa_subjects))?'checked':'') ?>> <?= $loop_row->name ?></label></li>
                <?php } } ?>
            </ul>
        </div>
    </div>
    <div class="col-md-3 classunit tsa">
        <label for="tsa_medium">Medium (TSA Only)</label>
        <select class="form-select form-select-sm" name="tsa_medium" id="tsa_medium">
            <option selected value="">Select</option>
            <?php if($mediums){ foreach($mediums as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($tsa_medium == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>

    <div class="col-md-2">
        <label for="father_name">Name of the Father <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="father_name" id="father_name" placeholder="Name of the Father" value="<?= $father_name ?>" required>
    </div>
    <div class="col-md-2">
        <label for="father_occupation">Occupation <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="father_occupation" id="father_occupation" placeholder="Occupation" value="<?= $father_occupation ?>" required>
    </div>
    <div class="col-md-2">
        <label for="father_mobile">Mobile Number <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="father_mobile" id="father_mobile" placeholder="Mobile Number" onkeypress="return isNumber(event)" minlength="10" maxlength="10" value="<?= $father_mobile ?>" required>
    </div>
    <div class="col-md-2">
        <label for="mother_name">Name of the Mother</label>
        <input type="text" class="form-control form-control-sm" name="mother_name" id="mother_name" placeholder="Name of the Mother" value="<?= $mother_name ?>">
    </div>
    <div class="col-md-2">
        <label for="mother_occupation">Occupation</label>
        <input type="text" class="form-control form-control-sm" name="mother_occupation" id="mother_occupation" placeholder="Occupation" value="<?= $mother_occupation ?>">
    </div>
    <div class="col-md-2">
        <label for="mother_mobile">Mobile Number</label>
        <input type="text" class="form-control form-control-sm" name="mother_mobile" id="mother_mobile" placeholder="Mobile Number" onkeypress="return isNumber(event)" minlength="10" maxlength="10" value="<?= $mother_mobile ?>">
    </div>

    <div class="col-md-2">
        <label for="emergency_name">Emergency Info <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="emergency_name" id="emergency_name" placeholder="Name" value="<?= $emergency_name ?>" required>
    </div>
    <div class="col-md-2">
        <label for="emergency_phone">&nbsp;</label>
        <input type="text" class="form-control form-control-sm" name="emergency_phone" id="emergency_phone" placeholder="Contact Number" onkeypress="return isNumber(event)" minlength="10" maxlength="10" value="<?= $emergency_phone ?>" required>
    </div>
    <div class="col-md-2">
        <label for="emergency_relation">&nbsp;</label>
        <input type="text" class="form-control form-control-sm" name="emergency_relation" id="emergency_relation" placeholder="Relation" value="<?= $emergency_relation ?>" required>
    </div>
    <div class="col-md-3">
        <label for="know_about_us">How did they know about us? <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="know_about_us" id="know_about_us" required>
            <option selected value="">Select</option>
            <?php if($knowAbouts){ foreach($knowAbouts as $loop_row){?>
                <option value="<?= $loop_row->id ?>" <?= (($know_about_us == $loop_row->id)?'selected':'') ?>><?= $loop_row->name ?></option>
            <?php } } ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="photo">Upload Photo (jpg or png)</label>
        <input type="file" class="form-control form-control-sm" name="photo" id="photo" placeholder="Upload Photo (jpg or png)">
        <p class="mt-2">
            <?php if($photo != ''){?>
                <img src="<?= config('constants.app_url') . config('constants.uploads_url_path') . $photo ?>" style="width:100px; height:100px; border:1px solid #CCCCCC;border-radius:5px;">
            <?php } else {?>
                <img src="https://placehold.co/300x200" style="width:100px; height:100px; border:1px solid #CCCCCC;border-radius:5px;">
            <?php }?>
        </p>
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
    <script>
        $(function(){
            $('.classunit').hide();

            var unit_id = '<?= $unit_id ?>';
            if(unit_id == 1){
                $('.vhs').show();
                $('.tsa').hide();
            } else {
                $('.vhs').hide();
                $('.tsa').show();
            }

            $('#unit_id').on('change', function(){
                var unit_id = $('#unit_id').val();
                if(unit_id == 1){
                    $('.vhs').show();
                    $('.tsa').hide();
                } else {
                    $('.vhs').hide();
                    $('.tsa').show();
                }

                $('#branch_id').val('');
                $('#branch_id .branch').hide();
                $('#branch_id .unit' + unit_id).show();
            });
        })
    </script>
@endsection