<?php

include_once("./DbConfig.php");
include_once("./config.php");


if(isset($_REQUEST["device_name"])) {
	// only set cmds completed related to that device
	$device_name = $_REQUEST["device_name"];
	set_device_cmd_completed($device_name);
}
else {
	// set all cmds completed
 	$device_name = "";
	set_cmd_completed();
}


// func
function set_device_cmd_completed($device_name) {
  global $cert_path;
  global $ca_cert_path;

	global $dbhost;              
  global $dbuser;              
  global $dbpass;              
  global $dbname;              

  // mysqli_obj
	$mysqli_obj = mysqli_init();
  mysqli_options($mysqli_obj, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);	
	$mysqli_obj->ssl_set(NULL, NULl, $ca_cert_path, NULL, NULL);
  $link = mysqli_real_connect($mysqli_obj, $dbhost, $dbuser, $dbpass, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL);

	if(!$link)
	{
		die('Connect error ('. mysqli_connect_errno(). '): '. mysqli_connect_error(). "\n");
	}
	else
  {
    $sql = "UPDATE Command SET CommandComplete = 1 WHERE RemoteName = ? and CommandComplete = 0";
    $stmt = $mysqli_obj->prepare($sql);
    $stmt->bind_param("s", $device_name);
    $stmt->execute();

    $stmt->close();
    $mysqli_obj->close();
    var_dump("set_device_cmd_completed good");
  }  
}


function set_cmd_completed() {
  global $cert_path;
  global $ca_cert_path;

	global $dbhost;              
  global $dbuser;              
  global $dbpass;              
  global $dbname;              

  // mysqli_obj
	$mysqli_obj = mysqli_init();
  mysqli_options($mysqli_obj, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);	
	$mysqli_obj->ssl_set(NULL, NULl, $ca_cert_path, NULL, NULL);
  $link = mysqli_real_connect($mysqli_obj, $dbhost, $dbuser, $dbpass, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL);

	if(!$link)
	{
		die('Connect error ('. mysqli_connect_errno(). '): '. mysqli_connect_error(). "\n");
	}
	else
  {
    $sql = "UPDATE Command SET CommandComplete = 1 WHERE CommandComplete = 0";
    $stmt = $mysqli_obj->prepare($sql);
    $stmt->execute();

    $stmt->close();
    $mysqli_obj->close();
    var_dump("set_cmd_completed good");
  }
}



