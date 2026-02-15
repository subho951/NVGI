<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NVGI - Dashboard</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  

  
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
  <h2 >Dashboard</h2>
  <!--<h6 class="mb-4 text-danger">Developer's Note : ID should be : NVGI/{Unit Name}{1st letter of branch}/1,2,3...</h6>-->
  <div class="card shadow bg-light mb-4">
    <div class="card-body">
     <div class="row">
      	<div class="col-md-3">
        	<div class="card shadow bg-info ">
            	<div class="card-body text-center text-white">
                <h5>VHS Bibirhat</h5>
                <h2>19</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
        	<div class="card shadow bg-info ">
            	<div class="card-body text-center text-white">
                <h5>VHS Rajarhat</h5>
                <h2>4</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
        	<div class="card shadow bg-info ">
            	<div class="card-body text-center text-white">
                <h5>TSA Bibirhat</h5>
                <h2>239</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
        	<div class="card shadow bg-info ">
            	<div class="card-body text-center text-white">
                <h5>TSA Mukundapur</h5>
                <h2>6</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
        	<div class="card shadow bg-warning ">
            	<div class="card-body text-center text-white">
                <h5>Total VHS Students : 23</h5>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
        	<div class="card shadow bg-warning ">
            	<div class="card-body text-center text-white">
                <h5>Total TSA Students : 245</h5>
                </div>
            </div>
        </div>
        
     </div>   
      
    </div>
  </div>
  
  <div class="card shadow bg-light">
    <div class="card-body">
     <div class="row">
      	<div class="col-md-2">
        	<div class="card shadow bg-success ">
            	<div class="card-body text-center text-white">
                <h5>Jayeeta</h5>
                <h2>126</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
        	<div class="card shadow bg-success ">
            	<div class="card-body text-center text-white">
                <h5>Swastika</h5>
                <h2>4</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
        	<div class="card shadow bg-success ">
            	<div class="card-body text-center text-white">
                <h5>Sahana</h5>
                <h2>239</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
        	<div class="card shadow bg-success ">
            	<div class="card-body text-center text-white">
                <h5>Abirlal</h5>
                <h2>6</h2>
                </div>
            </div>
        </div>
        
        
        <div class="col-md-2">
        	<div class="card shadow bg-success ">
            	<div class="card-body text-center text-white">
                <h5>Sweety</h5>
                <h2>6</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
        	<div class="card shadow bg-success ">
            	<div class="card-body text-center text-white">
                <h5>Ruma</h5>
                <h2>6</h2>
                </div>
            </div>
        </div>
        
        
        
     </div>   
      
    </div>
  </div>
  
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




<!-- Initialize Tooltips -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      })
    });
  </script>

</body>
</html>
