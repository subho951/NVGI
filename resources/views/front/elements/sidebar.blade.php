<?php
use Illuminate\Support\Facades\Route;
use App\Helpers\Helper;

$routeName    = Route::current();
$pageName     = explode("/", $routeName->uri());
$pageSegment  = $pageName[0];
if(count($pageName) > 1){
    $pageFunction  = $pageName[1];
} else {
    $pageFunction  = '';
}

?>
<style>
    .active-link {
        color: #99b138 !important;
    }
</style>

<h4>Admin Panel 1.0</h4>

<ul class="nav flex-column sidebar-menu">

    <li class="nav-item">
        <a href="<?= url('dashboard') ?>" class="nav-link <?= (($pageSegment == 'dashboard')?'active-link':'') ?>">
            <i class="fa fa-home"></i> <span>Dashboard</span>
        </a>
    </li>
    
    <!-- Masters -->
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center <?= in_array($pageSegment, ['unit','branch','subject','session','board','medium','know-about','class','religion']) ? 'active-link' : '' ?>"
        data-bs-toggle="collapse"
        href="#mastersMenu"
        role="button"
        aria-expanded="<?= in_array($pageSegment, ['unit','branch','subject','session','board','medium','know-about','class','religion']) ? 'true' : 'false' ?>"
        aria-controls="mastersMenu">
            <span><i class="fa-solid fa-database"></i> Masters</span>
            <i class="fa-solid fa-angle-down menu-arrow"></i>
        </a>

        <ul class="collapse list-unstyled ps-3 <?= in_array($pageSegment, ['unit','branch','subject','session','board','medium','know-about','class','religion']) ? 'show' : '' ?>" id="mastersMenu">
            <li>
                <a href="<?= url('unit/list') ?>" class="nav-link <?= (($pageSegment == 'unit')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Units</span>
                </a>
            </li>

            <li>
                <a href="<?= url('branch/list') ?>" class="nav-link <?= (($pageSegment == 'branch')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Branches</span>
                </a>
            </li>

            <li>
                <a href="<?= url('subject/list') ?>" class="nav-link <?= (($pageSegment == 'subject')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Subjects</span>
                </a>
            </li>

            <li>
                <a href="<?= url('session/list') ?>" class="nav-link <?= (($pageSegment == 'session')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Sessions</span>
                </a>
            </li>

            <li>
                <a href="<?= url('board/list') ?>" class="nav-link <?= (($pageSegment == 'board')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Boards</span>
                </a>
            </li>

            <li>
                <a href="<?= url('medium/list') ?>" class="nav-link <?= (($pageSegment == 'medium')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Mediums</span>
                </a>
            </li>

            <li>
                <a href="<?= url('religion/list') ?>" class="nav-link <?= (($pageSegment == 'religion')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Religion</span>
                </a>
            </li>

            <!--<li>
                <a href="<?= url('know-about/list') ?>" class="nav-link <?= (($pageSegment == 'know-about')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Know About Us</span>
                </a>
            </li>-->

            <li>
                <a href="<?= url('class/list') ?>" class="nav-link <?= (($pageSegment == 'class')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Class</span>
                </a>
            </li>

        </ul>
    </li>

    <li class="nav-item">
        <a href="<?= url('front-desk/list') ?>" class="nav-link <?= (($pageSegment == 'front-desk')?'active-link':'') ?>">
            <i class="fa-solid fa-bell-concierge"></i> <span>Front-Desks</span>
        </a>
    </li>

    <!-- Students -->
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center <?= in_array($pageSegment, ['student']) ? 'active-link' : '' ?>"
            data-bs-toggle="collapse"
            href="#studentsMenu"
            role="button"
            aria-expanded="<?= in_array($pageSegment, ['student']) ? 'true' : 'false' ?>"
            aria-controls="studentsMenu">
            <span><i class="fa-solid fa-users"></i> Students</span>
            <i class="fa-solid fa-angle-down menu-arrow"></i>
        </a>

        <ul class="collapse list-unstyled ps-3 <?= in_array($pageSegment, ['student']) ? 'show' : '' ?>" id="studentsMenu">
            <li>
                <a href="<?= url('student/list') ?>" class="nav-link <?= (($pageSegment == 'student' && $pageFunction == 'list')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>List</span>
                </a>
            </li>

            <li>
                <a href="<?= url('student/add') ?>" class="nav-link <?= (($pageSegment == 'student' && $pageFunction == 'add')?'active-link':'') ?>">
                    <i class="fa-solid fa-arrow-right"></i> <span>Add Student</span>
                </a>
            </li>

        </ul>
    </li>    

    <li class="nav-item">
        <a href="<?= url('settings') ?>" class="nav-link <?= (($pageSegment == 'settings')?'active-link':'') ?>">
            <i class="fa-solid fa-gears"></i> <span>Settings</span>
        </a>
    </li>

</ul>



