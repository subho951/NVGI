<?php

use App\Helpers\Helper;
?>
<button id="sidebarToggle" class="btn btn-sm btn-dark mb-2">
    <i class="fa-solid fa-bars"></i>
</button>
<h4 class="mb-0"><?=Helper::getSettingValue('site_name')?></h4>
<a href="<?= url('logout') ?>" class="btn btn-danger btn-sm"><i class="fa fa-sign-out"></i> Logout</a>