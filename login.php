<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
header("location: index.php");
}

//get error message if present
if(!empty($_GET['message'])) {
    $message = $_GET['message'];{
    }
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link rel="stylesheet" href="style.css">
    <meta charset="utf-8">
    <title>Login page</title>
    <img src= "img\DEOL-Partners-Logotype.png"  class="center" style="width:400px;height:75px;">


  </head>
  <body>
    <form action="checking.php" method="post">
      <div class="flex-container">
        <label class="inlog" for="uname"><b>Username:</b></label>
        <input type="text" placeholder="Enter Username" name="uname" required>
      <br>
        <label class="inlog" for="psw"><b>Password:</b></label>
        <input type="password" placeholder="Enter Password" name="psw" required>
      <br>
        <button type="submit">Login</button>
      <br>
        <?php //post error message
        if(!empty($_GET['message'])) { echo "$message";}
        ?>
      </div>
    </form>
  </body>
</html>
