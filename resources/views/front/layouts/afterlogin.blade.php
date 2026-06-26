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
  @yield('styles')
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
      const tableElement = $('#example');
      if (!tableElement.length) {
        return;
      }

      const isStudentListPage = @json(request()->is('student/list'));
      const isEmployeeListPage = @json(request()->is('employee/list'));
      const cleanExportBody = function(data, row, column, node) {
        if (node && node.dataset && typeof node.dataset.exportText === 'string') {
          return node.dataset.exportText;
        }

        if (typeof data !== 'string') {
          return data;
        }

        return data
          .replace(/<br\s*\/?>/gi, '\n')
          .replace(/<[^>]*>/g, '')
          .replace(/\u00a0/g, ' ')
          .replace(/[ \t]+/g, ' ')
          .trim();
      };
      const isActionColumn = function(idx, node) {
        // Exclude Action/Actions columns from all exports.
        const headerText = (node && node.textContent ? node.textContent : '').trim().toLowerCase();
        if (headerText === 'action' || headerText === 'actions') {
          return true;
        }

        // Fallback for student list where action is the last column.
        if (isStudentListPage && idx === 12) {
          return true;
        }

        return false;
      };
      const excelColumnFilter = function(idx, data, node) {
        if (isActionColumn(idx, node)) {
          return false;
        }

        const headerText = (node && node.textContent ? node.textContent : '').trim().toLowerCase();
        if (isEmployeeListPage && headerText === 'bank details') {
          return false;
        }

        return true;
      };
      const pdfColumnFilter = function(idx, data, node) {
        if (isActionColumn(idx, node)) {
          return false;
        }

        if (isEmployeeListPage && node && node.classList && node.classList.contains('employee-export-only')) {
          return false;
        }

        return true;
      };

      tableElement.DataTable({
        dom: 'Bfrtip',
        buttons: [{
            extend: 'excel',
            className: 'btn btn-success btn-sm',
            exportOptions: {
              columns: excelColumnFilter,
              format: {
                body: cleanExportBody
              }
            }
          },
          {
            extend: 'pdfHtml5',
            className: 'btn btn-danger btn-sm',
            orientation: 'landscape',
            pageSize: (isStudentListPage || isEmployeeListPage) ? 'A3' : 'A4',
            exportOptions: {
              columns: pdfColumnFilter,
              format: {
                body: cleanExportBody
              }
            },
            customize: function(doc) {
              if (!isStudentListPage && !isEmployeeListPage) {
                return;
              }

              // Fit wide list exports by reducing font and forcing compact column widths.
              doc.pageMargins = [8, 12, 8, 12];
              doc.defaultStyle.fontSize = isEmployeeListPage ? 5 : 6;
              doc.styles.tableHeader.fontSize = isEmployeeListPage ? 6 : 7;
              doc.styles.tableHeader.alignment = 'center';
              doc.styles.tableHeader.fillColor = '#123a63';
              doc.styles.tableHeader.color = '#ffffff';

              const tableNode = doc.content.find(function(node) {
                return node.table;
              });

              if (tableNode && tableNode.table && tableNode.table.body && tableNode.table.body.length) {
                const columnCount = tableNode.table.body[0].length;
                let baseWidths = null;

                if (isStudentListPage && columnCount === 12) {
                  baseWidths = [16, 54, 76, 46, 38, 38, 38, 38, 70, 94, 58, 110];
                } else if (isStudentListPage && columnCount === 13) {
                  baseWidths = [16, 54, 76, 46, 38, 38, 38, 38, 70, 94, 58, 96, 14];
                } else if (isEmployeeListPage && columnCount === 9) {
                  baseWidths = [24, 104, 68, 104, 104, 82, 82, 136, 58];
                } else if (isEmployeeListPage && columnCount === 10) {
                  baseWidths = [22, 96, 58, 88, 82, 70, 70, 108, 84, 46];
                }

                if (baseWidths) {
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
        pageLength: 10,
        scrollX: !isEmployeeListPage,
        autoWidth: false
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
