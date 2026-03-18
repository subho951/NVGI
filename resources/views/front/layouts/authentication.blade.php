<?php

use App\Helpers\Helper;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  @include('front/elements/head')
</head>

<body class="auth-body">
  <div class="auth-shell">
    <div class="auth-brand-panel">
      <div class="auth-brand-inner">
        <img class="auth-brand-logo" src="<?=((Helper::getSettingValue('site_logo') != '')?config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_logo'):env('NO_IMAGE'))?>" alt="logo">
        <p class="auth-brand-eyebrow">Welcome To</p>
        <h2 class="auth-brand-title"><?=Helper::getSettingValue('site_name')?></h2>
        <p class="auth-brand-subtitle"><?=Helper::getSettingValue('description')?></p>
      </div>
    </div>
    <div class="auth-form-panel">
      <div class="auth-card">
        @yield('content')
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script type="text/javascript">
      $(function(){
        $('.autohide').delay(5000).fadeOut('slow');
      });
  </script>
  @yield('scripts')
</body>

</html>
