@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Models\Unit;
use App\Models\Student;
use App\Helpers\Helper;
?>
<h4 class=" text-center">Dashboard Overview</h4>
<!--<h6 class="mb-4 text-danger">Developer's Note : ID should be : NVGI/{Unit Name}{1st letter of branch}/1,2,3...</h6>-->
<div class="card shadow bg-light mb-4">
    <div class="card-body">
        <div class="row">
            <?php
            if ($branches) {
                foreach ($branches as $branch) {
                    $getUnit = Unit::select('name')->where('id', '=', $branch->unit_id)->first();
                    $student_count = Student::where('branch_id', '=', $branch->id)->where('status', '!=', 3)->count();
            ?>
                    <div class="col-md-3 mb-3">
                        <div class="card shadow dashboard-card bg1 ">
                            <div class="card-body text-center text-white">
                                <h5><?= (($getUnit) ? $getUnit->name : '') ?> <?= $branch->name ?></h5>
                                <h2><?= $student_count ?></h2>
                            </div>
                        </div>
                    </div>
            <?php }
            } ?>
            <?php
            if ($units) {
                foreach ($units as $unit) {
                    $student_count = Student::where('unit_id', '=', $unit->id)->where('status', '!=', 3)->count();
            ?>
                    <div class="col-md-6 mb-3">
                        <div class="card shadow dashboard-card bg4 ">
                            <div class="card-body text-center text-white">
                                <h5>Total <?= $unit->name ?> Students<br /><?= $student_count ?></h5>
                            </div>
                        </div>
                    </div>
            <?php }
            } ?>
        </div>
    </div>
</div>
<div class="card shadow bg-light">
    <div class="card-header">
        <h4 class=" text-center">Entry Record</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <?php
            if ($users) {
                foreach ($users as $user) {
                    $student_count = Student::where('created_by', '=', $user->id)->where('status', '!=', 3)->count();
            ?>
                    <div class="col-md-3 mb-3">
                        <div class="card shadow dashboard-card bg7">
                            <div class="card-body text-center text-white">
                                <h5><?= $user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name ?></h5>
                                <h2><?= $student_count ?></h2>
                            </div>
                        </div>
                    </div>
            <?php }
            } ?>
        </div>
    </div>
</div>
<hr class="my-4">
<h4 class="mb-3 text-center">Analytics</h4>
<div class="row g-3">
    <div class="col-md-6">
        <div class="chart-box shadow">
            <canvas id="branchChart"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-box shadow">
            <canvas id="growthChart"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-box shadow">
            <canvas id="totalChart"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-box shadow">
            <canvas id="staffChart"></canvas>
        </div>
    </div>
</div>
@endsection