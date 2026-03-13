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
            <a href="<?= url('lead/add') ?>" class="btn btn-success btn-sm">Add New <?= $module['title'] ?></a>
        </h5>
    </div>
    <div class="card-body">
        <h6 class="text-center alert alert-info alert-sm py-2 px-2">List of <?= $module['title'] ?></h6>
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle text-center" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Lead No.</th>
                        <th class="text-center">Sales Person Name</th>
                        <th class="text-center">Student Name</th>
                        <th class="text-center">Guardian Name</th>
                        <th class="text-center">Phone</th>
                        <th class="text-center">Address</th>
                        <th class="text-center">Age</th>
                        <th class="text-center">Remarks</th>
                        <th class="text-center">Other Remarks</th>
                        <th class="text-center">Date Uploaded</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows) {
                        $sl = 1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $sl++ ?></td>
                                <td><?= $row->lead_no ?></td>
                                <td><?= $row->sales_person_name ?></td>
                                <td><?= $row->student_name ?></td>
                                <td><?= $row->guardian_name ?></td>
                                <td><?= $row->phone ?></td>
                                <td><?= $row->address ?></td>
                                <td><?= $row->age ?></td>
                                <td><?= $row->remarks1 ?></td>
                                <td>
                                    <ul class="list-group">
                                        <?php if($row->remarks2 != ''){?>
                                            <li class="list-group-item">
                                                <span style="float: left;"><b>Remarks 2 :</b> <?= $row->remarks2 ?></span>
                                            </li>
                                        <?php }?>
                                        <?php if($row->remarks3 != ''){?>
                                            <li class="list-group-item">
                                                <span style="float: left;"><b>Remarks 3 :</b> <?= $row->remarks3 ?></span>
                                            </li>
                                        <?php }?>
                                        <?php if($row->remarks4 != ''){?>
                                            <li class="list-group-item">
                                                <span style="float: left;"><b>Remarks 4 :</b> <?= $row->remarks4 ?></span>
                                            </li>
                                        <?php }?>
                                        <?php if($row->remarks5 != ''){?>
                                            <li class="list-group-item">
                                                <span style="float: left;"><b>Remarks 5 :</b> <?= $row->remarks5 ?></span>
                                            </li>
                                        <?php }?>
                                    </ul>
                                </td>
                                <td><?= date_format(date_create($row->created_at), "d-m-Y h:i A") ?></td>
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
                                </td>
                            </tr>
                    <?php }
                    } else { ?>
                        <tr>
                            <td colspan="12" class="text-danger text-center">
                                No records found
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection