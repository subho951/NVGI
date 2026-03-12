@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    .form-check-input:checked {
        background-color: #1e293b;
        border-color: #1e293b;
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

<?php
if ($row) {
    $name = $row->name;
    $module_id = (($row->module_id != '') ? json_decode($row->module_id) : []);
} else {
    $name = '';
    $module_id = [];
}
?>

<h6 class="text-center alert alert-info alert-sm py-2 px-2"><?= $action ?> <?= $module['title'] ?></h6>
<span class="text-danger">Star (*) marks fields are mandatory</span>
<form class="row g-3" method="POST" enctype="multipart/form-data" action="">
    @csrf

    <div class="col-md-6">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm" name="name" id="name" placeholder="Name" value="<?= $name ?>" required>
    </div>

    <div class="col-md-12">
        <label for="name">Modules <span class="text-danger">*</span></label>

        <div class="row">
            <?php
            if (!empty($modules)) {
                foreach ($modules as $module) {
                    $checked = ((in_array($module->id, $module_id))?'checked':'');
            ?>
                    <div class="col-md-3" style="padding: 10px;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="module_id[]" id="flexSwitchCheckDefault<?= $module->id ?>" value="<?= $module->id ?>" <?= $checked ?>>
                            <label class="form-check-label" for="flexSwitchCheckDefault<?= $module->id ?>">
                                <?= $module->name ?>
                            </label>
                        </div>
                    </div>
            <?php }
            } ?>
        </div>
    </div>

    <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
        <button type="submit" class="btn btn-success btn-sm w-100"><?= $action ?></button>
    </div>
</form>
@endsection