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
<!--<h6 class="mb-4 text-danger">Developer's Note : ID should be : NVGI/{Unit Name}{1st letter of branch}/1,2,3...</h6>-->
<div class="card shadow bg-light mb-4">
    <div class="card-body">
        <h6 class="text-center alert alert-info alert-sm py-2 px-2"><?= $action ?> <?= $module['title'] ?></h6>
        <form method="POST" action="" class="row g-3">
            @csrf
            <div class="col-md-1">
                Select Unit
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="unit_id" id="unit_id" required>
                    <option selected disabled>Click here</option>
                    <?php if($units){ foreach($units as $unit){?>
                        <option value="<?= $unit->id ?>" <?= (($single_row)?(($single_row->unit_id == $unit->id)?'selected':''):'') ?>><?= $unit->name ?></option>
                    <?php } }?>
                </select>
                @error('unit_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-2">
                Name of the <?= $module['title'] ?>
            </div>
            <div class="col-md-3">
                <input class="form-control form-control-sm" name="name" id="name" placeholder="Write <?= $module['title'] ?> Name" value="<?= (($single_row)?$single_row->name:'') ?>" required>
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-1">
                <button type="submit" class="btn btn-success btn-sm w-100"><?= $action ?></button>
            </div>
        </form>
    </div>
</div>
<div class="card shadow bg-light">
    <div class="card-body">
        <h6 class="text-center alert alert-info alert-sm py-2 px-2">List of <?= $module['title'] ?></h6>
        <table class="table table-bordered datatable">
            <thead>
                <th>#</th>
                <th>Name of the Unit</th>
                <th>Name of the <?= $module['title'] ?></th>
                <th>Action</th>
            </thead>
            <tbody>
                <?php if($rows){ $sl=1; foreach($rows as $row){?>
                    <tr>
                        <td><?=$sl++?></td>
                        <td><?= $row->unit_name ?></td>
                        <td><?=$row->name?></td>
                        <td>
                            <?php
                            $encoded_id     = Helper::encoded($row->id);
                            $delete_url     = $controllerRoute . '/delete/';
                            $status_url     = $controllerRoute . '/change-status/';
                            $edit_url       = $controllerRoute . '/edit/';
                            ?>
                            <a href="<?=url($controllerRoute . '/edit/'.Helper::encoded($row->id))?>" class="text-primary" title="Edit <?=$module['title']?>">Edit</a>
                            |
                            <?php if($row->status){?>
                                <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $status_url ?>', 'Are you sure you want to deactivate this record?')" class="text-success" title="Active <?=$module['title']?>">Active</a>
                            <?php } else {?>
                                <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $status_url ?>', 'Are you sure you want to activate this record?')" class="text-warning" title="Blocked <?=$module['title']?>">Blocked</a>
                            <?php } ?>
                            |
                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $delete_url ?>', 'This record will be permanently deleted. Do you want to proceed?')" class="text-danger" title="Delete <?=$module['title']?>">Delete</a>
                        </td>
                    </tr>
                <?php } }?>
            </tbody>
        </table>
        <!-- <small class="text-danger">Developer's note : deactivation, delete, etc should come with nice confirm message</small> -->
    </div>
</div>
@endsection