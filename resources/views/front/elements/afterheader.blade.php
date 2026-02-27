<?php

use App\Helpers\Helper;
?>
<button id="sidebarToggle" class="btn btn-sm btn-dark mb-2 d-lg-none">
    <i class="fa-solid fa-bars"></i>
</button>
<h5 class="mb-0 fw-bold text-primary" style="text-align:center;"><i class="fa fa-gift"></i> <?=Helper::getSettingValue('site_name')?></h5>
<a href="<?= url('logout') ?>" class="btn btn-danger btn-sm"><i class="fa fa-sign-out"></i> Logout</a>