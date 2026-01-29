<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https:
    <link rel="stylesheet" href="https:
    <link href="/vehicare_db/includes/style/admin.css" rel="stylesheet" type="text/css">
    <script src="https:
    <title>VehiCare Admin Dashboard</title>
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background: linear-gradient(135deg, 
  <div class="container-fluid">
    <a class="navbar-brand" href="/vehicare_db/admins/dashboard.php" style="color: 
    <form class="d-flex ms-auto" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET">
      <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="search">
      <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
    </form>
    <div style="margin-left: 20px;">
      <a href="/vehicare_db/logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>
