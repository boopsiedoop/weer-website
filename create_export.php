<?php
session_start();
if ($_SESSION["loggedin"] != true) {
    die(json_encode([
        'error' => true,
        'message' => 'You are not logged in!',
    ]));
}

$benchmark_start = round(microtime(1) * 1000);

include 'connect_database.php';

$exportType = $_POST['export-type'];

$exportDataUnescaped = explode(',', $_POST['export-data']);
$exportData = [];
foreach($exportDataUnescaped as $entry) {
    array_push($exportData, '`' . $database_connection->real_escape_string($entry) . '`');
}

if($_POST['selected-stations'] === '') {
    die(json_encode([
        'error' => true,
        'message' => 'No stations selected!',
    ]));
}

if($_POST['selected-stations'] === 'all') {
    $stationsQuery = 'true';
}else{
    $selectedStationsUnescaped = explode(',', $_POST['selected-stations']);
    $selectedStations = [];
    foreach($selectedStationsUnescaped as $entry) {
        array_push($selectedStations, "'" . $database_connection->real_escape_string($entry) . "'");
    }

    $stationsQuery = 'station_id=' . implode(' OR station_id=', $selectedStations);
}

$maxRows = intval($_POST['max-rows']);

// strtotime returns an int so these values are safe for sql queries.
$startDatetime = strtotime($_POST['start-datetime']);
$endDatetime = strtotime($_POST['end-datetime']);

if($startDatetime >= $endDatetime) {
    die(json_encode([
        'error' => true,
        'message' => 'The end date/time should come after the start date/time.',
    ]));
}

// All the values have safe and/or escaped user data.
$query = 'SELECT id,station_id,date,' . join(',', $exportData) . ' FROM data WHERE (' . $stationsQuery . ') AND (date BETWEEN ' . $startDatetime . ' AND ' . $endDatetime . ') ORDER BY date DESC';
if($maxRows > 0) {
    // $maxRows is safe because it's an int!
    $query .= ' LIMIT ' . $maxRows;
}

function correctData(&$row, $stringBooleans = false) {
    if(isset($row['id'])) $row['id'] = intval($row['id']);
    if(isset($row['station_id'])) $row['station_id'] = intval($row['station_id']);
    if(isset($row['date'])) $row['date'] = date('Y-m-d G:i:s', $row['date']);
    if(isset($row['temperature'])) $row['temperature'] = floatval($row['temperature']);
    if(isset($row['dew_point'])) $row['dew_point'] = floatval($row['dew_point']);
    if(isset($row['station_air_pressure'])) $row['station_air_pressure'] = floatval($row['station_air_pressure']);
    if(isset($row['sea_air_pressure'])) $row['sea_air_pressure'] = floatval($row['sea_air_pressure']);
    if(isset($row['visibility'])) $row['visibility'] = floatval($row['visibility']);
    if(isset($row['wind_speed'])) $row['wind_speed'] = floatval($row['wind_speed']);
    if(isset($row['precipitation'])) $row['precipitation'] = floatval($row['precipitation']);
    if(isset($row['snow_height'])) $row['snow_height'] = floatval($row['snow_height']);
    if(isset($row['overcast'])) $row['overcast'] = floatval($row['overcast']);
    if(isset($row['wind_direction'])) $row['wind_direction'] = intval($row['wind_direction']);

    if(isset($row['has_frozen'])) $row['has_frozen'] = $row['has_frozen'] === '1';
    if(isset($row['has_rained'])) $row['has_rained'] = $row['has_rained'] === '1';
    if(isset($row['has_snowed'])) $row['has_snowed'] = $row['has_snowed'] === '1';
    if(isset($row['has_hailed'])) $row['has_hailed'] = $row['has_hailed'] === '1';
    if(isset($row['has_thundered'])) $row['has_thundered'] = $row['has_thundered'] === '1';
    if(isset($row['has_whirlwinded'])) $row['has_whirlwinded'] = $row['has_whirlwinded'] === '1';

    if($stringBooleans) {
        if(isset($row['has_frozen'])) $row['has_frozen'] = $row['has_frozen'] ? 'true' : 'false';
        if(isset($row['has_rained'])) $row['has_rained'] = $row['has_rained'] ? 'true' : 'false';
        if(isset($row['has_snowed'])) $row['has_snowed'] = $row['has_snowed'] ? 'true' : 'false';
        if(isset($row['has_hailed'])) $row['has_hailed'] = $row['has_hailed'] ? 'true' : 'false';
        if(isset($row['has_thundered'])) $row['has_thundered'] = $row['has_thundered'] ? 'true' : 'false';
        if(isset($row['has_whirlwinded'])) $row['has_whirlwinded'] = $row['has_whirlwinded'] ? 'true' : 'false';
    }

    return $row;
}

$directory = 'exports';
if(!is_dir($directory)) {
    mkdir($directory);
}

$exportFileName = $directory . '/export-' . time() . '.' . $exportType;
$exportFile = fopen($exportFileName, "w");

if(!$exportFile) {
    die(json_encode([
        'error' => true,
        'message' => "Could not open file $exportFileName",
    ]));
}

$result = $database_connection->query($query);

if($exportType === 'json') {
    fwrite($exportFile, '[');

    $first = true;
    foreach($result as $row) {
        correctData($row);

        $line = '';
        if($first) {
            $first = false;
        }else{
            $line .= ',';
        }

        $line .= json_encode($row);

        fwrite($exportFile, $line);
    }

    fwrite($exportFile, ']');
}else if($exportType == 'csv') {
    $first = true;
    foreach($result as $row) {
        correctData($row, true);

        $line = '';
        if($first) {
            $first = false;

            // Add header
            fwrite($exportFile, join(',', array_keys($row)) . "\n");
        }

        fwrite($exportFile, join(',', array_values($row)) . "\n");
    }
}else if($exportType == 'xml') {
    fwrite($exportFile, "<?xml version=\"1.0\"?>\n");
    fwrite($exportFile, "<DATAPOINTS>\n");

    foreach($result as $row) {
        correctData($row, true);

        $str = "  <DATAPOINT>\n";

        foreach($row as $key => $value) {
            $keyName = strtoupper($key);
            $str .= "    <$keyName>$value</$keyName>\n";
        }

        $str .= "  </DATAPOINT>\n";

        fwrite($exportFile, $str);
    }

    fwrite($exportFile, "</DATAPOINTS>\n");
}

fclose($exportFile);

$benchmark_elapsed = round(microtime(1) * 1000) - $benchmark_start;

echo(json_encode([
    'error' => false,
    'message' => 'Exported ' . $result->num_rows . ' rows in ' . $benchmark_elapsed . 'ms.',
    'url' => $exportFileName,
    'name' => 'export.' . $exportType,
]));
