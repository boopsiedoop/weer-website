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
}else{
  $query = "
    SELECT *
    FROM data
    WHERE station_id IN (
        SELECT stn
        FROM stations
        WHERE (country='NORWAY' OR country='SWEDEN' OR country='DENMARK' OR country='ICELAND' OR country='FINLAND' OR country='FAROE ISLANDS')
    )
    AND date BETWEEN (
        SELECT date
        FROM data
        WHERE station_id IN (
            SELECT stn
            FROM stations
            WHERE (country='NORWAY' OR country='SWEDEN' OR country='DENMARK' OR country='ICELAND' OR country='FINLAND' OR country='FAROE ISLANDS')
        )
        ORDER BY ABS(date - $date) ASC
        LIMIT 1
    ) AND (
        (
            SELECT date
            FROM data
            WHERE station_id IN (
                SELECT stn
                FROM stations
                WHERE (country='NORWAY' OR country='SWEDEN' OR country='DENMARK' OR country='ICELAND' OR country='FINLAND' OR country='FAROE ISLANDS')
            )
            ORDER BY ABS(date - $date) ASC
            LIMIT 1
        ) + 50
    )";

  if($type == 1){
    $query = "$query ORDER BY snow_height DESC LIMIT 1";
  }
  elseif($type == 2){
    $query = "$query ORDER BY temperature DESC LIMIT 1";
  }
  elseif($type == 3){
    $query = "$query ORDER BY temperature ASC LIMIT 1";
  }

  $result = $database_connection->query($query);
}

$answer = array();



foreach($result as $row) {
  array_push($answer, $row);
}

echo json_encode($answer);

?>
