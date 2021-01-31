<?php
session_start();
// username and password sent from form
$username=$_POST['uname'];
$password=$_POST['psw'];
  //check username and password
  if($username == "DEOL-partner" && $password =="pwd123"){
    // if correct then go to homepage and log in to the session
    $_SESSION["loggedin"] = true;
    header("location: index.php");
    exit;
  }
  else{
    // if wrong then go back to login page and give error message
    header("Location: login.php?message=Wrong password and/or username please try again");
  }
?>
