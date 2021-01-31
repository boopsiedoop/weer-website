<?php

session_start();
if ($_SESSION["loggedin"] != true) {
  die(json_encode([]));
}

include 'Connect_Database.php';
$id = $_GET['id'];

// Connectie maken met de database ....
$result = database("SELECT * FROM data WHERE station_id =".$id." ORDER BY date DESC ");
$answer = array();



foreach($result as $row) {
  array_push($answer, $row);
}

echo json_encode($answer);

?>
