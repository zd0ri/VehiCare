<?php
if (isset($_SESSION['message'])) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert' style='margin: 20px;'>
    <i class='fas fa-exclamation-circle'></i> <strong>{$_SESSION['message']}</strong>
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    unset($_SESSION['message']);
}

if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert' style='margin: 20px;'>
    <i class='fas fa-check-circle'></i> <strong>{$_SESSION['success']}</strong>
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert' style='margin: 20px;'>
    <i class='fas fa-times-circle'></i> <strong>{$_SESSION['error']}</strong>
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    unset($_SESSION['error']);
}
?>