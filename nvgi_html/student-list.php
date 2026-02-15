<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NVGI - Student List</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/brands.min.css" integrity="sha512-+oRH6u1nDGSm3hH8poU85YFIVTdSnS2f+texdPGrURaJh8hzmhMiZrQth6l56P4ZQmxeZzd2DqVEMqQoJ8J89A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/regular.min.css" integrity="sha512-aNH2ILn88yXgp/1dcFPt2/EkSNc03f9HBFX0rqX3Kw37+vjipi1pK3L9W08TZLhMg4Slk810sPLdJlNIjwygFw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/solid.min.css" integrity="sha512-uj2QCZdpo8PSbRGL/g5mXek6HM/APd7k/B5Hx/rkVFPNOxAQMXD+t+bG4Zv8OAdUpydZTU3UHmyjjiHv2Ww0PA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <link href="style.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="image/TM-logo-new-vedant-favicon.png">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h4>Admin Panel</h4>
  <a href="dashboard.php">Dashboard</a>
  <a href="unit.php">Manage Unit</a>
  <a href="branch.php">Manage Branch</a>
  <a href="front-desk.php">Manage Front-Desk</a>
  <a href="add-student.php">Add Student</a>
  <a href="student-list.php">Student List</a>
  
  <!--<a href="#">Settings</a>-->
</div>
<!-- Sidebar -->

<!-- Topbar -->
<div class="topbar">
  <h4 class="mb-0">New Vedant Group Of Institutions (NVGI)</h4>
  <button class="btn btn-danger btn-sm"><i class="fa fa-user"></i> Logout</button>
</div>
<!-- Topbar -->

<!-- Main Content -->
<div class="content">
  <h2 >Student Database</h2>
  <h6 class="mb-4 text-danger">Developer's Note : ID should be : NVGI/{Unit Name}{1st 3 letters of branch}/1,2,3...</h6>
  <div class="card shadow">
    <div class="card-body">
      <table id="example" class="table table-striped table-bordered align-middle text-center" style="width:100%">
        <thead class="table-dark">
          <tr>
            <th class="text-center">ID</th>
            <th class="text-center">Name of the Student</th>
            <th class="text-center">Contact</th>
            <th class="text-center">Unit</th>
            <th class="text-center">Branch</th>
            <th class="text-center">Class</th>
            <th class="text-center">Admitted On</th>
            <th class="text-center">Taken By</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>NVGI/VHSBIB/1</td><td><img src="https://placehold.co/300x200" style="width:35px; height:40px; border:1px solid #CCCCCC;"><br>Agni Jana</td><td>9830098300</td><td>VHS</td><td>Bibirhat</td><td>One</td><td>31.08.2025</td><td>Jayeeta</td><td><a href="#" title="View Details" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-eye text-success"></i></a> | <a href="#" title="Edit Student Data" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-edit text-primary"></i></a> | <a href="#" title="De-activate Student" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-trash text-danger"></i></a></td></tr>
          
          
          <tr><td>NVGI/TSAMUK/1</td><td><img src="https://placehold.co/300x200" style="width:35px; height:40px; border:1px solid #CCCCCC;"><br>Pradip Dhar</td><td>8017580175</td><td>TSA</td><td>Mukundapur</td><td>Eight</td><td>14.08.2025</td><td>Swastika</td><td><a href="#" title="View Details"><i class="fa fa-eye text-success"></i></a> | <a href="#" title="Edit Student Data"><i class="fa fa-edit text-primary"></i></a> | <a href="#" title="De-activate Student"><i class="fa fa-trash text-danger"></i></a></td></tr>
          
          
          <tr><td>NVGI/VHSRAJ/1</td><td><img src="https://placehold.co/300x200" style="width:35px; height:40px; border:1px solid #CCCCCC;"><br>Misti Dey</td><td>6258962589</td><td>VHS</td><td>Rajarhat</td><td>Toddler</td><td>16.08.2025</td><td>Sahana</td><td><a href="#" title="View Details" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-eye text-success"></i></a> | <a href="#" title="Edit Student Data" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-edit text-primary"></i></a> | <a href="#" title="De-activate Student" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-trash text-danger"></i></a></td></tr>
          
          
          <tr><td>NVGI/TSABIB/1</td><td><img src="https://placehold.co/300x200" style="width:35px; height:40px; border:1px solid #CCCCCC;"><br>Amlan Kumar Pramanick</td><td>7546975469</td><td>TSA</td><td>Bibirhat</td><td>Twelve</td><td>26.08.2025</td><td>Jayeeta</td><td><a href="#" data-bs-toggle="modal" data-bs-target="#studentDetails" title="View Details" data-bs-toggle="tooltip-second" data-bs-placement="left"><i class="fa fa-eye text-success"></i></a> | <a href="#" title="Edit Student Data" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-edit text-primary"></i></a> | <a href="#" title="De-activate Student" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-trash text-danger"></i></a></td></tr>
          
          
          <tr><td>NVGI/TSAMUK/2</td><td><img src="https://placehold.co/300x200" style="width:35px; height:40px; border:1px solid #CCCCCC;"><br>Sk Akhtar Ali</td><td>9798969594</td><td>TSA</td><td>Mukundapur</td><td>Six</td><td>26.08.2025</td><td>Swastika</td><td><a href="#" title="View Details" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-eye text-success"></i></a> | <a href="#" title="Edit Student Data" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-edit text-primary"></i></a> | <a href="#" title="De-activate Student" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-trash text-danger"></i></a></td></tr>
          
          
          <tr><td>NVGI/TSABIB/2</td><td><img src="https://placehold.co/300x200" style="width:35px; height:40px; border:1px solid #CCCCCC;"><br>Sajjad Hossain</td><td>6549871268</td><td>TSA</td><td>Bibirhat</td><td>Nine</td><td>26.08.2025</td><td>Sahana</td><td><a href="#" title="View Details" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-eye text-success"></i></a> | <a href="#" title="Edit Student Data" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-edit text-primary"></i></a> | <a href="#" title="De-activate Student" data-bs-toggle="tooltip" data-bs-placement="left"><i class="fa fa-trash text-danger"></i></a></td></tr>
          
          
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- Main Content -->



<!-- Modal -->
<div class="modal fade" id="studentDetails" tabindex="-1" aria-labelledby="studentDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="studentDetailsLabel">Amlan Kumar Pramanick</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3"><img src="https://placehold.co/300x200" style="width:70px; height:80px; border:1px solid #CCCCCC;"></div>
        <table class="table table-bordered align-middle">
        	<tr>
            	<td>ID : NVGI/VHSR/1</td>
                <td>Unit : VHS</td>
                <td>Branch : Rajarhat</td>
                <td>Session : 2025-26</td>
                <td>Admission Date : 26.08.2025</td>
                <td>Gender : Male</td>
            </tr>
            
            <tr>
            	
                <td>Religion : Hindu</td>
                <td>Caste : SC</td>
                <td>DOB : 26.01.2020</td>
                <td>PH : No</td>
                <td>Address : Address line one</td>
                <td>Pincode : 741259</td>
            </tr>
            
             <tr>
            	
                <td>Admitted (VHS) : Nursery</td>
                <td>Daycare : No</td>
                <td>Admitted (TSA) : Null</td>
                <td>Board (TSA) : Null</td>
                <td>Subject (TSA) : Null</td>
                <td>Medium (TSA) : Null</td>
            </tr>
            
            <tr>
            	
                <td>Father : Biplab Pramaick</td>
                <td>Occupation  : Business</td>
                <td>Mobile : 9834567892</td>
                <td>Mother : Bimala Pramanick</td>
                <td>Occupation : Housewife</td>
                <td>Mobile : 8978945689</td>
            </tr>
            
             <tr>
            	
                <td>Emergency  : Biplab Pramaick</td>
                <td>Mobile : 9834567892</td>
                <td>Relation : Father</td>
                <td>Sourse : Website</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            
           
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm">Print</button>
      </div>
    </div>
  </div>
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
  document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
    tooltipTriggerList.map(function (el) {
      return new bootstrap.Tooltip(el)
    })
  });
</script>

</body>
</html>
