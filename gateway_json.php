<?php

include_once("./DbConfig.php");  
include_once("./config.php");

// igloohomecontrol.com/iGloo/cgi-bin/c.php?c=HG10301114N&d=y

// Require https
/*if ($_SERVER['HTTPS'] != "on") 
{
    $url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit;
}*/
    
if ($_GET["gw"]=="")
{
	exit(0);
}

//$gatewayId=preg_replace("/[^A-Za-z0-9 ]/", '', $_GET["gw"]);// attempt to prevent injection (TO DO - HARDEN THIS) */
 
// error msg in array
$errmsg = array("error"=>"");


// connect to db
if(mysqli_connect_errno($con)) {
  // error_msg
  $errmsg['error'] = $errmsg['error'] . "Failed to connect to MySQL";

  // echo error_msg json
  echo json_encode($errmsg);

  // out
  exit();
}
else
{
  // prepare the statement
  // gateway_id, what?
  $gatewayId = $_GET["gw"];

  // join remote, command and gateway to get remote_mac, remote_name, remote_type and cmd
  $query = "SELECT Remote.RemoteMac as mac,Remote.RemoteName as name,Remote.RemoteType as type,Command.CommandJSON as cmd FROM Command inner join Remote on Command.RemoteId = Remote.RemoteId join Gateway on Remote.RemoteGatewayId = Gateway.GatewayId where Gateway.GatewayCode=?";

  // ----------------------------- NOTE -----------------------------
  // But we don't have $con
	$stmt = mysqli_stmt_init($con);
		
	// if failed to prepare statement
	if(!mysqli_stmt_prepare($stmt, $query))
	{
    // prepare statement with query and fail
	  $errmsg['error'] = "Failed to prepare statement";
		echo json_encode($errmsg);
	}
	else
	{
    // bind param
		// bind and execute
		mysqli_stmt_bind_param($stmt, "s", $gatewayId);

    // execute statement
		mysqli_execute($stmt);
				
    // bound result
    // statement bind result
    // statement
    // remote_mac
    // remote_name
    // remote_type
    // remote_cmd
		mysqli_stmt_bind_result($stmt, $remoteMac, $remoteName, $remoteType, $remoteCommand);
			
		$commandsArray = array();
				
		/* fetch values */
		while (mysqli_stmt_fetch($stmt)) 
		{		
		  $remoteCommand = [
        "mac" => $remoteMac,
        "name"=>$remoteName,
        "type"=>$remoteType,
        "command"=>json_decode($remoteCommand)];
				array_push($commandsArray, $remoteCommand);				
		}

		$commands = ["remotes" => $commandsArray];
		echo(json_encode($commands));	
  }	
  mysqli_close($con);
}


