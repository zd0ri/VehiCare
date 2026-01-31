<?php
// Generate bcrypt hash for password
$password = "Tech@123";
$hashed = password_hash($password, PASSWORD_BCRYPT);
echo "Password: " . $password . "\n";
echo "Hash: " . $hashed . "\n";
?>
