<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="/vehicare_db/includes/style/admin.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <title>VehiCare Admin Dashboard</title>
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%); box-shadow:0 2px 8px rgba(0,0,0,0.15);">
  <div class="container-fluid">
    <a class="navbar-brand" href="/vehicare_db/admins/dashboard.php" style="color: #fff; font-weight: bold;"><i class="fas fa-car"></i> VehiCare Admin</a>
    <form class="d-flex ms-auto" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET">
      <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="search">
      <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
    </form>
    <div style="margin-left: 20px;">
      <a href="/vehicare_db/logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>