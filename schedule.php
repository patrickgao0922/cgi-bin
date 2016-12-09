<?php

// --------------------- NOTE ---------------------------
// We don't use this code, so I am not upgrading.

// Connect
$dbhost = "localhost";
$dbuser = "igloosof_hmctrl";
$dbpass = "C!H7=xLZcgOu";
$dbname = "igloosof_homecontrol";
$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname );


function queueJSONCommand($remoteName,$cmdJSON)
{
global $dbhost;
global $dbuser;
global $dbpass;
global $dbname;

	$conInsert= mysqli_connect($dbhost,$dbuser,$dbpass,$dbname );
	$query = "INSERT INTO Command(CommandDate,RemoteName,CommandJSON,CommandComplete) VALUES (utc_timestamp(),?,?,0)";

	$stmt = mysqli_prepare($conInsert,$query);
		
	mysqli_stmt_bind_param($stmt,"ss",$remoteName,$cmdJSON);
	mysqli_stmt_execute($stmt);

	mysqli_close($conInsert);	
}

function deleteJSONCommand($remoteName,$cmdId)
{
global $con;

	if (mysqli_connect_errno($con))
	{
        	error_log("Failed to connect to MySQL");
	}
	else
	{
		$query = "UPDATE Command SET CommandComplete = 1 WHERE RemoteName=? and CommandId=? and CommandComplete=0  limit 1";

		$stmt = mysqli_prepare($con,$query);

		mysqli_stmt_bind_param($stmt,"ss",$remoteName,$cmdId);
		mysqli_stmt_execute($stmt);
	}

}

// Main code starts here  ;-)

	if (mysqli_connect_errno($con))
	{
        	error_log("Failed to connect to MySQL");
	}
	else
	{
		date_default_timezone_set('Australia/Melbourne');
		$timeStr= date('H:i');
		//$timeStr='19:42';// testing
		$daycode = date('N');// get ISO day of the week
		
		if (intval($daycode)>5)
		{

			$query="SELECT ScheduleID,ScheduleRemoteName,ScheduleCommandJSON FROM Schedule WHERE  (ScheduleDaycode = 0 OR ScheduleDaycode = 9 OR ScheduleDaycode = ? ) AND ScheduleTime = ?";
		
			$stmt = mysqli_prepare($con,$query);

			mysqli_stmt_bind_param($stmt,"ss",$daycode ,$timeStr);
			//mysqli_stmt_bind_param($stmt,"s",$timeStr);
			mysqli_stmt_execute($stmt);
			echo("Weekday Daycode: ".$daycode ." Time:".$timeStr."</br></br>");
		}
		else
		{

			$query="SELECT ScheduleID,ScheduleRemoteName,ScheduleCommandJSON FROM Schedule WHERE  (ScheduleDaycode = 0 OR ScheduleDaycode = 8 OR ScheduleDaycode = ? ) AND ScheduleTime = ?";
	
			$stmt = mysqli_prepare($con,$query);

			mysqli_stmt_bind_param($stmt,"ss",$daycode ,$timeStr);
			mysqli_stmt_execute($stmt);	
			echo("Weekday Daycode: ".$daycode ." Time:".$timeStr."</br></br>");
		}



		
		
		mysqli_stmt_bind_result($stmt,$scheduleId,$remoteName,$cmdJSON);
		
		while (mysqli_stmt_fetch($stmt)) 
		{
			echo("Adding  " . $scheduleId." Remote ".$remoteName." Command:".$cmdJSON."</br>");
			queueJSONCommand($remoteName,$cmdJSON);
		}
		mysqli_close($con);	
	}

	
?>
