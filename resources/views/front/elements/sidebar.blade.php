<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\Helper;

$routeName    = Route::current();
$pageName     = explode("/", $routeName->uri());
$pageSegment  = $pageName[0];
if (count($pageName) > 1) {
    $pageFunction  = $pageName[1];
} else {
    $pageFunction  = '';
}
$role_id = session('user_data')['role_id'];
?>
<style>
    .active-link {
        color: #99b138 !important;
    }
</style>

<h4>Admin Panel 1.0</h4>
<ul class="nav flex-column sidebar-menu">
    <?php if(in_array(1, $moduleIds)){?>
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="<?= url('dashboard') ?>" class="nav-link <?= (($pageSegment == 'dashboard') ? 'active-link' : '') ?>">
                <i class="fa fa-home"></i> <span>Dashboard</span>
            </a>
        </li>
    <?php }?>

    <?php if((in_array(2, $moduleIds)) || (in_array(3, $moduleIds)) || (in_array(4, $moduleIds))){?>
        <!-- Access & permission -->
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center <?= in_array($pageSegment, ['module', 'role', 'front-desk']) ? 'active-link' : '' ?>"
                data-bs-toggle="collapse"
                href="#AccessMenu"
                role="button"
                aria-expanded="<?= in_array($pageSegment, ['module', 'role', 'front-desk']) ? 'true' : 'false' ?>"
                aria-controls="AccessMenu">
                <span><i class="fa-solid fa-key"></i> Access & Permission</span>
                <i class="fa-solid fa-angle-down menu-arrow"></i>
            </a>

            <ul class="collapse list-unstyled ps-3 <?= in_array($pageSegment, ['module', 'role', 'front-desk']) ? 'show' : '' ?>" id="AccessMenu">
                <?php if(in_array(2, $moduleIds)){?>
                    <li>
                        <a href="<?= url('module/list') ?>" class="nav-link <?= (($pageSegment == 'module') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Modules</span>
                        </a>
                    </li>
                <?php }?>
                <?php if(in_array(3, $moduleIds)){?>
                    <li>
                        <a href="<?= url('role/list') ?>" class="nav-link <?= (($pageSegment == 'role') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Roles</span>
                        </a>
                    </li>
                <?php }?>
                <?php if(in_array(4, $moduleIds)){?>
                    <li>
                        <a href="<?= url('front-desk/list') ?>" class="nav-link <?= (($pageSegment == 'front-desk') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Users</span>
                        </a>
                    </li>
                <?php }?>
            </ul>
        </li>
    <?php } ?>

    <?php if((in_array(6, $moduleIds)) || (in_array(7, $moduleIds)) || (in_array(8, $moduleIds)) || (in_array(9, $moduleIds)) || (in_array(10, $moduleIds)) || (in_array(11, $moduleIds)) || (in_array(12, $moduleIds)) || (in_array(13, $moduleIds)) || (in_array(14, $moduleIds))){?>
        <!-- Masters -->
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center <?= in_array($pageSegment, ['unit', 'branch', 'subject', 'session', 'board', 'medium', 'know-about', 'class', 'religion']) ? 'active-link' : '' ?>"
                data-bs-toggle="collapse"
                href="#mastersMenu"
                role="button"
                aria-expanded="<?= in_array($pageSegment, ['unit', 'branch', 'subject', 'session', 'board', 'medium', 'know-about', 'class', 'religion']) ? 'true' : 'false' ?>"
                aria-controls="mastersMenu">
                <span><i class="fa-solid fa-database"></i> Masters</span>
                <i class="fa-solid fa-angle-down menu-arrow"></i>
            </a>

            <ul class="collapse list-unstyled ps-3 <?= in_array($pageSegment, ['unit', 'branch', 'subject', 'session', 'board', 'medium', 'know-about', 'class', 'religion']) ? 'show' : '' ?>" id="mastersMenu">
                <?php if(in_array(6, $moduleIds)){?>
                    <li>
                        <a href="<?= url('unit/list') ?>" class="nav-link <?= (($pageSegment == 'unit') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Units</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(7, $moduleIds)){?>
                    <li>
                        <a href="<?= url('branch/list') ?>" class="nav-link <?= (($pageSegment == 'branch') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Branches</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(8, $moduleIds)){?>
                    <li>
                        <a href="<?= url('subject/list') ?>" class="nav-link <?= (($pageSegment == 'subject') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Subjects</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(9, $moduleIds)){?>
                    <li>
                        <a href="<?= url('session/list') ?>" class="nav-link <?= (($pageSegment == 'session') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Sessions</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(10, $moduleIds)){?>
                    <li>
                        <a href="<?= url('board/list') ?>" class="nav-link <?= (($pageSegment == 'board') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Boards</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(11, $moduleIds)){?>
                    <li>
                        <a href="<?= url('medium/list') ?>" class="nav-link <?= (($pageSegment == 'medium') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Mediums</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(12, $moduleIds)){?>
                    <li>
                        <a href="<?= url('religion/list') ?>" class="nav-link <?= (($pageSegment == 'religion') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Religions</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(13, $moduleIds)){?>
                    <!--<li>
                        <a href="<?= url('know-about/list') ?>" class="nav-link <?= (($pageSegment == 'know-about') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Know About Us</span>
                        </a>
                    </li>-->
                <?php } ?>
                <?php if(in_array(14, $moduleIds)){?>
                    <li>
                        <a href="<?= url('class/list') ?>" class="nav-link <?= (($pageSegment == 'class') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Class</span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </li>

        <!-- <li class="nav-item">
            <a href="<?= url('front-desk/list') ?>" class="nav-link <?= (($pageSegment == 'front-desk') ? 'active-link' : '') ?>">
                <i class="fa-solid fa-bell-concierge"></i> <span>Front-Desks</span>
            </a>
        </li> -->
    <?php } ?>

    <?php if((in_array(15, $moduleIds)) || (in_array(16, $moduleIds))){?>
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
                <?php if(in_array(15, $moduleIds)){?>
                    <li>
                        <a href="<?= url('student/list') ?>" class="nav-link <?= (($pageSegment == 'student' && $pageFunction == 'list') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>List</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(16, $moduleIds)){?>
                    <li>
                        <a href="<?= url('student/add') ?>" class="nav-link <?= (($pageSegment == 'student' && $pageFunction == 'add') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Add Student</span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>

    <?php if((in_array(17, $moduleIds)) || (in_array(18, $moduleIds))){?>
        <!-- CRM -->
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center <?= in_array($pageSegment, ['sales-person', 'lead']) ? 'active-link' : '' ?>"
                data-bs-toggle="collapse"
                href="#crmMenu"
                role="button"
                aria-expanded="<?= in_array($pageSegment, ['sales-person', 'lead']) ? 'true' : 'false' ?>"
                aria-controls="crmMenu">
                <span><i class="fa-solid fa-arrows-spin"></i> CRM</span>
                <i class="fa-solid fa-angle-down menu-arrow"></i>
            </a>

            <ul class="collapse list-unstyled ps-3 <?= in_array($pageSegment, ['sales-person', 'lead']) ? 'show' : '' ?>" id="crmMenu">
                <?php if(in_array(17, $moduleIds)){?>
                    <li>
                        <a href="<?= url('sales-person/list') ?>" class="nav-link <?= (($pageSegment == 'sales-person') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Sales Person</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if(in_array(18, $moduleIds)){?>
                    <li>
                        <a href="<?= url('lead/add') ?>" class="nav-link <?= (($pageSegment == 'lead') ? 'active-link' : '') ?>">
                            <i class="fa-solid fa-arrow-right"></i> <span>Leads</span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>

    <?php if(in_array(5, $moduleIds)){?>
        <!-- Settings -->
        <li class="nav-item">
            <a href="<?= url('settings') ?>" class="nav-link <?= (($pageSegment == 'settings') ? 'active-link' : '') ?>">
                <i class="fa-solid fa-gears"></i> <span>Settings</span>
            </a>
        </li>
    <?php } ?>
</ul>