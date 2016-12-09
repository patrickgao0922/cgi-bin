<?php

global $home_dir;
global $db_dir;
global $home_url;
global $cert_path;
global $ca_cert_path;

$home_dir = "/home4/igloosof";

$db_dir = "/home4/igloosof/public_html/iGloo/database";
//$db_dir = "/var/www/html/iGloo/database";

$home_url = "igloohomecontrol.com";
//$home_url = "toothfi.com";

$cert_path = dirname(__FILE__). "/misc/cert";
$ca_cert_path = $cert_path. "/rds-combined-ca-bundle.pem";
