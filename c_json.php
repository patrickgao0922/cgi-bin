<?php

require_once "DbConfig.php";
require_once "config.php";


function queueJSONCommand($remoteName,$cmdJSON)
{
	global $cert_path;
	global $ca_cert_path;

  global $dbhost;
  global $dbuser;
  global $dbpass;
  global $dbname;

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
		// http://www.w3schools.com/php/php_mysql_prepared_statements.asp
    $sql = "INSERT INTO Command (CommandDate, RemoteName, CommandJSON, CommandComplete) VALUES (utc_timestamp(),?,?,0)";
    $stmt = $mysqli_obj->prepare($sql);

		$stmt->bind_param("ss", $remoteName, $cmdJSON);
    $stmt->execute();

		$stmt->close();
    $mysqli_obj->close();
  }
}


// pull out schedule, then set cmd
function processSchedules()
{
  // global var
  global $cert_path;
	global $ca_cert_path;

  global $dbhost;
  global $dbuser;
  global $dbpass;
  global $dbname;

  // sql
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
    // yes, utc
    date_default_timezone_set('UTC');


    // current hour and min
    $timeStr = date('H:i');      

    // day code
    $daycode = date('N'); // get ISO day of the week
 
    // 1 for Monday, 7 for Sunday
    // so aiming for 6, 7, which is Sat and Sun.
    if(intval($daycode)>5)
    {
      // get id, device_name, json  based on day_code e.g. 4 and hour,min
      $sql = "SELECT ScheduleID, ScheduleRemoteName, ScheduleCommandJSON FROM Schedule WHERE (ScheduleDaycode = 0 OR ScheduleDaycode = 9 OR ScheduleDaycode = ? ) AND ScheduleTime = ?";
      $stmt = $mysqli_obj->prepare($sql);
		  $stmt->bind_param("ss", $daycode, $timeStr);
      $stmt->execute();

      //echo("Weekday Daycode: ".$daycode ." Time:".$timeStr."</br></br>");
    }
    else {
      // This is for Monday to Friday.
      $sql = "SELECT ScheduleID, ScheduleRemoteName, ScheduleCommandJSON FROM Schedule WHERE  (ScheduleDaycode = 0 OR ScheduleDaycode = 8 OR ScheduleDaycode = ? ) AND ScheduleTime = ?";
			$stmt = $mysqli_obj->prepare($sql);
			$stmt->bind_param("ss", $daycode, $timeStr);
      $stmt->execute();

			//echo("Weekday Daycode: ".$daycode ." Time:".$timeStr."</br></br>");
    }
    
    // https://secure.php.net/manual/en/mysqli-stmt.bind-result.php
    $stmt->bind_result($scheduleId, $remoteName, $cmdJSON);
		
		while($stmt->fetch()) 
		{
			//echo("Adding  " . $scheduleId." Remote ".$remoteName." Command:".$cmdJSON."</br>");

			queueJSONCommand($remoteName, $cmdJSON);
		}

    // update cmd to be completed, if not completed and curr_time > cmd_time + 600
    // Basically, this sql will clean up ANY cmd in 10 mins, if this scrip is running.
    // BUG......
    $sql = "UPDATE Command SET CommandDate = UTC_TIMESTAMP(), CommandComplete = 2 WHERE CommandComplete = 0 and ( UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(CommandDate) ) > 600";
    //$sql = "UPDATE Command SET CommandDate = UTC_TIMESTAMP(), CommandComplete = 2 WHERE CommandComplete = 0 and ( UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(CommandDate) ) > 200";
    $stmt = $mysqli_obj->prepare($sql);
    $stmt->execute();

		$stmt->close();
    $mysqli_obj->close();
  }
}


function checkSchedules() {

  // global var
  global $cert_path;
	global $ca_cert_path;

  global $dbhost;
  global $dbuser;
  global $dbpass;
  global $dbname;

  // sql conn
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
    // 60 means 60 seconds in unix time
    // Basically, if you keep refreshing this page within 60s, nothing will happen.
		// Basically, every 60s, we run processSchedules().
    // Otherwise, processSchedules() will be called.
    // LastSchedule == SettingId == 1
    $sql = "UPDATE Setting SET SettingValue = NOW() WHERE SettingId=1 and UNIX_TIMESTAMP(NOW()) > (UNIX_TIMESTAMP(SettingValue)+60)";
    $stmt = $mysqli_obj->prepare($sql);
    $stmt->execute();

    $isScheduleNeeded = $mysqli_obj->affected_rows;
    $stmt->close();
    $mysqli_obj->close();

    if ($isScheduleNeeded == 1)
		{
			//echo "Run the schedule";
			processSchedules();
		}
		else
		{
			//echo "Already run";
		}  
  }
}


function getCommand($remoteName)
{
  /*
	global $dbhost,$dbuser; 
	global $dbpass; 
	global $dbname; 
  */

  // global var
  global $cert_path;
	global $ca_cert_path;

  global $dbhost;
  global $dbuser;
  global $dbpass;
  global $dbname;

	$multi_chan_device_type = array(
    "phoenix_multi_remote",
		"motolux_multi_remote",
		"dynaveil_multi_remote"
  );

  $single_chan_device_type = array(
    "h_and_g_rc300",
		"generic_blind_1_channel"
  );

  // connect to db
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
    // decode remote name
		$remoteName = urldecode($remoteName);

    // pull last 5 cmds of SAME KIND
    $sql = "SELECT CommandId, CommandJSON FROM Command WHERE CommandComplete=0 and RemoteName=? order by CommandDate asc LIMIT 5";
    $stmt = $mysqli_obj->prepare($sql);
    $stmt->bind_param("s", $remoteName);
    $stmt->execute();
    $stmt->bind_result($cmdId, $cmdJSON);

    // new empty array
    //"{}";
    $cmd_array = array(); 		

    // 5 cmds of same kind in a while loop
		while($stmt->fetch()) 
		{
      // become obj
      $obj = json_decode($cmdJSON);

      if(in_array($obj->type, $single_chan_device_type)) {
        single_channel_device_build_cmd_array($obj, $cmdId, $remoteName, $cmd_array);
      }
			elseif(in_array($obj->type, $multi_chan_device_type)) {
				multi_channel_device_build_cmd_array($obj, $cmdId, $remoteName, $cmd_array);
			}
      else {
        generic_output_build_cmd_array($obj, $cmdId, $remoteName, $cmd_array);
      }	
		}

    // close 2
    $stmt->close();
    $mysqli_obj->close();

    // echo json
    echo json_encode($cmd_array);
  }
}


function single_channel_device_build_cmd_array($obj, $cmd_id, $device_name, &$cmd_array) {
  // cmd_id
  $obj->cmdId = $cmd_id;

  // keep the actual command json
  $cmd = $obj->command;
  unset($obj->command);
  $obj->cmd = $cmd;

  // no channel

  // description becomes action
  $action = $obj->cmd->description;
  unset($obj->cmd->description);
  $obj->cmd->act = $action;

  // $obj->cmdList has been reset, no more.
  unset($obj->cmd->cmdList);

  // real name BEC-xxxxxxxx
  $obj->real_name = $obj->name;

  // cloud name, BC1xxxxxxx
  $obj->name = $device_name;

  array_push($cmd_array, $obj);
}


function multi_channel_device_build_cmd_array($obj, $cmd_id, $device_name, &$cmd_array) {
  // cmd_id
  $obj->cmdId = $cmd_id;

  // keep the actual command json
  $cmd = $obj->command;
  // Old way: Only one command one time
  // unset($obj->command);
  // $obj->cmd = $cmd;

  // // keep channel
  // $chan = $obj->cmd->channel;
  // unset($obj->cmd->channel);
  // $obj->cmd->chan = $chan;

  // // description becomes action
  // $action = $obj->cmd->description;
  // unset($obj->cmd->description);
  // $obj->cmd->act = $action;

  // If command is not a arrya: on single command
  $obj->cmd = new StdClass;
  unset($obj->command);
  if (!is_array($cmd)) {
    
    // $obj->cmd = $cmd;

    // keep channel
    $chan = $obj->cmd->channel;
    unset($obj->cmd->channel);
    $obj->cmd->chan = $chan;

    // description becomes action
    $action = $obj->cmd->description;
    unset($obj->cmd->description);
    $obj->cmd->act = $action;

  // $obj->cmdList has been removed when inserted.
  } else { // Group Channeling
    // $obj->cmd = $cmd;
    $mul_chan_commands_array = array();
    foreach ($cmd as &$single_cmd) {
      $single_mul_chan_command = new StdClass;
      $single_mul_chan_command->chan = $single_cmd->channel;

      $single_mul_chan_command->act = $single_cmd->description;

      array_push($mul_chan_commands_array, $single_mul_chan_command);
      
    }
    $obj->cmd->subcmd = $mul_chan_commands_array;
  }

	unset($obj->cmd->cmdList);

  // real name BEC-xxxxxxxx
  $obj->real_name = $obj->name;

  // cloud name, BC1xxxxxxx
  $obj->name = $device_name;

  // cloud name, BC1xxxxxxx
  //$obj->name = $device_name;

  array_push($cmd_array, $obj);

}


function generic_output_build_cmd_array($obj, $cmd_id, $device_name, &$cmd_array) {
  // cmd_id
  $obj->cmdId = $cmd_id;

  // keep the actual command json
  $cmd = $obj->command;

    
    unset($obj->command);
    $obj->cmd = $cmd;

    // keep channel
    $chan = $obj->cmd->channel;
    unset($obj->cmd->channel);
    $obj->cmd->chan = $chan;

    // description becomes action
    $action = $obj->cmd->description;
    unset($obj->cmd->description);
    $obj->cmd->act = $action;

  

  

  // real name BEC-xxxxxxxx
  $obj->real_name = $obj->name;

  // cloud name, BC1xxxxxxx
  $obj->name = $device_name;

  // cloud name, BC1xxxxxxx
  //$obj->name = $device_name;

  array_push($cmd_array, $obj);

}


/*
function deleteCommand($remoteName,$cmdId)
{
	global $dbhost;
	global $dbuser; 
	global $dbpass; 
	global $dbname; 

	$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname );
	
	if (mysqli_connect_errno($con))
	{
        	error_log("Failed to connect to MySQL");
	}
	else
	{
		if ($cmdId!="")
		{
			$ids=preg_split("/,+/", $cmdId);
			foreach ($ids as &$value) 
			{
				$query = "UPDATE Command SET CommandComplete = 1 WHERE RemoteName=? and CommandId=? and CommandComplete=0";
			
				$stmt = mysqli_prepare($con,$query);

				mysqli_stmt_bind_param($stmt,"ss",$remoteName,$value);		
				
				if(mysqli_stmt_execute($stmt)!=true)
				{
				}
				
				
			}			
		}

		//else
		//{
		//	$query = "UPDATE Command SET CommandComplete = 1 WHERE RemoteName=? and CommandComplete=0  limit 1";	
			
		//	$stmt = mysqli_prepare($con,$query);

		//	mysqli_stmt_bind_param($stmt,"s",$remoteName);						
		//}
		

		mysqli_close($con);	
	}
	echo("{}");
}
*/


function deleteCommand($remoteName, $cmdId) {
  global $cert_path;
	global $ca_cert_path;

  global $dbhost;
  global $dbuser;
  global $dbpass;
  global $dbname;

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
    if ($cmdId!="")
    {
      $ids = preg_split("/,+/", $cmdId);
      foreach ($ids as &$value) {
        $sql = "UPDATE Command SET CommandComplete = 1 WHERE RemoteName=? and CommandId=? and CommandComplete=0";
        $stmt = $mysqli_obj->prepare($sql);
        $stmt->bind_param("ss", $remoteName, $value);
        $stmt->execute();
      }
    }
    else {

    }
  }

  echo("{}");
}


// Check if any schedules need to be run before we do anything else!
checkSchedules();

$debug     = '';
$zoneID    = "";
$deviceID  = "";

// device id    
if (isset($_REQUEST['c']))
{
  $codeID = $_REQUEST['c'];
}
else
{
  $codeID = "";
}


// cmd
if (isset($_REQUEST['d']))
{ 
  $deleteFileWhenDone = $_REQUEST['d'];
}
else
{
  $deleteFileWhenDone = "";
}

    
// cmd_id
if (isset($_REQUEST['id']))
{
	$comamndId= $_REQUEST['id'];
}
else
{
  $comamndId= "";
}

	
if (strcmp($codeID, '') !== 0)
{
	if ($deleteFileWhenDone == "y")
	{
  	deleteCommand($codeID,$comamndId);
	}
	else
	{
 		getCommand($codeID);
	}
}
