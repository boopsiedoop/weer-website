<?php
include 'connect_database.php';
session_start();

if ($_SESSION["loggedin"] != true) {
header("location: login.php");
die();
}
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/dataTables.bootstrap4.min.css"/>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="bootstrap-dark.min.css">
    <link rel="stylesheet" href="style.css">

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(() => {
            $('#export-form').submit(function(e) {
                let selectedStationsStr;
                if(selectedStations_all) {
                    selectedStationsStr = 'all';
                }else{
                    selectedStationsStr = selectedStations.join(',');
                }

                let exportData = [];
                $('[id^=data-]').each((index, element) => {
                    if(element.checked) {
                        let name = element.id.substr('data-'.length);
                        exportData.push(name);
                    }
                });
                let exportDataStr = exportData.join(',');

                let data = $('#export-form').serialize() + `&export-data=${exportDataStr}&selected-stations=${selectedStationsStr}`;
                console.log(data);

                $('#export-status').html('<i class="fas fa-sync fa-spin"></i> Exporting..');

                $.post({
                    url: 'create_export.php',
                    data: data,
                    success: result => {
                        if(result.error) {
                            $('#export-status').html('<i class="fas fa-exclamation-circle"></i> Warning! ' + result.message);
                            return;
                        }

                        $('#export-status').html('<i class="fas fa-check-circle"></i> Export done! Your download should begin shortly. ' + result.message);

                        let link = document.createElement('a');
                        link.download = result.name;
                        link.href = result.url;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    },
                    error: err => {
                        $('#export-status').html('<i class="fas fa-exclamation-circle"></i> Error! ' + err.responseText);
                    },
                    dataType: 'json',
                });

                setTimeout(() => {

                }, 1000);

                return false;
            });

            $('#stations-table').DataTable();
            $('#stations-table_wrapper').click(function(e) {
                registerClickListeners();
                updateVisibleCheckboxes();
            });

            let selectedStations = [];
            let selectedStations_all = false;

            $('#select-all').on('input', function(e) {
                if(this.checked) {
                    selectedStations = [];
                    selectedStations_all = true;
                }else{
                    selectedStations = [];
                    selectedStations_all = false;
                }

                updateVisibleCheckboxes();
            });

            function updateVisibleCheckboxes() {
                $('.station-checkbox').each((index, box) => {
                    let id = parseInt(box.id.substr('station-'.length));
                    let checked = selectedStations_all || selectedStations.includes(id);
                    $(box).prop('checked', checked);
                });
            }

            let clickRegisterRegisteredFor = [];
            function registerClickListeners() {
                $('.station-checkbox').each((index, element) => {
                    let id = parseInt(element.id.substr('station-'.length));
                    if(clickRegisterRegisteredFor.indexOf(id) === -1) {
                        // Not registered yet
                        clickRegisterRegisteredFor.push(id);

                        $(element).click(function(e) {
                            if(!selectedStations_all) {
                                let id = parseInt(this.id.substr('station-'.length));
                                if(this.checked) {
                                    if(selectedStations.indexOf(id) === -1) {
                                        selectedStations.push(id);
                                    }
                                }else{
                                    let index = selectedStations.indexOf(id);
                                    if(index >= 0) selectedStations.splice(index, 1);
                                }
                            }
                        });
                    }
                });
            }
            registerClickListeners();
        });
    </script>
</head>
<body>
  <div class="topnav">
    <div class="logo-image">
          <img src= "img\DEOL-Partners-Logotype.png" class="img-fluid">
    </div>
    <a href="index.php">Homepage</a>
    <a href="averages.php">Averages</a>
    <a href="logout.php" style="float: right; padding-right: 60px">Logout</a>
  </div>
<div class="container">
    <form id="export-form">
        <div class="row">
            <div class="col-sm-3">
                <h3 class="toph3">Data points</h3>

                <div class="form-check">
                    <input class="form-check-input" id="data-temperature" type="checkbox" checked>
                    <label class="form-check-label" for="data-temperature">Temperature</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-dew_point" type="checkbox" checked>
                    <label class="form-check-label" for="data-dew_point">Dew point</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-station_air_pressure" type="checkbox" checked>
                    <label class="form-check-label" for="data-station_air_pressure">Station level air pressure</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-sea_air_pressure" type="checkbox" checked>
                    <label class="form-check-label" for="data-sea_air_pressure">Sea level air pressure</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-visibility" type="checkbox" checked>
                    <label class="form-check-label" for="data-visibility">Visibility</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-wind_speed" type="checkbox" checked>
                    <label class="form-check-label" for="data-wind_speed">Wind speed</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-precipitation" type="checkbox" checked>
                    <label class="form-check-label" for="data-precipitation">Precipitation</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-snow_height" type="checkbox" checked>
                    <label class="form-check-label" for="data-snow_height">Snow height</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-overcast" type="checkbox" checked>
                    <label class="form-check-label" for="data-overcast">Overcast</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-wind_direction" type="checkbox" checked>
                    <label class="form-check-label" for="data-wind_direction">Wind direction</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-has_frozen" type="checkbox" checked>
                    <label class="form-check-label" for="data-has_frozen">Has frozen</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-has_rained" type="checkbox" checked>
                    <label class="form-check-label" for="data-has_rained">Has rained</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-has_snowed" type="checkbox" checked>
                    <label class="form-check-label" for="data-has_snowed">Has snowed</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-has_hailed" type="checkbox" checked>
                    <label class="form-check-label" for="data-has_hailed">Has hailed</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-has_thundered" type="checkbox" checked>
                    <label class="form-check-label" for="data-has_thundered">Has thundered</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" id="data-has_whirlwinded" type="checkbox" checked>
                    <label class="form-check-label" for="data-has_whirlwinded">Has whirlwinded</label>
                </div>
            </div>

            <div class="col-sm">
                <h3 class="toph3">Station selection</h3>

                <div class="form-check">
                    <input class="form-check-input" id="select-all" type="checkbox">
                    <label class="form-check-label" for="select-all">Select all stations</label>
                </div>

                <table id="stations-table" width="100%" style="color: #B0B0B0;">
                    <thead>
                    <tr>
                        <th>Include</th>
                        <th>ID</th>
                        <th>Country</th>
                        <th>Name</th>
                        <th>Coordinates</th>
                        <th>Elevation</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $result = $database_connection->query("SELECT * FROM stations WHERE (country='NORWAY' OR country='SWEDEN' OR country='DENMARK' OR country='ICELAND' OR country='FINLAND' OR country='FAROE ISLANDS')");
                    foreach($result as $row) {
                        echo '<tr>';

                        echo '<th><input class="station-checkbox" id="station-' . $row['stn'] . '" type="checkbox"></th>';
                        echo '<th>' . $row['stn'] . '</th>';
                        echo '<th>' . $row['country'] . '</th>';
                        echo '<th>' . $row['name'] . '</th>';
                        echo '<th>' . number_format($row['latitude'], 2) . ', ' . number_format($row['longitude'], 2) . '</th>';
                        echo '<th>' . $row['elevation'] . '</th>';

                        echo '</tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-sm-3">
                <h3>Export type</h3>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export-type" id="export-type-xml" value="xml">
                    <label class="form-check-label" for="export-type-xml">XML</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export-type" id="export-type-json" value="json">
                    <label class="form-check-label" for="export-type-json">JSON</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export-type" id="export-type-csv" value="csv" checked>
                    <label class="form-check-label" for="export-type-csv">CSV</label>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label for="start-datetime">Start datetime</label>
                    <input id="start-datetime" name="start-datetime" class="form-control" type="datetime-local" value="<?= date('Y-m-d\TG:i', strtotime("-1 month")) ?>">
                </div>

                <div class="form-group">
                    <label for="end-datetime">End datetime</label>
                    <input id="end-datetime" name="end-datetime" class="form-control" type="datetime-local" value="<?= date('Y-m-d\TG:i') ?>">
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label for="max-rows">Maximum rows (0 for no maximum)</label>
                    <input id="max-rows" name="max-rows" class="form-control" type="number" value="1000">
                </div>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-sm-3">
                <button class="btn btn-primary" type="submit">Download export</button>
            </div>

            <div class="col-sm">
                <h3>Export status</h3>
                <span id="export-status"><i class="fas fa-pause-circle"></i> Not exporting yet.</span>
            </div>
        </div>
    </form>
</div>
</body>
</html>
