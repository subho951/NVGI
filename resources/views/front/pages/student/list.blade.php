@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    a{
        text-decoration: none;
    }
    .student-page-title{
        font-weight: 700;
        color: #1f2937;
        letter-spacing: 0.2px;
    }
    .student-list-card{
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        background: linear-gradient(180deg, #f6fbff 0%, #ffffff 35%);
    }
    .student-list-card .card-header{
        background: linear-gradient(90deg, #ffffff 0%, #eaf4ff 100%);
        border-bottom: 1px solid #d8e6f4;
    }
    .student-list-card .card-body{
        padding-top: 1rem;
    }
    .student-list-note{
        border: 1px solid #c8def6;
        background: linear-gradient(90deg, #dbeafe 0%, #e0f2fe 100%);
        color: #0f172a;
        font-weight: 600;
    }
    .student-table-wrap{
        border: 1px solid #d8e6f4;
        border-radius: 12px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        background: #ffffff;
    }
    #example{
        margin-bottom: 0 !important;
    }
    #example thead th{
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 700;
        white-space: nowrap;
    }
    #example tbody td{
        font-size: 0.9rem;
        color: #1f2937;
        vertical-align: middle;
    }
    #example tbody tr:hover{
        background-color: #f0f7ff;
    }
    .student-photo{
        width: 52px;
        height: 52px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
    }
    .fee-value{
        font-weight: 700;
        color: #0f766e;
    }
</style>
<h2 class="student-page-title mb-3">Manage <?= $module['title'] ?></h2>
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

<div class="card shadow-sm student-list-card">
    <div class="card-header">
        <h5>
            <a href="<?= url('student/add') ?>" class="btn btn-success btn-sm">Add New <?= $module['title'] ?></a>
        </h5>
    </div>
    <div class="card-body">
        <h6 class="text-center alert alert-sm py-2 px-2 student-list-note">List of <?= $module['title'] ?></h6>
        <div class="table-responsive student-table-wrap">
            <table id="example" class="table table-striped table-bordered align-middle text-center" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">ID</th>
                        <th class="text-center">Name of the Student</th>
                        <th class="text-center">Contact</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Branch</th>
                        <th class="text-center">Class</th>
                        <th class="text-center">DOB</th>
                        <th class="text-center">Parent Name</th>
                        <th class="text-center">Address & Pincode</th>
                        <th class="text-center">Admission Fees</th>
                        <th class="text-center">Monthly Fees</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows) {
                        $sl = 1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $sl++ ?></td>
                                <td><?= $row->student_id_serial ?></td>
                                <td>
                                    <?php if ($row->photo != '') { ?>
                                        <img src="<?= config('constants.app_url') . config('constants.uploads_url_path') . $row->photo ?>" class="student-photo">
                                    <?php } else { ?>
                                        <img src="{{ config('constants.no_image_avatar') }}" class="student-photo">
                                    <?php } ?>
                                    <br>
                                    <?= (!empty(trim((string)$row->full_name)) && strcasecmp(trim((string)$row->full_name), 'No Name') !== 0) ? $row->full_name : '-' ?>
                                </td>
                                <td><?= !empty($row->father_mobile) ? $row->father_mobile : '-' ?></td>
                                <td><?= !empty($row->unit_name) ? $row->unit_name : '-' ?></td>
                                <td><?= !empty($row->branch_name) ? $row->branch_name : '-' ?></td>
                                <td><?= !empty($row->class_name) ? $row->class_name : '-' ?></td>
                                <td><?= (!empty($row->dob) && strtotime($row->dob)) ? date("d-m-Y", strtotime($row->dob)) : '-' ?></td>
                                <td>
                                    <strong>Father:</strong> <?= (!empty(trim((string)$row->father_name)) && strcasecmp(trim((string)$row->father_name), 'No Name') !== 0) ? $row->father_name : '-' ?>
                                    <br>
                                    <strong>Mother:</strong> <?= (!empty(trim((string)$row->mother_name)) && strcasecmp(trim((string)$row->mother_name), 'No Name') !== 0) ? $row->mother_name : '-' ?>
                                </td>
                                <td>
                                    <?= !empty($row->permanent_address) ? $row->permanent_address : '-' ?>
                                    <br>
                                    PIN: <?= !empty($row->permanent_pincode) ? $row->permanent_pincode : '-' ?>
                                </td>
                                <td><span class="fee-value"><?= is_numeric($row->admission_fees) ? number_format((float)$row->admission_fees, 2) : '-' ?></span></td>
                                <td><span class="fee-value"><?= is_numeric($row->monthly_fees) ? number_format((float)$row->monthly_fees, 2) : '-' ?></span></td>
                                <td>
                                    <?php
                                    $encoded_id     = Helper::encoded($row->id);
                                    $delete_url     = $controllerRoute . '/delete/';
                                    $status_url     = $controllerRoute . '/change-status/';
                                    $edit_url       = $controllerRoute . '/edit/';
                                    ?>
                                    <a href="<?= url($controllerRoute . '/edit/' . Helper::encoded($row->id)) ?>" class="text-primary" title="Edit <?= $module['title'] ?>"><i class="fa fa-edit text-primary"></i></a>
                                    |
                                    <?php if ($row->status) { ?>
                                        <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $status_url ?>', 'Are you sure you want to deactivate this record?')" class="text-success" title="Active <?= $module['title'] ?>"><i class="fa fa-check text-success"></i></a>
                                    <?php } else { ?>
                                        <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $status_url ?>', 'Are you sure you want to activate this record?')" class="text-warning" title="Blocked <?= $module['title'] ?>"><i class="fas fa-ban text-danger"></i></a>
                                    <?php } ?>
                                    |
                                    <!-- <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $delete_url ?>', 'This record will be permanently deleted. Do you want to proceed?')" class="text-danger" title="Delete <?= $module['title'] ?>"><i class="fa fa-trash text-danger"></i></a> -->
                                    <!-- <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#studentDetails<?= $row->id ?>">
                                        <i class="fa fa-eye text-success"></i>
                                    </a> -->
                                    <a href="javascript:void(0);" class="viewStudentBtn text-info" data-id="<?= Helper::encoded($row->id) ?>" title="View <?= $module['title'] ?>">
                                        <i class="fa fa-eye text-info"></i>
                                    </a>
                                    |
                                    <a href="<?= url($controllerRoute . '/student-print/' . Helper::encoded($row->id)) ?>" class="text-primary" target="_blank" title="Print <?= $module['title'] ?>">
                                        <i class="fa fa-print text-primary"></i>
                                    </a>
                                    |
                                    <a href="<?= url($controllerRoute . '/student-pdf/' . Helper::encoded($row->id)) ?>" class="text-danger" target="_blank" title="PDF <?= $module['title'] ?>">
                                        <i class="fa-solid fa-file-pdf text-danger"></i>
                                    </a>
                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content" id="studentDetailsContent">
            
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script>
    document.addEventListener("click", function (e) {

        let btn = e.target.closest(".viewStudentBtn");

        if (btn) {

            let studentId = btn.dataset.id;
            let modalElement = document.getElementById('studentDetailsModal');

            if (!modalElement) {
                console.error("Modal not found!");
                return;
            }

            let modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();

            

            fetch("{{ url('student/details') }}/" + studentId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("studentDetailsContent").innerHTML = data.html;
                })
                .catch(error => {
                    document.getElementById("studentDetailsContent").innerHTML =
                        `<div class="alert alert-danger">Something went wrong.</div>`;
                });
        }
    });
    </script>
@endsection
