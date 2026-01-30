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
  <nav class="navbar dark navbar-expand-lg" style="background: transparent; padding: 20px 40px;">
    <div class="container-fluid" style="background: #2c3e50; border-radius: 50px; padding: 12px 30px; display: flex; align-items: center; gap: 30px;">
      <a class="navbar-brand" href="/vehicare_db/index.php" style="font-size: 24px; font-weight: 700; color: #fff; display: flex; align-items: center; margin: 0;">
        <i class="fas fa-car" style="margin-right: 8px;"></i> VehiCare
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
        aria-label="Toggle navigation" style="border-color: rgba(255,255,255,0.5);">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent" style="flex: 1;">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="gap: 10px;">
          <li class="nav-item">
            <a class="nav-link" href="/vehicare_db/index.php" style="color: #fff; padding: 8px 16px;">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/vehicare_db/services.php" style="color: #fff; padding: 8px 16px;">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/vehicare_db/about.php" style="color: #fff; padding: 8px 16px;">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/vehicare_db/contact.php" style="color: #fff; padding: 8px 16px;">Contact</a>
          </li>
          <?php
          if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
              if ($_SESSION['role'] === 'admin') {
                  echo '<li class="nav-item"><a class="nav-link" href="/vehicare_db/admins/dashboard.php" style="color: #fff; padding: 8px 16px;">Dashboard</a></li>';
              } elseif ($_SESSION['role'] === 'client') {
                  echo '<li class="nav-item"><a class="nav-link" href="/vehicare_db/client/dashboard.php" style="color: #fff; padding: 8px 16px;">Dashboard</a></li>';
              } elseif ($_SESSION['role'] === 'staff') {
                  echo '<li class="nav-item"><a class="nav-link" href="/vehicare_db/staff/dashboard.php" style="color: #fff; padding: 8px 16px;">Dashboard</a></li>';
              }
          }
          ?>
        </ul>
        
        <div class="d-flex align-items-center" style="gap: 10px;">
          <?php
          if (!isset($_SESSION['user_id'])) {
            echo "<a href='/vehicare_db/contact.php' class='btn btn-sm' style='background: transparent; color: #fff; border: none; padding: 8px 16px; font-weight: 500;'>Contact</a>";
            echo "<a href='/vehicare_db/register.php' class='btn btn-sm' style='background: transparent; color: #fff; border: 1px solid #fff; padding: 8px 16px; border-radius: 6px; font-weight: 500;'>Register</a>";
            echo "<a href='/vehicare_db/login.php' class='btn btn-sm btn-light' style='padding: 8px 20px; border-radius: 6px; font-weight: 600;'>Sign In</a>";
          } else {
            echo "<span style='color: #fff; font-size: 0.9em;'>{$_SESSION['email']}</span>";
            if ($_SESSION['role'] === 'client') {
              echo "<a href='/vehicare_db/client/dashboard.php' class='btn btn-sm btn-light' style='padding: 8px 20px; border-radius: 6px; font-weight: 600;'>Dashboard</a>";
            }
            echo "<a href='/vehicare_db/logout.php' class='btn btn-sm' style='background: transparent; color: #fff; border: 1px solid #fff; padding: 8px 16px; border-radius: 6px; font-weight: 500;'>Logout</a>";
          }
          ?>
        </div>
      </div>
    </div>
  </nav>
  <?php include_once __DIR__ . '/alert.php'; ?>
