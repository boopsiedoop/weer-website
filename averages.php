<?php
session_start();

if ($_SESSION["loggedin"] != true) {
header("location: login.php");
die();
}
if(!isset($_GET['start-time']) || !isset($_GET['end-time'])) {
    $startTime = strtotime('-1 month');
    $endTime = strtotime('today');
    header("Location: ?start-time=$startTime&end-time=$endTime");
    die();
}

$startTime = $_GET['start-time'];
$endTime = $_GET['end-time'];

// $startTime and $endTime are definitely an int, so they are safe to be used in sql queries
if(is_numeric($startTime)) $startTime = intval($startTime);
else $startTime = strtotime($startTime);

if(is_numeric($endTime)) $endTime = intval($endTime);
else $endTime = strtotime($endTime);

include 'connect_database.php';
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/dataTables.bootstrap4.min.css"/>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="bootstrap-dark.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        .table-div {
            padding: 15px;
        }
    </style>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(() => {
            $('#data-table').DataTable();
        });
    </script>
    <div class="topnav">
      <div class="logo-image">
            <img src= "img\DEOL-Partners-Logotype.png" class="img-fluid">
      </div>
      <a href="index.php">Homepage</a>
      <a href="export.php">Export</a>
      <a href="logout.php" style="float: right; padding-right: 60px">Logout</a>

    </div>
</head>
<body>
<div class="container">
    <h1 class="text-center">Average station data</h1>
</div>

<br>

<div class="table-div" style="color: #B0B0B0;">
    <table id="data-table" width="100%" style="color: #B0B0B0;">
        <thead>
        <tr>
            <th>St. ID</th>
            <th>St. Country</th>
            <th>St. Name</th>
            <th>St. Coordinates (lat, lon)</th>
            <th>St. Elevation (m)</th>
            <th>Temperature (°C)</th>
            <th>Dew point (°C)</th>
            <th>Humidity (%)</th>
            <th>Station air pressure (mbar)</th>
            <th>Sea air pressure (mbar)</th>
            <th>Visibility (km)</th>
            <th>Wind speed (km/h)</th>
            <th>Precipitation (cm)</th>
            <th>Snow height (cm)</th>
            <th>Overcast (%)</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // $startTime and $endTime are definitely an int, so they are safe to be used in sql queries
        $query = "
SELECT stn, country, name, latitude, longitude, elevation, `AVG(temperature)`, `AVG(dew_point)`, `AVG(station_air_pressure)`, `AVG(sea_air_pressure)`, `AVG(visibility)`, `AVG(wind_speed)`, `AVG(precipitation)`, `AVG(snow_height)`, `AVG(overcast)`
FROM (
  SELECT station_id, AVG(temperature), AVG(dew_point), AVG(station_air_pressure), AVG(sea_air_pressure), AVG(visibility), AVG(wind_speed), AVG(precipitation), AVG(snow_height), AVG(overcast)
  FROM data
  WHERE
    (date BETWEEN $startTime AND $endTime)
    AND
    station_id IN (
      SELECT stn
      FROM stations
      WHERE (country='NORWAY' OR country='SWEDEN' OR country='DENMARK' OR country='ICELAND' OR country='FINLAND' OR country='FAROE ISLANDS')
    )
  GROUP BY station_id
) averages
JOIN stations
ON averages.station_id = stations.stn";

        $result = $database_connection->query($query);
        foreach($result as $row) {
            echo '<tr>';

            $temperature = $row['AVG(temperature)'];
            $dew_point = $row['AVG(dew_point)'];

            // The UnwdmiGenerator21 generator generates wrong data.
            // The temperature can PHYSICALLY only be the same or higher than the dewpoint. However, the generator does not oby the physics rules.
            // We have to implement this patch to "correct" the data....
            if($temperature < $dew_point) {
                $dew_point = $temperature;
            }

            $humidity = 5 * $dew_point - 5 * $temperature + 100;

            echo '<th>' . $row['stn'] . '</th>';
            echo '<th>' . $row['country'] . '</th>';
            echo '<th>' . $row['name'] . '</th>';
            echo '<th>' . number_format($row['latitude'], 2) . ', ' . number_format($row['longitude'], 2) . '</th>';
            echo '<th>' . $row['elevation'] . '</th>';
            echo '<th>' . number_format($temperature, 1, '.', '') . '</th>';
            echo '<th>' . number_format($dew_point, 1, '.', '') . '</th>';
            echo '<th>' . number_format($humidity, 1, '.', '') . '</th>';
            echo '<th>' . number_format($row['AVG(station_air_pressure)'], 1, '.', '') . '</th>';
            echo '<th>' . number_format($row['AVG(sea_air_pressure)'], 1, '.', '') . '</th>';
            echo '<th>' . number_format($row['AVG(visibility)'], 1, '.', '') . '</th>';
            echo '<th>' . number_format($row['AVG(wind_speed)'], 1, '.', '') . '</th>';
            echo '<th>' . number_format($row['AVG(precipitation)'], 1, '.', '') . '</th>';
            echo '<th>' . number_format($row['AVG(snow_height)'], 1, '.', '') . '</th>';
            echo '<th>' . number_format($row['AVG(overcast)'], 1, '.', '') . '</th>';

            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</div>

<br><br>

<div class="container" style="color: #B0B0B0;">
    <form action="" method="GET">
        <div class="row">
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="start-datetime">Start datetime</label>
                    <input id="start-datetime" name="start-time" class="form-control" type="datetime-local" value="<?= date('Y-m-d\TG:i', strtotime("-1 month")) ?>">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="end-datetime">End datetime</label>
                    <input id="end-datetime" name="end-time" class="form-control" type="datetime-local" value="<?= date('Y-m-d\TG:i') ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                <button type="submit" class="btn btn-primary" style="width:100%">Update</button>
            </div>
        </div>
    </form>
</div>

</body>
</html>
