@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Models\Classes;
use App\Models\Subject;
use App\Models\Medium;
use App\Models\Board;
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .card, .card * {
        visibility: visible;
    }
    .card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    @page {
        size: A4 portrait;
        margin: 10mm;
    }

    body {
        -webkit-print-color-adjust: exact;
    }
}
</style>
<h2><?= $action ?> <?= $module['title'] ?></h2>
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
<div class="card shadow bg-light mb-4">
    <div class="card-body">
        <div class="text-center mb-3">
            <h1 class="modal-title fs-5" id="studentDetailsLabel"><?= $student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name ?></h1>
            @if($student->photo)
            <img src="{{ config('constants.app_url') . config('constants.uploads_url_path') . $student->photo }}" style="width:150px;height:150px;border:1px solid #ccc;" class="img-thumbnail">
            @else
            <img src="{{ config('constants.no_image_avatar') }}" style="width:150px;height:150px;border:1px solid #ccc;" class="img-thumbnail">
            @endif
        </div>
        <table class="table table-bordered align-middle">
            <tr>
                <td><h6 style="font-weight: bold;">ID :</h6> <span>{{ $student->student_id_serial }}</span></td>
                <td><h6 style="font-weight: bold;">Unit :</h6> <span>{{ $student->unit_name }}</span></td>
                <td><h6 style="font-weight: bold;">Branch :</h6> <span>{{ $student->branch_name }}</span></td>
                <td><h6 style="font-weight: bold;">Session :</h6> <span>{{ $student->session_name }}</span></td>
                <td>
                    <h6 style="font-weight: bold;">Admission Fees :</h6> <span>{{ number_format($student->admission_fees,2) }}</span><br>
                    <h6 style="font-weight: bold;">Admission Date :</h6> <span>{{ date('d-m-Y', strtotime($student->admission_date)) }}</span>
                </td>
                <td><h6 style="font-weight: bold;">Gender :</h6> <span>{{ $student->gender }}</span></td>
            </tr>
            <tr>
                <td><h6 style="font-weight: bold;">Religion :</h6> <span>{{ $student->religion_name }}</span></td>
                <td><h6 style="font-weight: bold;">Caste :</h6> <span>{{ $student->caste }}</span></td>
                <td><h6 style="font-weight: bold;">DOB :</h6> <span>{{ date('d-m-Y', strtotime($student->dob)) }}</span></td>
                <td><h6 style="font-weight: bold;">PH :</h6> <span><?= (($student->is_ph) ? 'Yes' : 'No') ?></span></td>
                <td><h6 style="font-weight: bold;">Address :</h6> <span>{{ $student->permanent_address }}</span></td>
                <td><h6 style="font-weight: bold;">Pincode :</h6> <span>{{ $student->permanent_pincode }}</span></td>
            </tr>
            <?php
            if ($student->unit_id == 1) {
                // vhs
                $class_name = $student->class_name;
                $vhs_daycare = (($student->vhs_daycare) ? 'Yes' : 'No');
                $board_name = '';
                $subject_name = '';
                $tsa_medium = '';
            } else {
                // tsa
                $class_name = $student->class_name;

                $getBoard = Board::select('name')->where('id', '=', $student->tsa_board)->first();
                $board_name = (($getBoard) ? $getBoard->name : '');

                $subject = [];
                $tsa_subjects = json_decode($student->tsa_subjects);
                echo 'subject';
                Helper::pr(json_decode($student->tsa_subjects));
                if (!empty($tsa_subjects)) {
                    for ($k = 0; $k < count($tsa_subjects); $k++) {
                        $getSubject = Subject::select('name')->where('id', '=', $tsa_subjects[$k])->first();
                        $subject[] = (($getSubject) ? $getSubject->name : '');
                    }
                }
                $subject_name = implode(', ', $subject);

                $getMedium = Medium::select('name')->where('id', '=', $student->tsa_medium)->first();
                $tsa_medium = (($getMedium) ? $getMedium->name : '');
            }
            ?>
            <?php if ($student->unit_id == 1) { ?>
                <tr>
                    <td><h6 style="font-weight: bold;">Admitted (VHS) :</h6> <span><?= $class_name ?></span></td>
                    <td><h6 style="font-weight: bold;">Daycare :</h6> <span><?= $vhs_daycare ?></span></td>
                    <td><h6 style="font-weight: bold;">Admitted (TSA) :</h6> <span>NA</span></td>
                    <td><h6 style="font-weight: bold;">Board (TSA) :</h6> <span>NA</span></td>
                    <td><h6 style="font-weight: bold;">Subject (TSA) :</h6> <span>NA</span></td>
                    <td><h6 style="font-weight: bold;">Medium (TSA) :</h6> <span>NA</span></td>
                </tr>
            <?php } else { ?>
                <tr>
                    <td><h6 style="font-weight: bold;">Admitted (VHS) :</h6> <span>NA</span></td>
                    <td><h6 style="font-weight: bold;">Daycare :</h6> <span>NA</span></td>
                    <td><h6 style="font-weight: bold;">Admitted (TSA) :</h6> <span><?= $class_name ?></span></td>
                    <td><h6 style="font-weight: bold;">Board (TSA) :</h6> <span><?= $board_name ?></span></td>
                    <td><h6 style="font-weight: bold;">Subject (TSA) :</h6> <span><?= $subject_name ?></span></td>
                    <td><h6 style="font-weight: bold;">Medium (TSA) :</h6> <span><?= $tsa_medium ?></span></td>
                </tr>
            <?php } ?>
            <tr>
                <td><h6 style="font-weight: bold;">Father :</h6> <span>{{ $student->father_name }}</span></td>
                <td><h6 style="font-weight: bold;">Occupation :</h6> <span>{{ $student->father_occupation }}</span></td>
                <td><h6 style="font-weight: bold;">Mobile :</h6> <span>{{ $student->father_mobile }}</span></td>
                <td><h6 style="font-weight: bold;">Mother :</h6> <span>{{ $student->mother_name }}</span></td>
                <td><h6 style="font-weight: bold;">Occupation :</h6> <span>{{ $student->mother_occupation }}</span></td>
                <td><h6 style="font-weight: bold;">Mobile :</h6> <span>{{ $student->mother_mobile }}</span></td>
            </tr>
            <tr>

                <td><h6 style="font-weight: bold;">Emergency :</h6> <span>{{ $student->emergency_name }}</span></td>
                <td><h6 style="font-weight: bold;">Mobile :</h6> <span>{{ $student->emergency_phone }}</span></td>
                <td><h6 style="font-weight: bold;">Relation :</h6> <span>{{ $student->emergency_relation }}</span></td>
                <td><h6 style="font-weight: bold;">Source :</h6> <span>{{ $student->source_name }}</span></td>
                <td><h6 style="font-weight: bold;">Blood Group :</h6> <span>{{ $student->blood_group }}</span></td>
                <td><h6 style="font-weight: bold;">Monthly Fees :</h6> <span>{{ number_format($student->monthly_fees,2) }}</span></td>
            </tr>
        </table>
    </div>
</div>
@endsection
@section('scripts')
    <script>
        window.onload = function () {

            // small delay so content renders properly
            setTimeout(function () {
                window.print();
            }, 500);

        };

        // after print dialog closes
        window.onafterprint = function () {
            window.close();
        };
    </script>
@endsection