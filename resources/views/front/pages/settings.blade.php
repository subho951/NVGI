@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;
?>
<style>
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        color: var(--bs-nav-pills-link-active-color);
        background-color: #343a40 !important;
    }
</style>
<h2>Settings</h2>
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
<div class="card shadow bg-light mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Profile Setting</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">General Setting</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-application-tab" data-bs-toggle="pill" data-bs-target="#pills-application" type="button" role="tab" aria-controls="pills-application" aria-selected="false">Application Setting</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Change Password</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-email-tab" data-bs-toggle="pill" data-bs-target="#pills-email" type="button" role="tab" aria-controls="pills-email" aria-selected="false">Email Setting</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-sms-tab" data-bs-toggle="pill" data-bs-target="#pills-sms" type="button" role="tab" aria-controls="pills-sms" aria-selected="false">SMS Setting</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                        <h6 class="text-center alert alert-info alert-sm py-2 px-2">Profile Setting</h6>
                        <form method="POST" action="<?= url('profile-settings') ?>" class="row g-3" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="first_name">First Name</label>
                                    <input type="text" class="form-control form-control-sm" name="first_name" id="first_name" placeholder="Write First Name" autocomplete="off" value="<?=$user->first_name?>" required>
                                    @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" class="form-control form-control-sm" name="last_name" id="last_name" placeholder="Write Last Name" autocomplete="off" value="<?=$user->last_name?>" required>
                                    @error('last_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control form-control-sm" name="email" id="email" placeholder="Write Email" autocomplete="off" value="<?=$user->email?>" required>
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="country_code">Country Code</label>
                                    <input type="text" class="form-control form-control-sm" name="country_code" id="country_code" placeholder="Write Country Code" autocomplete="off" value="<?=$user->country_code?>" required>
                                    @error('country_code') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="phone">Phone</label>
                                    <input type="text" class="form-control form-control-sm" name="phone" id="phone" placeholder="Write Phone" autocomplete="off" value="<?=$user->phone?>" minlength="10" maxlength="10" required>
                                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="profile_image">Profile Image</label>
                                    <input type="file" class="form-control form-control-sm" name="profile_image" id="profile_image" placeholder="Profile Image">
                                    <img src="<?=(($user->profile_image != '')?config('constants.app_url') . config('constants.uploads_url_path') . $user->profile_image:config('constants.no_image_avatar'))?>" alt="<?=$user->first_name?>" class="mt-3 d-block" style="width: 120px;height: 120px;border: 1px solid #343a402e;border-radius: 10px;" />
                                </div>

                                <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
                                    <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                        <h6 class="text-center alert alert-info alert-sm py-2 px-2">General Setting</h6>
                        <form method="POST" action="<?= url('general-settings') ?>" class="row g-3" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="site_name">Site Name</label>
                                    <input type="text" class="form-control form-control-sm" name="site_name" id="site_name" placeholder="Write Site Name" autocomplete="off" value="<?=Helper::getSettingValue('site_name')?>" required>
                                    @error('site_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="site_phone">Site Phone</label>
                                    <input type="text" class="form-control form-control-sm" name="site_phone" id="site_phone" placeholder="Write Site Phone" autocomplete="off" value="<?=Helper::getSettingValue('site_phone')?>" minlength="10" maxlength="10" required>
                                    @error('site_phone') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="address">Address</label>
                                    <input type="text" class="form-control form-control-sm" name="address" id="address" placeholder="Write Address" autocomplete="off" value="<?=Helper::getSettingValue('address')?>" required>
                                    @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="site_mail">Site Email</label>
                                    <input type="email" class="form-control form-control-sm" name="site_mail" id="site_mail" placeholder="Write Site Email" autocomplete="off" value="<?=Helper::getSettingValue('site_mail')?>" required>
                                    @error('site_mail') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="system_email">System Email</label>
                                    <input type="email" class="form-control form-control-sm" name="system_email" id="system_email" placeholder="Write System Email" autocomplete="off" value="<?=Helper::getSettingValue('system_email')?>" required>
                                    @error('system_email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="site_url">Site URL</label>
                                    <input type="text" class="form-control form-control-sm" name="site_url" id="site_url" placeholder="Write Site URL" autocomplete="off" value="<?=Helper::getSettingValue('site_url')?>" required>
                                    @error('site_url') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="description">Description</label>
                                    <textarea class="form-control form-control-sm" name="description" id="description" placeholder="Write Description" autocomplete="off" required><?=Helper::getSettingValue('description')?></textarea>
                                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="site_logo">Site Logo</label>
                                    <input type="file" class="form-control form-control-sm" name="site_logo" id="site_logo" placeholder="Site Logo">
                                    <img src="<?=((Helper::getSettingValue('site_logo') != '')?config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_logo'):env('NO_IMAGE'))?>" alt="<?=Helper::getSettingValue('site_name')?>" class="mt-3 d-block" style="width: 120px;height: 120px;border: 1px solid #343a402e;border-radius: 10px;" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="site_favicon">Site Favicon</label>
                                    <input type="file" class="form-control form-control-sm" name="site_favicon" id="site_favicon" placeholder="Site Favicon">
                                    <img src="<?=((Helper::getSettingValue('site_favicon') != '')?config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_favicon'):env('NO_IMAGE'))?>" alt="<?=Helper::getSettingValue('site_name')?>" class="mt-3 d-block" style="width: 120px;height: 120px;border: 1px solid #343a402e;border-radius: 10px;" />
                                </div>

                                <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
                                    <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="pills-application" role="tabpanel" aria-labelledby="pills-application-tab">
                        <h6 class="text-center alert alert-info alert-sm py-2 px-2">SMS Setting</h6>
                        <form method="POST" action="<?= url('application-settings') ?>" class="row g-3" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="overtime_hours">Overtime Hours</label>
                                    <input type="text" class="form-control form-control-sm" name="overtime_hours" id="overtime_hours" placeholder="Write Overtime Hours" autocomplete="off" value="<?=Helper::getSettingValue('overtime_hours')?>" required>
                                    @error('overtime_hours') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="employer_pf_percentage">Employer PF Percentage</label>
                                    <input type="text" class="form-control form-control-sm" name="employer_pf_percentage" id="employer_pf_percentage" placeholder="Write Employer PF Percentage" autocomplete="off" value="<?=Helper::getSettingValue('employer_pf_percentage')?>" required>
                                    @error('employer_pf_percentage') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="employee_pf_percentage">Employee PF Percentage</label>
                                    <input type="text" class="form-control form-control-sm" name="employee_pf_percentage" id="employee_pf_percentage" placeholder="Write Employee PF Percentage" autocomplete="off" value="<?=Helper::getSettingValue('employee_pf_percentage')?>" required>
                                    @error('employee_pf_percentage') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="employer_esi_percentage">Employer ESI Percentage</label>
                                    <input type="text" class="form-control form-control-sm" name="employer_esi_percentage" id="employer_esi_percentage" placeholder="Write Employer ESI Percentage" autocomplete="off" value="<?=Helper::getSettingValue('employer_esi_percentage')?>" required>
                                    @error('employer_esi_percentage') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="employee_esi_percentage">Employee ESI Percentage</label>
                                    <input type="text" class="form-control form-control-sm" name="employee_esi_percentage" id="employee_esi_percentage" placeholder="Write Employee ESI Percentage" autocomplete="off" value="<?=Helper::getSettingValue('employee_esi_percentage')?>" required>
                                    @error('employee_esi_percentage') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
                                    <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                        <h6 class="text-center alert alert-info alert-sm py-2 px-2">Change Password</h6>
                        <form method="POST" action="<?= url('change-password') ?>" class="row g-3" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="old_password">Old Password</label>
                                    <input type="password" class="form-control form-control-sm" name="old_password" id="old_password" placeholder="Write Old Password" autocomplete="off" required>
                                    @error('old_password') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control form-control-sm" name="new_password" id="new_password" placeholder="Write New Password" autocomplete="off" required>
                                    @error('new_password') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" class="form-control form-control-sm" name="confirm_password" id="confirm_password" placeholder="Write Confirm Password" autocomplete="off" required>
                                    @error('confirm_password') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
                                    <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="pills-email" role="tabpanel" aria-labelledby="pills-email-tab">
                        <h6 class="text-center alert alert-info alert-sm py-2 px-2">Email Setting</h6>
                        <form method="POST" action="<?= url('email-settings') ?>" class="row g-3" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="from_email">From Email</label>
                                    <input type="text" class="form-control form-control-sm" name="from_email" id="from_email" placeholder="Write From Email" autocomplete="off" value="<?=Helper::getSettingValue('from_email')?>" required>
                                    @error('from_email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="from_name">From Name</label>
                                    <input type="text" class="form-control form-control-sm" name="from_name" id="from_name" placeholder="Write From Name" autocomplete="off" value="<?=Helper::getSettingValue('from_name')?>" required>
                                    @error('from_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="smtp_host">SMTP Host</label>
                                    <input type="text" class="form-control form-control-sm" name="smtp_host" id="smtp_host" placeholder="Write SMTP Host" autocomplete="off" value="<?=Helper::getSettingValue('smtp_host')?>" required>
                                    @error('smtp_host') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="smtp_username">SMTP Username</label>
                                    <input type="text" class="form-control form-control-sm" name="smtp_username" id="smtp_username" placeholder="Write SMTP Username" autocomplete="off" value="<?=Helper::getSettingValue('smtp_username')?>" required>
                                    @error('smtp_username') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="smtp_password">SMTP Password</label>
                                    <input type="text" class="form-control form-control-sm" name="smtp_password" id="smtp_password" placeholder="Write SMTP Password" autocomplete="off" value="<?=Helper::getSettingValue('smtp_password')?>" required>
                                    @error('smtp_password') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="smtp_port">SMTP Port</label>
                                    <input type="text" class="form-control form-control-sm" name="smtp_port" id="smtp_port" placeholder="Write SMTP Port" autocomplete="off" value="<?=Helper::getSettingValue('smtp_port')?>" required>
                                    @error('smtp_port') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
                                    <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="pills-sms" role="tabpanel" aria-labelledby="pills-sms-tab">
                        <h6 class="text-center alert alert-info alert-sm py-2 px-2">SMS Setting</h6>
                        <form method="POST" action="<?= url('sms-settings') ?>" class="row g-3" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="sms_authentication_key">Authentication Key</label>
                                    <input type="text" class="form-control form-control-sm" name="sms_authentication_key" id="sms_authentication_key" placeholder="Write Authentication Key" autocomplete="off" value="<?=Helper::getSettingValue('sms_authentication_key')?>" required>
                                    @error('sms_authentication_key') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="sms_sender_id">Sender ID</label>
                                    <input type="text" class="form-control form-control-sm" name="sms_sender_id" id="sms_sender_id" placeholder="Write Sender ID" autocomplete="off" value="<?=Helper::getSettingValue('sms_sender_id')?>" required>
                                    @error('sms_sender_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="sms_base_url">Base URL</label>
                                    <input type="text" class="form-control form-control-sm" name="sms_base_url" id="sms_base_url" placeholder="Write Base URL" autocomplete="off" value="<?=Helper::getSettingValue('sms_base_url')?>" required>
                                    @error('sms_base_url') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4 text-center">
                                    <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection