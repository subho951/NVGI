<?php
use Illuminate\Support\Facades\Route;
use App\Helpers\Helper;

$routeName    = Route::current();
$pageName     = explode("/", $routeName->uri());
$pageSegment  = $pageName[0];
?>
<style>
    .active-link {
        color: #99b138 !important;
    }
</style>
<h4>Admin Panel</h4>
<a href="<?= url('dashboard') ?>" <?= (($pageSegment == 'dashboard')?'class="active-link"':'')?>><i class="fa fa-home"></i> Dashboard</a>
<a href="<?= url('unit/list') ?>" <?= (($pageSegment == 'unit')?'class="active-link"':'')?>><i class="fa-solid fa-layer-group"></i> Manage Unit</a>
<a href="<?= url('branch/list') ?>" <?= (($pageSegment == 'branch')?'class="active-link"':'')?>><i class="fa-solid fa-code-branch"></i> Manage Branch</a>
<a href="<?= url('front-desk/list') ?>" <?= (($pageSegment == 'front-desk')?'class="active-link"':'')?>><i class="fa-solid fa-bell-concierge"></i> Manage Front-Desk</a>
<a href="<?= url('add-student') ?>" <?= (($pageSegment == 'add-student')?'class="active-link"':'')?>><i class="fa-solid fa-graduation-cap"></i> Add Student</a>
<a href="<?= url('student-list') ?>" <?= (($pageSegment == 'student-list')?'class="active-link"':'')?>><i class="fa-solid fa-user-graduate"></i> Student List</a>
<a href="<?= url('settings') ?>" <?= (($pageSegment == 'settings')?'class="active-link"':'')?>><i class="fa-solid fa-gears"></i> Settings</a>