<?php

session_start();
if ($_SESSION["loggedin"] != true) {
  die(json_encode([]));
}

include 'connect_database.php';
$id = $database_connection->real_escape_string($_GET['id']);
$date = $database_connection->real_escape_string($_GET['date']);
$type = $database_connection->real_escape_string($_GET['type']);

// Connectie maken met de database ....
if($type == 0){
  $result = $database_connection->query("SELECT * FROM data WHERE station_id =".$id." ORDER BY ABS(date - ".$date.") ASC LIMIT 1");
}
elseif($type == 1){
  $result = $database_connection->query("SELECT * FROM data WHERE date BETWEEN (SELECT date FROM data ORDER BY ABS(date - ".$date.") ASC LIMIT 1) AND ((SELECT date FROM data ORDER BY ABS(date - ".$date.") ASC LIMIT 1) + 50) ORDER BY snow_height DESC LIMIT 1");
}
elseif($type == 2){
  $result = $database_connection->query("SELECT * FROM data WHERE date BETWEEN (SELECT date FROM data ORDER BY ABS(date - ".$date.") ASC LIMIT 1) AND ((SELECT date FROM data ORDER BY ABS(date - ".$date.") ASC LIMIT 1) + 50) ORDER BY temperature DESC LIMIT 1");
}
elseif($type == 3){
  $result = $database_connection->query("SELECT * FROM data WHERE date BETWEEN (SELECT date FROM data ORDER BY ABS(date - ".$date.") ASC LIMIT 1) AND ((SELECT date FROM data ORDER BY ABS(date - ".$date.") ASC LIMIT 1) + 50) ORDER BY temperature LIMIT 1");
}

$answer = array();



foreach($result as $row) {
  array_push($answer, $row);
}

echo json_encode($answer);

?>
