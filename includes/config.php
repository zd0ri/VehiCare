<?php



$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicare_db";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->set_charset("utf8");


define('BASE_URL', 'http://localhost/vehicare_db/');
define('ADMIN_URL', 'http://localhost/vehicare_db/admins/');
?>

