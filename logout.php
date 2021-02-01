<link rel="stylesheet" href="style.css">
<?php
session_start();
session_destroy();
header("Location: login.php");
?>
