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
  <div class="mobile-overlay" id="mobileOverlay"></div>
  <!-- Sidebar -->
  <div id="sidebar" class="sidebar">
    @include('front/elements/sidebar')
  </div>

  <!-- Topbar -->
  <div class="topbar">
    @include('front/elements/afterheader')
  </div>
  <!-- Topbar -->

  <!-- Main Content -->
  <div class="content">
    @yield('content')
  </div>
  <!-- Main Content -->

  <!-- footer -->
  <div class="footer">
  @include('front/elements/footer')
  </div>

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
      const isStudentListPage = @json(request()->is('student/list'));
      const studentListExportColumns = function(idx) {
        // On student list, skip the last "Action" column in exports.
        if (!isStudentListPage) {
          return true;
        }
        return idx !== 12;
      };

      $('#example').DataTable({
        dom: 'Bfrtip',
        buttons: [{
            extend: 'excel',
            className: 'btn btn-success btn-sm',
            exportOptions: {
              columns: studentListExportColumns
            }
          },
          {
            extend: 'pdfHtml5',
            className: 'btn btn-danger btn-sm',
            orientation: isStudentListPage ? 'landscape' : 'portrait',
            pageSize: isStudentListPage ? 'A3' : 'A4',
            exportOptions: {
              columns: studentListExportColumns,
              format: {
                body: function(data) {
                  if (typeof data !== 'string') {
                    return data;
                  }
                  // Strip HTML and normalize spaces so long markup does not bloat PDF widths.
                  return data
                    .replace(/<br\s*\/?>/gi, '\n')
                    .replace(/<[^>]*>/g, '')
                    .replace(/\u00a0/g, ' ')
                    .replace(/[ \t]+/g, ' ')
                    .trim();
                }
              }
            },
            customize: function(doc) {
              if (!isStudentListPage) {
                return;
              }

              // Fit wide student list exports by reducing font and forcing compact column widths.
              doc.pageMargins = [8, 12, 8, 12];
              doc.defaultStyle.fontSize = 6;
              doc.styles.tableHeader.fontSize = 7;
              doc.styles.tableHeader.alignment = 'center';
              doc.styles.tableHeader.fillColor = '#123a63';
              doc.styles.tableHeader.color = '#ffffff';

              const tableNode = doc.content.find(function(node) {
                return node.table;
              });

              if (tableNode && tableNode.table && tableNode.table.body && tableNode.table.body.length) {
                const columnCount = tableNode.table.body[0].length;
                // Student list export excludes "Action", so expected export columns are 12.
                if (columnCount === 12) {
                  const baseWidths = [16, 56, 84, 50, 42, 42, 42, 42, 78, 120, 52, 52];
                  const totalBase = baseWidths.reduce((sum, width) => sum + width, 0);
                  const pageWidth = (doc.pageSize && typeof doc.pageSize === 'object' && doc.pageSize.width) ? doc.pageSize.width : 1190;
                  const availableWidth = Math.max(600, Math.floor(pageWidth - doc.pageMargins[0] - doc.pageMargins[2]));
                  const scaled = baseWidths.map((width) => Math.max(20, Math.floor((width / totalBase) * availableWidth)));
                  const usedWidth = scaled.reduce((sum, width) => sum + width, 0);
                  scaled[scaled.length - 1] += (availableWidth - usedWidth);
                  tableNode.table.widths = scaled;
                } else if (columnCount === 13) {
                  const baseWidths = [16, 56, 84, 50, 42, 42, 42, 42, 78, 120, 52, 52, 44];
                  const totalBase = baseWidths.reduce((sum, width) => sum + width, 0);
                  const pageWidth = (doc.pageSize && typeof doc.pageSize === 'object' && doc.pageSize.width) ? doc.pageSize.width : 1190;
                  const availableWidth = Math.max(600, Math.floor(pageWidth - doc.pageMargins[0] - doc.pageMargins[2]));
                  const scaled = baseWidths.map((width) => Math.max(20, Math.floor((width / totalBase) * availableWidth)));
                  const usedWidth = scaled.reduce((sum, width) => sum + width, 0);
                  scaled[scaled.length - 1] += (availableWidth - usedWidth);
                  tableNode.table.widths = scaled;
                } else {
                  tableNode.table.widths = Array(columnCount).fill('*');
                }

                tableNode.layout = {
                  paddingLeft: function() { return 2; },
                  paddingRight: function() { return 2; },
                  paddingTop: function() { return 1; },
                  paddingBottom: function() { return 1; }
                };
              }
            }
          }
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
    document.addEventListener('DOMContentLoaded', function() {

      const toggleBtn = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('mobileOverlay');

      function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
      }

      toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
      });

      /* Close when clicking overlay */
      overlay.addEventListener('click', closeSidebar);

      /* Close when clicking anywhere on page */
      document.addEventListener('click', function(e) {
        if (
          sidebar.classList.contains('show') &&
          !sidebar.contains(e.target) &&
          !toggleBtn.contains(e.target)
        ) {
          closeSidebar();
        }
      });

    });
  </script>

  <!-- ChartJS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>

    // Charts
    new Chart(document.getElementById('branchChart'), {
      type: 'bar',
      data: {
        labels: ['VHS Bibirhat', 'VHS Rajarhat', 'TSA Bibirhat', 'TSA Mukundapur'],
        datasets: [{
          data: [78, 23, 250, 11]
        }]
      }
    });

    new Chart(document.getElementById('staffChart'), {
      type: 'pie',
      data: {
        labels: ['Jayeeta', 'Swastika', 'Trija', 'Riyanka'],
        datasets: [{
          data: [50, 28, 23, 6]
        }]
      }
    });

    new Chart(document.getElementById('totalChart'), {
      type: 'doughnut',
      data: {
        labels: ['VHS', 'TSA'],
        datasets: [{
          data: [101, 261]
        }]
      }
    });

    new Chart(document.getElementById('growthChart'), {
      type: 'line',
      data: {
        labels: ['Month 1', 'Month 2', 'Month 3', 'Month 4'],
        datasets: [{
          label: 'VHS',
          data: [5, 10, 18, 23]
        }, {
          label: 'TSA',
          data: [100, 150, 200, 245]
        }]
      }
    });
  </script>
  @yield('scripts')
</body>

</html>
