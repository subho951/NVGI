@extends('front.layouts.authentication')
@section('content')
<?php

use App\Helpers\Helper;
?>
<?php if (session('success_message')) { ?>
    <div class="alert alert-success alert-dismissible autohide" role="alert">
        <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-desktop align-top me-2"></i>Success!</h6>
        <span><?= session('success_message') ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
        </button>
    </div>
<?php } ?>
<?php if (session('error_message')) { ?>
    <div class="alert alert-danger alert-dismissible autohide" role="alert">
        <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-store align-top me-2"></i>Error!</h6>
        <span><?= session('error_message') ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
        </button>
    </div>
<?php } ?>
<h4 class="text-success text-center"><img src="{{ config('constants.admin_assets_url') }}image/TM-logo-new-vedant.png" class="w-100"><br><?=Helper::getSettingValue('description')?></h4>
<form action="{{ url('signin') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Username</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter username" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
    </div>
    <div class="d-grid">
        <button type="submit" class="btn btn-success">Login</button>
    </div>
</form>
@endsection