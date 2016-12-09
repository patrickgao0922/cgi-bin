<?php

  // So it connects the map server. We don't use it.
	$IP = "igloo-main.ciw169h7kx6c.ap-southeast-2.rds.amazonaws.com";
	$port = '3306';
	$username = "igloomain";
	$password = "igloomain";
	$dbname = "ebdb";
	$connect =  mysqli_connect($IP, $username, $password,$dbname,$port);
	if (!$connect) {
		die('Could not connect: ' . $connection->connect_error);
	}
?>
