<?php

session_start();
if ($_SESSION["loggedin"] != true) {
  die(json_encode([]));
}

include 'connect_database.php';
$id = $database_connection->real_escape_string($_GET['id']);

// Connectie maken met de database ....
$result = $database_connection->query("SELECT * FROM data WHERE station_id =".$id." ORDER BY date DESC ");
$answer = array();



foreach($result as $row) {
  array_push($answer, $row);
}

echo json_encode($answer);

?>
