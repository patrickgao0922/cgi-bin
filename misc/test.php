<?php

// // https://stackoverflow.com/questions/29084442/how-to-use-mysqli-connection-with-ssl
ini_set ('error_reporting', E_ALL);
ini_set ('display_errors', '1');
error_reporting (E_ALL|E_STRICT);

include_once "../DbConfig.php";

// 
$cert_path = "/var/www/html/iGloo/misc/cert";
//$client_key_path = $cert_path. "/client-key-pkcs1-yassl-compatible.pem";
//$client_cert_path = $cert_path. "/client-cert.pem"; 
$ca_cert_path = $cert_path. "/rds-combined-ca-bundle.pem";

function main() {
	echo "<br/>start</br>";

	global $dbhost;
  global $dbuser;
  global $dbpass;
  global $dbname;

	global $client_key_path;
	global $client_cert_path;
	global $ca_cert_path;

	$db = mysqli_init();
	mysqli_options($db, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

	$db->ssl_set(NULL, NULl, $ca_cert_path, NULL, NULL);
	$link = mysqli_real_connect ($db, $dbhost, $dbuser, $dbpass, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL);

	if (!$link)
	{
    die ('Connect error (' . mysqli_connect_errno() . '): ' . mysqli_connect_error() . "\n");
	} else {
    $res = $db->query('SHOW TABLES;');
    print_r ($res);
    $db->close();
	}


}

main();
