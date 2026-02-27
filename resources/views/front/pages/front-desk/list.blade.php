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
            <input type="hidden" class="form-control form-control-sm" name="country_code" id="country_code" placeholder="Write Country Phone Code" autocomplete="off" required value="+91">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control form-control-sm" name="first_name" id="first_name" placeholder="Write First Name" autocomplete="off" required>
                    @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" class="form-control form-control-sm" name="middle_name" id="middle_name" placeholder="Write Middle Name" autocomplete="off" required>
                    @error('middle_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control form-control-sm" name="last_name" id="last_name" placeholder="Write Last Name" autocomplete="off" required>
                    @error('last_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- <div class="col-md-6 mb-3">
                    <label for="country_code">Country Phone Code</label>
                    <input type="text" class="form-control form-control-sm" name="country_code" id="country_code" placeholder="Write Country Phone Code" autocomplete="off" required>
                    @error('country_code') <span class="text-danger">{{ $message }}</span> @enderror
                </div> -->
                <div class="col-md-6 mb-3">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control form-control-sm" name="phone" id="phone" placeholder="Write Phone" autocomplete="off" required>
                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- <div class="col-md-6 mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control form-control-sm" name="email" id="email" placeholder="Write Email" autocomplete="off" required>
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div> -->
                <div class="col-md-6 mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control form-control-sm" name="password" id="password" placeholder="Write Password" autocomplete="off" minlength="8" maxlength="15" required>
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
                    <button type="submit" class="btn btn-success btn-sm w-100"><?= $action ?></button>
                </div>
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
                <th>ID (Auto-generated)</th>
                <th>Name</th>
                <!-- <th>Email</th> -->
                <th>Phone</th>
                <th>Action</th>
            </thead>
            <tbody>
                <?php if(count($rows) > 0){ $sl=1; foreach($rows as $row){?>
                    <tr>
                        <td><?=$sl++?></td>
                        <td><?=$row->serial_id?></td>
                        <td><?=$row->first_name . ' ' . $row->middle_name. ' ' . $row->last_name?></td>
                        <!-- <td><?=$row->email?></td> -->
                        <td><?=$row->country_code?> <?=$row->phone?></td>
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
                <?php } } else {?>
                    <tr>
                        <td colspan="5" class="text-danger text-center">
                            No records found
                        </td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
        <!-- <small class="text-danger">Developer's note : deactivation, delete, etc should come with nice confirm message</small> -->
    </div>
</div>
@endsection