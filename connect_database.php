<?php
include_once 'database_config.php';

// After the php script has finished executing, PHP will take care of closing the database connection for us!
$database_connection = new mysqli(DATABASE['host'], DATABASE['username'], DATABASE['password'], DATABASE['database']);
if($database_connection->connect_error) {
	die("Connection failed: " . $database_connection->connect_error);
}
