<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
 
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="/vehicare_db/includes/style/style.css?v=<?php echo time(); ?>" rel="stylesheet">
  <?php
  $isAdminPage = strpos($_SERVER['REQUEST_URI'], '/admins/') !== false;
  if ($isAdminPage) {
    echo '<link href="/vehicare_db/includes/style/admin.css" rel="stylesheet">' . "\n";
  }
  ?>
  <style>
    
    .admin-sidebar-shared {
      position: static;
      float: left;
      width: 220px;
      background: #fff;
      border-right: 1px solid #dfe6e9;
      padding: 20px 0;
      z-index: 1000;
    }

    body.admin-mode .admin-sidebar-shared .list-group-item {
      background: transparent;
      border: none;
      padding: 12px 20px;
      border-radius: 0;
      margin: 0;
      color: #5A4939;
      font-weight: 500;
      transition: all 0.2s ease;
      cursor: pointer;
      border-left: 3px solid transparent;
    }

    body.admin-mode .admin-sidebar-shared .list-group-item:hover {
      background: #f0e9ff;
      color: #5A4939;
      border-left-color: #5A4939;
    }

    body.admin-mode .admin-sidebar-shared .list-group-item.active {
      background: #f0e9ff;
      color: #5A4939;;
      border-left-color: #5A4939;
    }

   
    .admin-main-content {
      margin-left: 240px;
      padding: 28px;
      min-height: calc(100vh - 80px);
      overflow: visible;
    }

    @media (max-width: 992px) {
      .admin-sidebar-shared {
        float: none;
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #dfe6e9;
      }

      .admin-main-content {
        margin-left: 0;
      }
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous" >
  </script>
  <title>VehiCare - Vehicle Maintenance System</title>
  <script>
    if (window.location.pathname.includes('/admins/')) {
      document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('admin-mode');
      });
    }
  </script>
</head>

<body>
  <nav class="navbar dark navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="/vehicare_db/index.php" style="font-size: 24px; font-weight: 700; color: #fff; display: flex; align-items: center;">
        <i class="fas fa-car" style="margin-right: 8px;"></i> VehiCare
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="/vehicare_db/index.php" style="color: #fff;">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/vehicare_db/index.php#services" style="color: #fff;">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/vehicare_db/index.php#about" style="color: #fff;">About</a>
          </li>
          <?php
          if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
              echo '<li class="nav-item"><a class="nav-link" href="/vehicare_db/admins/dashboard.php" style="color: #fff;">Dashboard</a></li>';
          }
          ?>
        </ul>
        
        <div class="d-flex align-items-center">
          <?php
          if (!isset($_SESSION['user_id'])) {
            echo "<a href='#contact' class='btn btn-light' style='margin-right: 10px;'>Contact</a>";
            echo "<a href='/vehicare_db/register.php' class='btn btn-outline-light' style='margin-right: 10px;'>Register</a>";
            echo "<a href='#' class='btn btn-outline-light' style='margin-right: 10px;'>Book Appointment</a>";
            echo "<a href='/vehicare_db/login.php' class='btn btn-warning' style='background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border: none; color: #fff; font-weight: 600;'>Sign In</a>";
          } else {
            echo "<span class='me-3' style='color: #fff;'>{$_SESSION['email']}</span>";
            echo "<a href='/vehicare_db/logout.php' class='btn btn-outline-light'>Logout</a>";
          }
          ?>
        </div>
      </div>
    </div>
  </nav>
  <?php include_once __DIR__ . '/alert.php'; ?>
