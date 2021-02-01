<?php

session_start();
if ($_SESSION["loggedin"] != true) {
  die(json_encode([]));
}

include 'connect_database.php';
$id = $database_connection->real_escape_string($_GET['id']);
$date = $database_connection->real_escape_string($_GET['date']);

// Connectie maken met de database ....
$result = $database_connection->query("SELECT * FROM data WHERE station_id =".$id." ORDER BY ABS(date - ".$date.") ASC LIMIT 1");
$answer = array();

//" AND date BETWEEN ".$date." AND ".$date2.

foreach($result as $row) {
  array_push($answer, $row);
}

echo json_encode($answer);
//",&date="+dataDate,
?>
