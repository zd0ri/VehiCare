<?php
session_start();
session_destroy();
header("Location: /vehicare_db/index.php");
exit;
?>
