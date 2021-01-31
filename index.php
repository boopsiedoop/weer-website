<?php
include 'connect_database.php';
session_start();

if ($_SESSION["loggedin"] != true) {
header("location: login.php");
die();
}
?>

<!DOCTYPE html>
<html>

<head>
  <script>
  function getWidth() {
    return Math.max(
      document.body.scrollWidth,
      document.documentElement.scrollWidth,
      document.body.offsetWidth,
      document.documentElement.offsetWidth,
      document.documentElement.clientWidth
    );
  }

  function getHeight() {
    return Math.max(
      document.body.scrollHeight,
      document.documentElement.scrollHeight,
      document.body.offsetHeight,
      document.documentElement.offsetHeight,
      document.documentElement.clientHeight
    );
  }
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/0.5.16/p5.min.js" type="text/javascript"></script>
  <script src="https://cdn.jsdelivr.net/npm/mappa-mundi@0.0.4" type="text/javascript"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<div class="topnav">
  <div class="logo-image">
        <img src= "img\DEOL-Partners-Logotype.png" class="img-fluid">
  </div>
  <a href="averages.php">Averages</a>
  <a href="export.php">Export</a>
  <a href="logout.php">Logout</a>
</div>

<body>

    <div class="map">
    <script>
      const JAVASCRIPT_DATA = [
      <?php
      $result = database("SELECT * FROM stations WHERE country='NORWAY' OR country='SWEDEN' OR country='DENMARK' OR country='ICELAND' OR country='FINLAND' OR country='FAROE ISLANDS'");
      foreach($result as $row) {
          echo "{ id: ".$row['stn'].", name: '".$row['name']."', lat: ".$row['latitude'].", lon: ".$row['longitude'].", elevation: ".$row['elevation']."},";
      }
      ?>
      ];
      var SELECT_DATA = [
        {}
      ];
    </script>
    <script>

      let myMap;
      let canvas;
      const mappa = new Mappa('Leaflet');


      const options = {
        lat: 64.906423,
        lng: -18.506711,
        zoom: 6,
        style: "https://cartodb-basemaps-{s}.global.ssl.fastly.net/dark_all/{z}/{x}/{y}.png"
      }


      function setup(){
        canvas = createCanvas(getWidth()*0.745,getHeight()*0.80).parent('myContainer');
        myMap = mappa.tileMap(options);

        myMap.overlay(canvas);


      }



      function draw(){
        if (recent == 0){
          if (dataDate != Math.floor(new Date(document.getElementById( 'datePicker' ).value)/ 1000 ) + ((document.getElementById( 'myRange' ).value)*3600)){
            dataDate  = Math.floor(new Date(document.getElementById( 'datePicker' ).value)/ 1000 ) + ((document.getElementById( 'myRange' ).value)*3600)
            i_dataDate = dataDate
            console.log(dataDate)

          }
        }
        else{
          if(i_dataDate == Math.floor(new Date(document.getElementById( 'datePicker' ).value)/ 1000 ) + ((document.getElementById( 'myRange' ).value)*3600)){
            dataDate = Math.floor(new Date()/ 1000)
          }
          else{
            recent = 0
          }
        }

        clear();

        for (var i = 0; i < JAVASCRIPT_DATA.length; i++) {
          fill( 155, 155, 155);
          for (var x = 0; x < SELECT_DATA.length; x++) {
            if (JAVASCRIPT_DATA[i].id == SELECT_DATA[x].id) {
                fill( 200, 100, 255);
            }

          var posistion = myMap.latLngToPixel(JAVASCRIPT_DATA[i].lat, JAVASCRIPT_DATA[i].lon);

          ellipse(posistion.x, posistion.y, 10, 10);
          }
        }
        var hour = document.getElementById("myRange").value
        if(hour == 24){
          hour = "00"
        }
        document.getElementById("hour").innerHTML=("Hour ("+hour+":00)")
      }
      function mousePressed() {
        if(mouseX < 0 || mouseY <0 || mouseX > width || mouseY > height){return}

        for(var i = 0; i < JAVASCRIPT_DATA.length; i++) {
          var entry = JAVASCRIPT_DATA[i];
          var screenPos = myMap.latLngToPixel(entry.lat, entry.lon);
          if(dist(mouseX, mouseY, screenPos.x, screenPos.y) < 20) {
            if (SELECT_DATA[0].id != entry.id){
              SELECT_DATA = [
                {id: entry.id}
              ];
              document.getElementById("id").innerHTML=(entry.id)
              document.getElementById("city").innerHTML=(entry.name)
              document.getElementById("height").innerHTML=(entry.elevation+"m")
              $.get({
                url: "get_station_data.php?id="+entry.id,//"&date="+....+"&time="+.....+",
                success:(data)=>{
                  data = JSON.parse(data)

                  document.getElementById("temp").innerHTML=(data[0].temperature+"°C")
                  document.getElementById("wind").innerHTML=(data[0].wind_speed+"km/h")
                  document.getElementById("visibility").innerHTML=(data[0].visibility+"km")
                  document.getElementById("snow").innerHTML=(data[0].snow_height+"cm")
                  document.getElementById("overcast").innerHTML=(data[0].overcast+"%")
                  document.getElementById('humidity').innerHTML=(((data[0].dew_point*5)-(data[0].temperature*5))+100+"%")
                  document.getElementById("dew_point").innerHTML=(data[0].dew_point+"°C")

                  var date = new Date(data[0].date * 1000)
                  var hour = date.getHours()
                  if (hour < 10){
                    hour = "0"+hour
                  }
                  var minutes = date.getMinutes()
                  if (minutes < 10){
                    minutes = "0"+minutes
                  }
                  document.getElementById("time").innerHTML=(hour+":"+minutes)
                  var month = date.getMonth() + 1
                  if (month < 10){
                    month = "0"+month
                  }
                  var day = date.getDate()
                  if (day < 10){
                    day = "0"+day
                  }
                  document.getElementById("date").innerHTML=(day+"-"+ month+"-"+date.getFullYear())

                  document.getElementById('arrow').style = 'transform: rotateZ(-'+data[0].wind_direction+'deg);'

                  if (data[0].has_whirlwinded == 1) {
                    document.getElementById('wheather').src='img/tornado.png'
                    }
                  else if (data[0].has_hailed == 1) {
                    document.getElementById('wheather').src='img/hail.png'
                  }
                  else if (data[0].has_snowed == 1) {
                    document.getElementById('wheather').src='img/snow.png'
                  }
                  else if (data[0].has_tundered == 1) {
                    document.getElementById('wheather').src='img/thunder.png'
                  }
                  else if (data[0].has_rained == 1) {
                    document.getElementById('wheather').src='img/rain.png'
                  }
                  else if (data[0].overcast > 50) {
                    document.getElementById('wheather').src='img/clouds.png'
                  }
                  else {
                    document.getElementById('wheather').src='img/sun.png'
                  }

                }})
            }
            myMap.map.flyTo([JAVASCRIPT_DATA[i].lat, JAVASCRIPT_DATA[i].lon]);
            break;
          }
        }
      }

      function renew(x){
        for(var i = 0; i < JAVASCRIPT_DATA.length; i++) {
          var entry = JAVASCRIPT_DATA[i];
          if (entry.id == x){
            SELECT_DATA = [
              {id: x}
            ];
            document.getElementById("id").innerHTML=(entry.id)
            document.getElementById("city").innerHTML=(entry.name)
            document.getElementById("height").innerHTML=(entry.elevation+"m")
            $.get({
              url: "get_station_data.php?id="+entry.id,//"&date="+....+"&time="+.....+",
              success:(data)=>{
                data = JSON.parse(data)

                document.getElementById("temp").innerHTML=(data[0].temperature+"°C")
                document.getElementById("wind").innerHTML=(data[0].wind_speed+"km/h")
                document.getElementById("visibility").innerHTML=(data[0].visibility+"km")
                document.getElementById("snow").innerHTML=(data[0].snow_height+"cm")
                document.getElementById("overcast").innerHTML=(data[0].overcast+"%")
                document.getElementById('humidity').innerHTML=(((data[0].dew_point*5)-(data[0].temperature*5))+100+"%")
                document.getElementById("dew_point").innerHTML=(data[0].dew_point+"°C")

                var date = new Date(data[0].date * 1000)
                var hour = date.getHours()
                if (hour < 10){
                  hour = "0"+hour
                }
                var minutes = date.getMinutes()
                if (minutes < 10){
                  minutes = "0"+minutes
                }
                document.getElementById("time").innerHTML=(hour+":"+minutes)
                var month = date.getMonth() + 1
                if (month < 10){
                  month = "0"+month
                }
                var day = date.getDate()
                if (day < 10){
                  day = "0"+day
                }
                document.getElementById("date").innerHTML=(day+"-"+ month+"-"+date.getFullYear())

                document.getElementById('arrow').style = 'transform: rotateZ(-'+data[0].wind_direction+'deg);'

                if (data[0].has_whirlwinded == 1) {
                  document.getElementById('wheather').src='img/tornado.png'
                  }
                else if (data[0].has_hailed == 1) {
                  document.getElementById('wheather').src='img/hail.png'
                }
                else if (data[0].has_snowed == 1) {
                  document.getElementById('wheather').src='img/snow.png'
                }
                else if (data[0].has_tundered == 1) {
                  document.getElementById('wheather').src='img/thunder.png'
                }
                else if (data[0].has_rained == 1) {
                  document.getElementById('wheather').src='img/rain.png'
                }
                else if (data[0].overcast > 50) {
                  document.getElementById('wheather').src='img/clouds.png'
                }
                else {
                  document.getElementById('wheather').src='img/sun.png'
                }

              }})
              myMap.map.flyTo([JAVASCRIPT_DATA[i].lat, JAVASCRIPT_DATA[i].lon]);
              break;
          }
        }
      }

      function Snow(){
        renew(21150)
      }

      function min_temp(){
        renew(22300)
      }

      function max_temp(){
        renew(26380)
      }

      function start_data(){
        date = 1
      }

    </script>

    <div class="databox">

      <div class= "data1">
        <p class = "headtext">Time</p>
        <p class = "bodytext" id="time"></p>
      </div>
      <div class= "data1">
        <p class = "headtext">Date</p>
        <p class = "bodytext" id="date"></p>
      </div>
      <div class= "data1">
        <p class = "headtext">Snow fall</p>
        <input type="button" onclick="Snow()" class="button" value="Most snow fall">
      </div>
      <div class= "data1">
        <p class = "headtext">Highest temperature</p>
        <input type="button" onclick="max_temp()" class="button" value="Highest temperature">
      </div>
      <div class= "data1">
        <p class = "headtext">Lowest temperature</p>
        <input type="button" onclick="min_temp()" class="button" value="Lowest temperature">
      </div>
      <div class= "data1">
        <p class = "headtext">Most recent data</p>
        <input type="button" onclick="start_data()" class="button" value="Recent data">
      </div>
      <div class= "data1">
        <p class = "headtext">-----</p>
        <p class = "bodytext">-----</p>
      </div>



      <div class= "box1">
        <div class= "sliderbox">
          <p class = "headtext">Station Name</p>
          <p class = "bodytext" id="city">kjQhv</p>
        </div>
        <div class= "data3">
          <p class = "headtext">Station ID</p>
          <p class = "bodytext" id="id">19283</p>
        </div>
        <div class= "data4">
          <p class = "headtext">Altitude</p>
          <p class = "bodytext" id="height">200m</p>
        </div>
        <div class= "data3">
          <p class = "headtext">Humidity</p>
          <p class = "bodytext" id="humidity">43.5%</p>
        </div>
        <div class= "data4">
          <p class = "headtext">Temperature</p>
          <p class = "bodytext" id="temp">20.5°C</p>
        </div>
        <div class= "data3">
          <p class = "headtext">Dew point</p>
          <p class = "bodytext" id="dew_point">-4°C</p>
        </div>
        <div class= "data4">
          <p class = "headtext">Overcast</p>
          <p class = "bodytext" id="overcast">200m</p>
        </div>
        <div class= "data3">
          <p class = "headtext">-----</p>
          <p class = "bodytext">=====</p>
        </div>
        <div class= "data4">
          <p class = "headtext">-----</p>
          <p class = "bodytext">-----</p>
        </div>
        <div class= "data3">
          <p class = "headtext">Snow height</p>
          <p class = "bodytext" id="snow">20.5°C</p>
        </div>
        <div class= "data4">
          <p class = "headtext">Visibility</p>
          <p class = "bodytext" id="visibility">kjQhv</p>
        </div>
        <div class= "dataimages">
          <div class="dataImg">
            <p class = "headtext">Wind direction</p>
            <img id = "arrow" src="img/arrow.png", height="100px">
            <p class = "bodytext" id="wind">19283</p>
          </div>
          <div class="dataImg">
            <p class = "headtext">Wheather</p>
            <img id="wheather" src="img/tornado.png", height="125px">
          </div>
        </div>
        <div class= "sliderbox">
          <p class = "headtext" id="hour">Hour</p>
          <input type="range" min="1" max="24" value="12" class="slider" id="myRange">
          <div class="datePicker">
            <input type="date" id="datePicker" name="datePicker">
          </div>
        </div>
      </div>

    </div>
        <div id="myContainer"></div>

<script>
var date = new Date()
var hour = date.getHours()
if (hour < 10){
  hour = "0"+hour
}
var minutes = date.getMinutes()
if (minutes < 10){
  minutes = "0"+minutes
}
document.getElementById("time").innerHTML=(hour+":"+minutes)
var month = date.getMonth() + 1
if (month < 10){
  month = "0"+month
}
var day = date.getDate()
if (day < 10){
  day = "0"+day
}

document.getElementById("date").innerHTML=(day+"-"+ month+"-"+date.getFullYear())

document.getElementById("myRange").value = date.getHours();
document.getElementById("datePicker").value = (date.getFullYear()+"-"+ month+"-"+day);
document.getElementById("datePicker").max = (date.getFullYear()+"-"+ month+"-"+day);

var dataDate = 0
var recent = 1
var i_dataDate = Math.floor(new Date(document.getElementById( 'datePicker' ).value)/ 1000 ) + ((document.getElementById( 'myRange' ).value)*3600)

</script>
</body>

</html>
