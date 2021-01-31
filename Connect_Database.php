<?php
function database($query){ //function parameters, two variables.

		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "stations";

		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
		  die("Connection failed: " . $conn->connect_error);
		}

		$sql = $query;
		$result = $conn->query($sql);
		$conn->close();
		return $result;
	}

?>
