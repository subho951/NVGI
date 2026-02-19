<?php

use App\Helpers\Helper;
use Illuminate\Support\Facades\Route;

$routeName    = Route::current();
$pageName     = explode("/", $routeName->uri());
$pageSegment  = $pageName[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  @include('front/elements/afterhead')
</head>

<body>

  <!-- Sidebar -->
  <div class="sidebar">
    @include('front/elements/sidebar')
  </div>
  <!-- Sidebar -->

  <!-- Topbar -->
  <div class="topbar">
    <div id="sidebar" class="sidebar">
      @include('front/elements/afterheader')
    </div>
  </div>
  <!-- Topbar -->

  <!-- Main Content -->
  <div class="content">
    @yield('content')
  </div>
  <!-- Main Content -->

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- DataTables Buttons (Export) -->
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

  <!-- Initialize DataTable -->
  <script>
    $(document).ready(function() {
        $('#example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdf', className: 'btn btn-danger btn-sm' }
            ],
            pageLength: 10
        });
    });
  </script>

  <!-- Initialize Tooltips -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      })
    });
  </script>
  <script type="text/javascript">
    $(function() {
      $('.autohide').delay(5000).fadeOut('slow');
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    var url = '<?= url('/') ?>';
    function showConfirmBox(id, action_url, confirm_message) {
      Swal.fire({
        title: 'Are you sure?',
        text: confirm_message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url + '/' + action_url + id;
        }
      });
    }
  </script>
  <script>
      document.addEventListener('DOMContentLoaded', function () {
          const toggleBtn = document.getElementById('sidebarToggle');
          const sidebar   = document.getElementById('sidebar');

          if (!toggleBtn || !sidebar) {
              console.error('Sidebar or toggle button not found!');
              return;
          }

          toggleBtn.addEventListener('click', function () {
              sidebar.classList.toggle('collapsed');
          });
      });
  </script>
  @yield('scripts')
</body>

</html>