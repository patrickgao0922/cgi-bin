<?php
  // similar to connect.php
	$IP = "aah7yx3g9cwgat.ciw169h7kx6c.ap-southeast-2.rds.amazonaws.com";//'54.153.147.59';
	$port = '3306';
	$username = "igloomain";//'darcular';
	$password = "igloomain";//'';
	$dbname = "ebdb";//'igloo';
	$connect =  mysqli_connect($IP, $username, $password,$dbname,$port);
	if (!$connect) {
		die('Could not connect: ' . $connection->connect_error);
	}
?>
