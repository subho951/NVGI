<?php

use App\Helpers\Helper;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  @include('front/elements/head')
</head>

<body style="background: url('{{ config('constants.admin_assets_url') }}image/bg.jpg') no-repeat center center fixed;background-size: cover;">

  <div class="login-box">
    @yield('content')
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