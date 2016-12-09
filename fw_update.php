<?php

include_once("./config.php");

global $home_dir;

$fileName = "";
$type = $_GET["type"];
  
/*
print("type:" . $type ."<br/>");
print("id:" .$_GET["id"] ."<br/>");
*/


switch ($type)
{
  // We don't have this file any more.
	case "gateway_json":
		$fileName="gateway_json/WifiGatewayV2.cpp.bin";
		break;			
	default:
	  $fileName="gateway_old_app/WifiGatewayV2.cpp.bin";
	  //print($type);
	  break;
}


if ($fileName!="")
{
	$filespec = $home_dir. "/firmware/" . $fileName;

  // If file there, download
	if(file_exists($filespec))
	{
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($fileSpec).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($fileSpec));

		readfile($filespec);
		exit;
	}
	else
	{
		//print($filespec);
		exit;
	}
}
else
{
	exit;
}

