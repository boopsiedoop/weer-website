<link rel="stylesheet" href="style.css">
<?php
session_start();
session_destroy();
echo 'You have been logged out. <a href="login.php">login</a>';
?>
