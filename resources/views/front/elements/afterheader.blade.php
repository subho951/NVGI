<?php
use App\Helpers\Helper;
// echo '<pre>';print_r(session()->all());
?>
<button id="sidebarToggle" class="btn btn-sm btn-dark mb-2 d-lg-none">
    <i class="fa-solid fa-bars"></i>
</button>
<h5 class="mb-0 fw-bold text-primary" style="text-align:center;"><i class="fa fa-gift"></i> <?=Helper::getSettingValue('site_name')?></h5>
<h6 class="mb-0 fw-bold text-primary" style="text-align:right;"><i class="fa fa-user"></i> Welcome <?= session('user_data')['name'] ?></h6>

<a href="<?= url('logout') ?>" class="btn btn-danger btn-sm"><i class="fa fa-sign-out"></i> Logout</a>