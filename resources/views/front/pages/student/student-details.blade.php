<?php
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Medium;
use App\Models\Board;
?>
<div class="modal-header">
    <h1 class="modal-title fs-5" id="studentDetailsLabel"><?= $student->first_name.' '. $student->middle_name . ' ' . $student->last_name ?></h1>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="text-center mb-3">
        @if($student->photo)
        <img src="{{ config('constants.app_url') . config('constants.uploads_url_path') . $student->photo }}"
            style="width:70px;height:80px;border:1px solid #ccc;">
        @else
        <img src="https://placehold.co/300x200"
            style="width:70px;height:80px;border:1px solid #ccc;">
        @endif
    </div>
    <table class="table table-bordered align-middle">
        <tr>
            <td>ID : {{ $student->student_id_serial }}</td>
            <td>Unit : {{ $student->unit_name }}</td>
            <td>Branch : {{ $student->branch_name }}</td>
            <td>Session : {{ $student->session_name }}</td>
            <td>
                Admission Fees : {{ $student->admission_fees }}<br>
                Admission Date : {{ date('d-m-Y', strtotime($student->admission_date)) }}
            </td>
            <td>Gender : {{ $student->gender }}</td>
        </tr>
        <tr>
            <td>Religion : {{ $student->religion_name }}</td>
            <td>Caste : {{ $student->caste }}</td>
            <td>DOB : {{ date('d-m-Y', strtotime($student->dob)) }}</td>
            <td>PH : <?= (($student->is_ph) ? 'Yes' : 'No') ?></td>
            <td>Address : {{ $student->permanent_address }}</td>
            <td>Pincode : {{ $student->permanent_pincode }}</td>
        </tr>
        <?php
        if($student->unit_id == 1){
            // vhs
            $class_name = $student->class_name;
            $vhs_daycare = (($student->vhs_daycare)?'Yes':'No');
            $board_name = '';
            $subject_name = '';
            $tsa_medium = '';
        } else {
            // tsa
            $class_name = $student->class_name;

            $getBoard = Board::select('name')->where('id', '=', $student->tsa_board)->first();
            $board_name = (($getBoard)?$getBoard->name:'');

            $subject = [];
            $tsa_subjects = json_decode($student->tsa_subjects);
            if(!empty($tsa_subjects)){
                for($k=0;$k<count($tsa_subjects);$k++){
                    $getSubject = Subject::select('name')->where('id', '=', $tsa_subjects[$k])->first();
                    $subject[] = (($getSubject)?$getSubject->name:'');
                }
            }
            $subject_name = implode(', ', $subject);
            
            $getMedium = Medium::select('name')->where('id', '=', $student->tsa_medium)->first();
            $tsa_medium = (($getMedium)?$getMedium->name:'');
        }
        ?>
        <?php if($student->unit_id == 1){?>
            <tr>
                <td>Admitted (VHS) : <?= $class_name ?></td>
                <td>Daycare : <?= $vhs_daycare ?></td>
                <td>Admitted (TSA) : NA</td>
                <td>Board (TSA) : NA</td>
                <td>Subject (TSA) : NA</td>
                <td>Medium (TSA) : NA</td>
            </tr>
        <?php } else {?>
            <tr>
                <td>Admitted (VHS) : NA</td>
                <td>Daycare : NA</td>
                <td>Admitted (TSA) : <?= $class_name ?></td>
                <td>Board (TSA) : <?= $board_name ?></td>
                <td>Subject (TSA) : <?= $subject_name ?></td>
                <td>Medium (TSA) : <?= $tsa_medium ?></td>
            </tr>
        <?php }?>
        <tr>
            <td>Father : {{ $student->father_name }}</td>
            <td>Occupation : {{ $student->father_occupation }}</td>
            <td>Mobile : {{ $student->father_mobile }}</td>
            <td>Mother : {{ $student->mother_name }}</td>
            <td>Occupation : {{ $student->mother_occupation }}</td>
            <td>Mobile : {{ $student->mother_mobile }}</td>
        </tr>
        <tr>

            <td>Emergency : {{ $student->emergency_name }}</td>
            <td>Mobile : {{ $student->emergency_phone }}</td>
            <td>Relation : {{ $student->emergency_relation }}</td>
            <td>Source : {{ $student->source_name }}</td>
            <td>Blood Group : {{ $student->blood_group }}</td>
            <td>Monthly Fees : {{ $student->monthly_fees }}</td>
        </tr>
    </table>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-success btn-sm">Print</button>
</div>