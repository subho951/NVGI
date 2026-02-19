<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NVGI - Add Student</title>

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
    <h2>Add Student</h2>
    <h6 class="mb-4 text-danger">Developer's Note : ID should be : NVGI/{Unit Name}{1st 3 letters of branch}/1,2,3...</h6>
    <div class="card shadow bg-light">
      <div class="card-body">

        <form class="row g-3">

          <!-- Row 1: Three Inputs -->
          <div class="col-md-2">
            <label>Admission Taken By</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Jayeeta">Jayeeta</option>
              <option value="Swastika">Swastika</option>
              <option value="Sahana">Sahana</option>
              <option value="Abirlal">Abirlal</option>
            </select>
          </div>


          <div class="col-md-2">
            <label>Unit</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="VHS">VHS</option>
              <option value="TSA">TSA</option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Branch</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Bibirhat">Bibirhat</option>
              <option value="Rajarhat">Rajarhat</option>
              <option value="Mukundapur">Mukundapur</option>
            </select>
          </div>


          <div class="col-md-2">
            <label>Session</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="2025-2026">2025-2026</option>
              <option value="2026-2027">2026-2027</option>
              <option value="2027-2028">2027-2028</option>
              <option value="2028-2029">2028-2029</option>
              <option value="2029-2030">2029-2030</option>
            </select>
          </div>



          <div class="col-md-2">
            <label>Date of Admission</label>
            <input type="Date" class="form-control  form-control-sm" placeholder="First Name">
          </div>

          <div class="col-md-2">
            <label>Student ID (Auto-gen)</label>
            <input type="text" class="form-control form-control-sm" readonly placeholder="NVGI/VHSRAJ/1">
          </div>



          <div class="col-md-2">
            <label>First Name</label>
            <input type="text" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Middle Name</label>
            <input type="text" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Last Name</label>
            <input type="text" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Gender</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Religion</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Hindu">Hindu</option>
              <option value="Muslim">Muslim</option>
              <option value="Christian">Christian</option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Caste</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="General">General</option>
              <option value="SC">SC</option>
              <option value="ST">ST</option>
              <option value="OBC-A">OBC-A</option>
              <option value="OBC-B">OBC-B</option>
            </select>
          </div>




          <div class="col-md-2">
            <label>Date of Birth</label>
            <input type="date" class="form-control form-control-sm" placeholder="">
          </div>



          <div class="col-md-2">
            <label>Is PH?</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="No">No</option>
              <option value="Yes">Yes</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Permanent Address</label>
            <input type="text" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Pincode</label>
            <input type="number" class="form-control form-control-sm" placeholder="">
          </div>


          <div class="col-md-2">
            <label>Admitted in (VHS Only)</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Toddler">Toddler</option>
              <option value="Nursery">Nursery</option>
              <option value="LKG">LKG</option>
              <option value="UKG">UKG</option>
              <option value="Class-I">Class-I</option>
              <option value="Class-II">Class-II</option>
              <option value="Class-III">Class-III</option>
              <option value="Class-IV">Class-IV</option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Day Care (VHS Only)</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="No">No</option>
              <option value="Yes">Yes</option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Admitted in (TSA Only)</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Class-I">Class-I</option>
              <option value="Class-II">Class-II</option>
              <option value="Class-III">Class-III</option>
              <option value="Class-IV">Class-IV</option>
              <option value="Class-V">Class-V</option>
              <option value="Class-VI">Class-VI</option>
              <option value="Class-VII">Class-VII</option>
              <option value="Class-VIII">Class-VIII</option>
              <option value="Class-IX">Class-IX</option>
              <option value="Class-X">Class-X</option>
              <option value="Class-XI">Class-XI</option>
              <option value="Class-XII">Class-XII</option>
              <option value="1st Year">1st Year</option>
              <option value="2nd Year">2nd Year</option>
              <option value="3rd Year">3rd Year</option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Board (TSA Only)</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Class-I">CBSE</option>
              <option value="Class-II">ICSE</option>
              <option value="Class-III">WBBSE</option>
              <option value="Class-IV">WBCHSE</option>
              <option value="Class-V">ISC</option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Subject (TSA Only)</label>
            <div class="dropdown">
              <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100" type="button" id="multiSelectDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Click here
              </button>
              <ul class="dropdown-menu w-100" aria-labelledby="multiSelectDropdown">
                <li><label class="dropdown-item"><input type="checkbox" value="Bengali" class="form-check-input me-2"> Bengali</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="English" class="form-check-input me-2"> English</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="Mathematics" class="form-check-input me-2"> Mathematics</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="Physics" class="form-check-input me-2"> Physics</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="Chemistry" class="form-check-input me-2"> Chemistry</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="Biology" class="form-check-input me-2"> Biology</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="Sanskrit" class="form-check-input me-2"> Sanskrit</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="SST" class="form-check-input me-2"> SST</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="Political Science" class="form-check-input me-2"> Political Science</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="Accountancy" class="form-check-input me-2"> Accountancy</label></li>
                <li><label class="dropdown-item"><input type="checkbox" value="Spoken English" class="form-check-input me-2"> Spoken English</label></li>
              </ul>
            </div>
          </div>

          <div class="col-md-2">
            <label>Medium (TSA Only)</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Bengali">Bengali</option>
              <option value="English">English</option>
            </select>
          </div>


          <div class="col-md-2">
            <label>Name of the Father</label>
            <input type="text" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Occupation</label>
            <input type="text" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Mobile Number</label>
            <input type="number" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Name of the Mother</label>
            <input type="text" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Occupation</label>
            <input type="text" class="form-control form-control-sm" placeholder="">
          </div>

          <div class="col-md-2">
            <label>Mobile Number</label>
            <input type="number" class="form-control form-control-sm" placeholder="">
          </div>


          <div class="col-md-2">
            <label>Emergency Info</label>
            <input type="text" class="form-control form-control-sm" placeholder="Name">
          </div>

          <div class="col-md-2">
            <label>&nbsp;</label>
            <input type="number" class="form-control form-control-sm" placeholder="Contact Number">
          </div>

          <div class="col-md-2">
            <label>&nbsp;</label>
            <input type="text" class="form-control form-control-sm" placeholder="Relation">
          </div>

          <div class="col-md-3">
            <label>How did they know about us?</label>
            <select class="form-select form-select-sm">
              <option selected disabled>Click here</option>
              <option value="Hoarding">Hoarding</option>
              <option value="Website">Website</option>
              <option value="Hoarding">FB/Whatsapp</option>
              <option value="Friends/Relatives">Friends/Relatives</option>
              <option value="Local">Local</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <div class="col-md-3">
            <label>Upload Photo (jpg or png)</label>
            <input type="file" class="form-control form-control-sm" placeholder="">
          </div>



          <!-- Row 3: Submit Button -->
          <div class="col-12">
            <button type="submit" class="btn btn-success w-100">Submit</button>
          </div>

        </form>

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
    document.addEventListener('DOMContentLoaded', function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      })
    });
  </script>

</body>

</html>