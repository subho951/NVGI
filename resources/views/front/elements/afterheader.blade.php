<?php
use App\Helpers\Helper;
// echo '<pre>';print_r(session()->all());
?>
<div class="topbar-shell">
    <div class="topbar-left-zone">
        <button id="sidebarToggle" class="btn topbar-menu-btn d-lg-none" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-brand">
            <h5 class="topbar-brand-title">
                <!-- <i class="fa-solid fa-shield-halved"></i> -->
                <?= Helper::getSettingValue('site_name') ?>
            </h5>
            <p class="topbar-brand-subtitle">Administration Console</p>
        </div>
    </div>

    <div class="topbar-right-zone">
        <div class="topbar-user-chip">
            <span class="topbar-user-icon"><i class="fa fa-user"></i></span>
            <div class="topbar-user-meta">
                <small>Welcome</small>
                <strong><?= session('user_data')['name'] ?></strong>
            </div>
        </div>

        <a href="<?= url('logout') ?>" class="btn btn-danger btn-sm topbar-logout-btn">
            <i class="fa fa-sign-out"></i> Logout
        </a>
    </div>
</div>
