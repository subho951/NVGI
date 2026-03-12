@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Models\Module;
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
            <a href="<?= url('role/add') ?>" class="btn btn-success btn-sm">Add New <?= $module['title'] ?></a>
        </h5>
    </div>
    <div class="card-body">
        <h6 class="text-center alert alert-info alert-sm py-2 px-2">List of <?= $module['title'] ?></h6>
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle text-center" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Modules</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows) {
                        $sl = 1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $sl++ ?></td>
                                <td><?= $row->name ?></td>
                                <td>
                                    <div class="row">
                                        <?php
                                        $module_id = (($row->module_id != '')?json_decode($row->module_id):[]);
                                        if(!empty($module_id)){ for($m=0;$m<count($module_id);$m++){
                                            $getModule = Module::select('name')->where('id', '=', $module_id[$m])->first();
                                        ?>
                                            <div class="col-md-3">
                                                <span class="badge bg-primary"><?= (($getModule)?$getModule->name:'') ?></span>
                                            </div>
                                        <?php } }?>
                                    </div>
                                </td>
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
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection