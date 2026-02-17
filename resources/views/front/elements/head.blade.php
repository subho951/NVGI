<?php
use App\Helpers\Helper;
?>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Login Page - <?=Helper::getSettingValue('site_name')?></title>
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="<?=((Helper::getSettingValue('site_favicon') != '')?config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_favicon'):config('constants.no_image'))?>" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ config('constants.admin_assets_url') }}css/style.css" rel="stylesheet">