<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Page - Employee Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
      margin: 0;
      background: url('image/bg2.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-box {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 350px;
      background-color: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
    .login-box h2 {
      margin-bottom: 25px;
      text-align: center;
    }
	
	 /* Green border + optional subtle glow on focus */
    .form-control:focus, 
    .form-select:focus, 
    textarea:focus {
      border-color: #28a745 !important;         /* Green border */
      box-shadow: 0 0 5px rgba(40, 167, 69, 0.4) !important; /* Subtle green glow */
    }
	
	label{font-weight:600;}
	
	.bg-success{background-color:#99B138 !important;}
	
	.bg-info{background-color:#24A0AE !important;}
	
	.bg-dark{background-color:#5F5F5F !important;}
	
	.bg-danger{background-color:#E35353 !important;}
	
	.bg-warning{background-color:#E78245 !important;}
	
	.btn-danger {
    --bs-btn-bg: #f55e5efa !important;}
	
	.btn-success {
    --bs-btn-bg: #99B138 !important;}
	
	.text-success{color:#99B138 !important;}
	
	.text-danger{color:#f55e5efa !important;}
	
	.text-primary{color:#105870 !important;}
	
	 @media (max-width: 767px) {
      .sidebar {
        width: 200px;
      }
      .topbar, .content {
        margin-left: 200px;
      }
    }
  </style>
</head>
<body>

  <div class="login-box">
    <h4 class="text-primary text-center"><img src="image/TM-logo-new-vedant.png" class="w-100"><br>Employee Management</h4>
    <form action="#">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" placeholder="Enter username">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" placeholder="Enter password">
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
