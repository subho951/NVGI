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
    <div class="card-header">
        <h5>
            <a href="<?= url('student/add') ?>" class="btn btn-success btn-sm">Add New <?= $module['title'] ?></a>
        </h5>
    </div>
    <div class="card-body">
        <h6 class="text-center alert alert-info alert-sm py-2 px-2">List of <?= $module['title'] ?></h6>
        <div class="table-responsive">
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
                        <th class="text-center">Admitted On</th>
                        <th class="text-center">Added By</th>
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
                                        <img src="<?= config('constants.app_url') . config('constants.uploads_url_path') . $row->photo ?>" style="width:50px; height:50px; border:1px solid #CCCCCC;">
                                    <?php } else { ?>
                                        <img src="{{ config('constants.no_image_avatar') }}" style="width:50px; height:50px; border:1px solid #CCCCCC; border-radius:50%;">
                                    <?php } ?>
                                    <br>
                                    <?= $row->full_name ?>
                                </td>
                                <td><?= $row->father_mobile ?></td>
                                <td><?= $row->unit_name ?></td>
                                <td><?= $row->branch_name ?></td>
                                <td><?= $row->class_name ?></td>
                                <td><?= date_format(date_create($row->created_at), "d-m-Y h:i A") ?></td>
                                <td><?= $row->first_name . ' ' . $row->last_name ?></td>
                                <td><?= $row->admission_fees ?></td>
                                <td><?= $row->monthly_fees ?></td>
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