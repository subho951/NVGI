@extends('front.layouts.authentication')

@section('content')
<?php

use App\Helpers\Helper;
?>
@if(session('success_message'))
    <div class="alert alert-success alert-dismissible autohide" role="alert">
        <strong>Success:</strong> {{ session('success_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error_message'))
    <div class="alert alert-danger alert-dismissible autohide" role="alert">
        <strong>Error:</strong> {{ session('error_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<h4 class="text-primary text-center mb-4">
    <img src="<?= ((Helper::getSettingValue('site_logo') != '') ? config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_logo') : env('NO_IMAGE')) ?>" style="width:100%; height:100px; object-fit:contain;" alt="Logo">
    <br>
    Branch Employee Management
</h4>

<form action="{{ route('branch.portal.signin') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" placeholder="Enter branch serial ID" autocomplete="username" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" autocomplete="current-password" required>
    </div>
    <div class="d-grid">
        <button type="submit" class="btn btn-primary">Login</button>
    </div>
</form>
@endsection
