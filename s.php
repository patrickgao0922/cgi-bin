<?php

include_once "DbConfig.php";
include_once "./config.php";

global $db_dir;
global $home_url;

function queueJSONCommand($remoteName, $cmdJSON)
{
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
		$cmds = json_decode($cmdJSON);
		$cmds->type = preg_replace('/\s+/', '', $cmds->type);

		if(isset($cmds->type)) {
      if(in_array($cmds->type, $single_chan_device_type)) {
		    $sql = "INSERT INTO `Command`(`CommandDate`, `RemoteName`, `CommandJSON`, `CommandComplete`) VALUES (utc_timestamp(),?,?,0)";
        $stmt = $mysqli_obj->prepare($sql);

        // no mac address
		    unset($cmds->mac);

		    // clean up white space in the type and the name
		    $cmds->name = preg_replace('/\s+/', '', $cmds->name);
		    $cmdJSON = json_encode($cmds);
		
		    $stmt->bind_param("ss", $remoteName, $cmdJSON);
        $stmt->execute();

		    $stmt->close();
        $mysqli_obj->close();
      }
			else if(in_array($cmds->type, $multi_chan_device_type)) {
				$sql = "INSERT INTO `Command`(`CommandDate`, `RemoteName`, `CommandJSON`, `CommandComplete`) VALUES (utc_timestamp(),?,?,0)";
        $stmt = $mysqli_obj->prepare($sql);

        // no mac address
		    unset($cmds->mac);

		    // clean up white space in the type and the name
		    $cmds->name = preg_replace('/\s+/', '', $cmds->name);
		    $cmdJSON = json_encode($cmds);
		
		    $stmt->bind_param("ss", $remoteName, $cmdJSON);
        $stmt->execute();

		    $stmt->close();
        $mysqli_obj->close();
			}
      else {
        // Legacy
        $sql = "INSERT INTO `Command`(`CommandDate`, `RemoteName`, `CommandJSON`, `CommandComplete`) VALUES (utc_timestamp(),?,?,0)";
        $stmt = $mysqli_obj->prepare($sql);

        //unset($cmds->name);
		    unset($cmds->mac);
		    //unset($cmds->command->cmdList);

		    // clean up white space in the type and the name
		    $cmds->name = preg_replace('/\s+/', '', $cmds->name);
		    $cmdJSON = json_encode($cmds);
		
		    $stmt->bind_param("ss", $remoteName, $cmdJSON);
        $stmt->execute();

		    $stmt->close();
        $mysqli_obj->close();
      }
    }
    else {

    }
	}

}

# http://$home_url/iGloo/cgi-bin/s.php?n=CMDS.ig&d=HG10301114N&c=l8L1l9l4L1l8l1L1

# http://$home_url/iGloo/cgi-bin/s.php?n=DState.ds&d=HG14898122&c=PWOYESTEM022MIN009MAX035THM_NOFLM002FAN002AU1000AU2000CLD_NOHOO1234567TSTENAU130420150407PMTML22X%09Serial%20Number%09Brunswick%20West%09-37.76195526%09144.94239807%090.00000000%090.00000000%090.00000000%090.00000000&t=874bab9ec8b4accf4057042d487b6ed06eec67804158d1d78c7a818bb12daa0d&notify=y


// device token
$deviceToken  = "";
if (strcmp($_POST['t'], '') !== 0)
{
  $deviceToken = $_POST['t'];
}
elseif (strcmp($_GET['t'], '') !== 0)
{
  $deviceToken = $_GET['t'];
}

// debug
$debug = '';
if (strcmp($_POST['db'], '') !== 0)
{
  $debug = $_POST['db'];
}
elseif (strcmp($_GET['db'], '') !== 0)
{
  $debug = $_GET['db'];
}
    
// notify
$notify = '';
if (strcmp($_POST['notify'], '') !== 0)
{
  $notify = $_POST['notify'];
}
elseif (strcmp($_GET['notify'], '') !== 0)
{
  $notify = $_GET['notify'];
}
    
// n json, device name, c command, sync_db
if (strcmp($_POST['n'], '') !== 0 && strcmp($_POST['d'], '') !== 0 && strcmp($_POST['c'], '') !== 0)
{
  $type = "Post: ";
  $name  = $_POST['n'];
  $directory = $_POST['d'];
  $command  = $_POST['c'];
  $syncDatabase = $_POST['sdb'];
}
elseif (strcmp($_GET['n'], '') !== 0 && strcmp($_GET['d'], '') !== 0 && strcmp($_GET['c'], '') !== 0)
{
  $type = "Get: ";
  $name  = $_GET['n'];
  $directory  = $_GET['d'];
  $command  = $_GET['c'];
  $syncDatabase = $_GET['sdb'];
}
else
{
  $type = "Unknown: ";
  $name  = "?";
  $directory  = "?";
  $command = "";
  $syncDatabase = "n";
}

// c1, what is c1? comamnd1
if (strcmp($_POST['c1'], '') !== 0)
{
  $command1 = $_POST['c1'];
}
elseif (strcmp($_GET['c1'], '') !== 0)
{
  $command1 = $_GET['c1'];
}
else
{
  $command1 = "";
}

// cmd with json
if ($name=="CMDS.JSON")
{
  queueJSONCommand($directory,$command);
}

// original cmd
$originalCommand = $command;

if (strcmp($type, 'Unknown: ') !== 0)
{
  // Make database dir on server
  if (!file_exists($db_dir. "/". $directory)) 
  {
    mkdir ($db_dir. "/". $directory, 0777, true);
  }

  // cmd json
  if (strcmp($name, 'CMDS.ig') !== 0)
  {
    // look for TML
    $labelPosition = strpos($command, 'TML', 1);

    // position
    if ($labelPosition !== false)
    {
      // position 91
      if ($labelPosition < 91)
      {
        $dummyStr = substr('??????????', 0, 91 - $labelPosition);
        $command = substr($command, 0, $labelPosition).$dummyStr.substr($command, $labelPosition, strlen($command) - ($labelPosition + 1));
        echo 'labelPosition: '.$labelPosition.'   ';
      }
    }
  }


  // cmd prefix
  $commandPrefix = '';
  
  // cmd prefix     
  if (strcmp($name, 'CMDS.ig') == 0)
  {
     $commandPrefix = 'iGloo:';
  }
 
  // device token
 	if (strcmp($deviceToken, '') !== 0)
 	{
    $command = $command."\t".$deviceToken."\t1"."\t2"."\t3";
  }

  // insert content
  file_put_contents ($db_dir. "/". $directory. "/". $name, $commandPrefix. $command);

  if (strcmp($command1, '') !== 0)
  {
      file_put_contents ($db_dir. "/". $directory. "/". $name. "1", $commandPrefix.$command1);  
  }
        
	//date_default_timezone_set ('UTC');
	date_default_timezone_set('Australia/Melbourne');

  // cmd
	echo $command.'<br>';
	
  // chip id
	$chip_id = $directory;

  // 1st tab pos
	$firstTabPos = strpos ($command, "\t");
	
  // map entry
	$mapEntry = substr($command, 0, $firstTabPos);
	
  // echo 
	echo "entry='".$mapEntry."'<br>";
	
  // 2nd tab pos
	$secondTabPos = strpos ($command, "\t", $firstTabPos + 1);
	
  // map serial
	$mapSerial = substr ($command, $firstTabPos + 1, $secondTabPos - $firstTabPos - 1);

  // echo 
	echo "serial='".$mapSerial."'<br>";

  // 3rd tab pos
	$thirdTabPos = strpos ($command, "\t", $secondTabPos + 1);
	
  // suburb
	$mapSuburb = substr ($command, $secondTabPos + 1, $thirdTabPos - $secondTabPos- 1);
	
  // echo 
	echo "suburb='".$mapSuburb."'<br>";
	
	// 4th tab pos
	$forthTabPos = strpos ($command, "\t", $thirdTabPos + 1);
	
  // map latitude
	$mapLat = substr ($command, $thirdTabPos + 1, $forthTabPos - $thirdTabPos - 1);
	
  // echo 
	echo "lat='".$mapLat."'<br>";
	
	// 5th tab pos
	$fifthTabPos = strpos ($command, "\t", $forthTabPos + 1);
	
  // map longtitude
	$mapLon = substr ($command, $forthTabPos + 1, $fifthTabPos - $forthTabPos - 1);
	
  // ehco 
	echo "lon='".$mapLon."'<br>";
	
	// install date
	$directoryPath = $db_dir. "/". $directory. '/';
	$mtime = @filemtime($directoryPath);	
	$mapInstalled_date = date('Y-m-d', $mtime);
	echo "installed_date='".$mapInstalled_date."'<br>";


	if (strcmp($_POST['ndb'], '') !== 0 || strcmp($_GET['ndb'], '') !== 0)
  {
    // fn1
    echo "ndb found<br>";
    $fn1  = "";
    if (strcmp($_POST['fn1'], '') !== 0)
    {
      $fn1 = $_POST['fn1'];
    }
    elseif (strcmp($_GET['fn1'], '') !== 0)
    {
      $fn1 = $_GET['fn1'];
    }

    // fv1
    $fv1  = "";
    if (strcmp($_POST['fv1'], '') !== 0)
    {
      $fv1 = $_POST['fv1'];
    }
    elseif (strcmp($_GET['fv1'], '') !== 0)
    {
      $fv1 = $_GET['fv1'];
    }


    $fn2  = "";
    if (strcmp($_POST['fn2'], '') !== 0)
    {
        $fn2 = $_POST['fn2'];
    }
    elseif (strcmp($_GET['fn2'], '') !== 0)
    {
        $fn2 = $_GET['fn2'];
    }

    $fv2  = "";
    if (strcmp($_POST['fv2'], '') !== 0)
    {
        $fv2 = $_POST['fv2'];
    }
    elseif (strcmp($_GET['fv2'], '') !== 0)
    {
        $fv2 = $_GET['fv2'];
    }


    $fn3  = "";
    if (strcmp($_POST['fn3'], '') !== 0)
    {
        $fn3 = $_POST['fn3'];
    }
    elseif (strcmp($_GET['fn3'], '') !== 0)
    {
        $fn3 = $_GET['fn3'];
    }

    $fv3  = "";
    if (strcmp($_POST['fv3'], '') !== 0)
    {
        $fv3 = $_POST['fv3'];
    }
    elseif (strcmp($_GET['fv3'], '') !== 0)
    {
        $fv3 = $_GET['fv3'];
    }


    $fn4  = "";
    if (strcmp($_POST['fn4'], '') !== 0)
    {
        $fn4 = $_POST['fn4'];
    }
    elseif (strcmp($_GET['fn4'], '') !== 0)
    {
        $fn4 = $_GET['fn4'];
    }

    $fv4  = "";
    if (strcmp($_POST['fv4'], '') !== 0)
    {
        $fv4 = $_POST['fv4'];
    }
    elseif (strcmp($_GET['fv4'], '') !== 0)
    {
        $fv4 = $_GET['fv4'];
    }


    $fn5  = "";
    if (strcmp($_POST['fn5'], '') !== 0)
    {
        $fn5 = $_POST['fn5'];
    }
    elseif (strcmp($_GET['fn5'], '') !== 0)
    {
        $fn5 = $_GET['fn5'];
    }

    $fv5  = "";
    if (strcmp($_POST['fv5'], '') !== 0)
    {
        $fv5 = $_POST['fv5'];
    }
    elseif (strcmp($_GET['fv5'], '') !== 0)
    {
        $fv5 = $_GET['fv5'];
    }


    $fn6  = "";
    if (strcmp($_POST['fn6'], '') !== 0)
    {
        $fn6 = $_POST['fn6'];
    }
    elseif (strcmp($_GET['fn6'], '') !== 0)
    {
        $fn6 = $_GET['fn6'];
    }

    $fv6  = "";
    if (strcmp($_POST['fv6'], '') !== 0)
    {
        $fv6 = $_POST['fv6'];
    }
    elseif (strcmp($_GET['fv6'], '') !== 0)
    {
        $fv6 = $_GET['fv6'];
    }


    $fn7  = "";
    if (strcmp($_POST['fn7'], '') !== 0)
    {
        $fn7 = $_POST['fn7'];
    }
    elseif (strcmp($_GET['fn7'], '') !== 0)
    {
        $fn7 = $_GET['fn7'];
    }

    $fv7  = "";
    if (strcmp($_POST['fv7'], '') !== 0)
    {
        $fv7 = $_POST['fv7'];
    }
    elseif (strcmp($_GET['fv7'], '') !== 0)
    {
        $fv7 = $_GET['fv7'];
    }


    $fn8  = "";
    if (strcmp($_POST['fn8'], '') !== 0)
    {
        $fn8 = $_POST['fn8'];
    }
    elseif (strcmp($_GET['fn8'], '') !== 0)
    {
        $fn8 = $_GET['fn8'];
    }

    $fv8  = "";
    if (strcmp($_POST['fv8'], '') !== 0)

    {
        $fv8 = $_POST['fv8'];
    }
    elseif (strcmp($_GET['fv8'], '') !== 0)
    {
        $fv8 = $_GET['fv8'];
    }


    $fn9  = "";
    if (strcmp($_POST['fn9'], '') !== 0)
    {
        $fn9 = $_POST['fn9'];
    }
    elseif (strcmp($_GET['fn9'], '') !== 0)
    {
        $fn9 = $_GET['fn9'];
    }

    $fv9  = "";
    if (strcmp($_POST['fv9'], '') !== 0)
    {
        $fv9 = $_POST['fv9'];
    }
    elseif (strcmp($_GET['fv9'], '') !== 0)
    {
        $fv9 = $_GET['fv9'];
    }


    $fn10  = "";
    if (strcmp($_POST['fn10'], '') !== 0)
    {
        $fn10 = $_POST['fn10'];
    }
    elseif (strcmp($_GET['fn10'], '') !== 0)
    {
        $fn10 = $_GET['fn10'];
    }

    $fv10  = "";
    if (strcmp($_POST['fv10'], '') !== 0)
    {
        $fv10 = $_POST['fv10'];
    }
    elseif (strcmp($_GET['fv10'], '') !== 0)
    {
        $fv10 = $_GET['fv10'];
    }


    $fn11  = "";
    if (strcmp($_POST['fn11'], '') !== 0)
    {
        $fn11 = $_POST['fn11'];
    }
    elseif (strcmp($_GET['fn11'], '') !== 0)
    {
        $fn11 = $_GET['fn11'];
    }

    $fv11  = "";
    if (strcmp($_POST['fv11'], '') !== 0)
    {
        $fv11 = $_POST['fv11'];
    }
    elseif (strcmp($_GET['fv11'], '') !== 0)
    {
        $fv11 = $_GET['fv11'];
    }


    $fn12  = "";
    if (strcmp($_POST['fn12'], '') !== 0)
    {
        $fn12 = $_POST['fn12'];
    }
    elseif (strcmp($_GET['fn12'], '') !== 0)
    {
        $fn12 = $_GET['fn12'];
    }

    $fv12  = "";
    if (strcmp($_POST['fv12'], '') !== 0)
    {
        $fv12 = $_POST['fv12'];
    }
    elseif (strcmp($_GET['fv12'], '') !== 0)
    {
        $fv12 = $_GET['fv12'];
    }


    $fn13  = "";
    if (strcmp($_POST['fn13'], '') !== 0)
    {
        $fn13 = $_POST['fn13'];
    }
    elseif (strcmp($_GET['fn13'], '') !== 0)
    {
        $fn13 = $_GET['fn13'];
    }

    $fv13  = "";
    if (strcmp($_POST['fv13'], '') !== 0)
    {
        $fv13 = $_POST['fv13'];
    }
    elseif (strcmp($_GET['fv13'], '') !== 0)
    {
        $fv13 = $_GET['fv13'];
    }


    $fn14  = "";
    if (strcmp($_POST['fn14'], '') !== 0)
    {
        $fn14 = $_POST['fn14'];
    }
    elseif (strcmp($_GET['fn14'], '') !== 0)
    {
        $fn14 = $_GET['fn14'];
    }

    $fv14  = "";
    if (strcmp($_POST['fv14'], '') !== 0)
    {
        $fv14 = $_POST['fv14'];
    }
    elseif (strcmp($_GET['fv14'], '') !== 0)
    {
        $fv14 = $_GET['fv14'];
    }


    $fn15  = "";
    if (strcmp($_POST['fn15'], '') !== 0)
    {
        $fn15 = $_POST['fn15'];
    }
    elseif (strcmp($_GET['fn15'], '') !== 0)
    {
        $fn15 = $_GET['fn15'];
    }

    $fv15  = "";
    if (strcmp($_POST['fv15'], '') !== 0)
    {
        $fv15 = $_POST['fv15'];
    }
    elseif (strcmp($_GET['fv15'], '') !== 0)
    {
        $fv15 = $_GET['fv15'];
    }


    $fn16  = "";
    if (strcmp($_POST['fn16'], '') !== 0)
    {
        $fn16 = $_POST['fn16'];
    }
    elseif (strcmp($_GET['fn16'], '') !== 0)
    {
        $fn16 = $_GET['fn16'];
    }

    $fv16  = "";
    if (strcmp($_POST['fv16'], '') !== 0)
    {
        $fv16 = $_POST['fv16'];
    }
    elseif (strcmp($_GET['fv16'], '') !== 0)
    {
        $fv16 = $_GET['fv16'];
    }


    $fn17  = "";
    if (strcmp($_POST['fn17'], '') !== 0)
    {
        $fn17 = $_POST['fn17'];
    }
    elseif (strcmp($_GET['fn17'], '') !== 0)
    {
        $fn17 = $_GET['fn17'];
    }

    $fv17  = "";
    if (strcmp($_POST['fv17'], '') !== 0)
    {
        $fv17 = $_POST['fv17'];
    }
    elseif (strcmp($_GET['fv17'], '') !== 0)
    {
        $fv17 = $_GET['fv17'];
    }


    $fn18  = "";
    if (strcmp($_POST['fn18'], '') !== 0)
    {
        $fn18 = $_POST['fn18'];
    }
    elseif (strcmp($_GET['fn18'], '') !== 0)
    {
        $fn18 = $_GET['fn18'];
    }

    $fv18  = "";
    if (strcmp($_POST['fv18'], '') !== 0)
    {
        $fv18 = $_POST['fv18'];
    }
    elseif (strcmp($_GET['fv18'], '') !== 0)
    {
        $fv18 = $_GET['fv18'];
    }


    $fn19  = "";
    if (strcmp($_POST['fn19'], '') !== 0)
    {
        $fn19 = $_POST['fn19'];
    }
    elseif (strcmp($_GET['fn19'], '') !== 0)
    {
        $fn19 = $_GET['fn19'];
    }

    $fv19  = "";
    if (strcmp($_POST['fv19'], '') !== 0)
    {
        $fv19 = $_POST['fv19'];
    }
    elseif (strcmp($_GET['fv19'], '') !== 0)
    {
        $fv19 = $_GET['fv19'];
    }


    $fn20  = "";
    if (strcmp($_POST['fn20'], '') !== 0)
    {
        $fn20 = $_POST['fn20'];
    }
    elseif (strcmp($_GET['fn20'], '') !== 0)
    {
        $fn20 = $_GET['fn20'];
    }

    $fv20  = "";
    if (strcmp($_POST['fv20'], '') !== 0)
    {
        $fv20 = $_POST['fv20'];
    }
    elseif (strcmp($_GET['fv20'], '') !== 0)
    {
        $fv20 = $_GET['fv20'];
    }


    $fn21  = "";
    if (strcmp($_POST['fn21'], '') !== 0)
    {
        $fn21 = $_POST['fn21'];
    }
    elseif (strcmp($_GET['fn21'], '') !== 0)
    {
        $fn21 = $_GET['fn21'];
    }

    $fv21  = "";
    if (strcmp($_POST['fv21'], '') !== 0)
    {
        $fv21 = $_POST['fv21'];
    }
    elseif (strcmp($_GET['fv21'], '') !== 0)
    {
        $fv21 = $_GET['fv21'];
    }


    $fn22  = "";
    if (strcmp($_POST['fn22'], '') !== 0)
    {
        $fn22 = $_POST['fn22'];
    }
    elseif (strcmp($_GET['fn22'], '') !== 0)
    {
        $fn22 = $_GET['fn22'];
    }

    $fv22  = "";
    if (strcmp($_POST['fv22'], '') !== 0)
    {
        $fv22 = $_POST['fv22'];
    }
    elseif (strcmp($_GET['fv22'], '') !== 0)
    {
        $fv22 = $_GET['fv22'];
    }


    $fn23  = "";
    if (strcmp($_POST['fn23'], '') !== 0)
    {
        $fn23 = $_POST['fn23'];
    }
    elseif (strcmp($_GET['fn23'], '') !== 0)
    {
        $fn23 = $_GET['fn23'];
    }

    $fv23  = "";
    if (strcmp($_POST['fv23'], '') !== 0)
    {
        $fv23 = $_POST['fv23'];
    }
    elseif (strcmp($_GET['fv23'], '') !== 0)
    {
        $fv23 = $_GET['fv23'];
    }


    $fn24  = "";
    if (strcmp($_POST['fn24'], '') !== 0)
    {
        $fn24 = $_POST['fn24'];
    }
    elseif (strcmp($_GET['fn24'], '') !== 0)
    {
        $fn24 = $_GET['fn24'];
    }

    $fv24  = "";
    if (strcmp($_POST['fv24'], '') !== 0)
    {
        $fv24 = $_POST['fv24'];
    }
    elseif (strcmp($_GET['fv24'], '') !== 0)
    {
        $fv24 = $_GET['fv24'];
    }


    $fn25  = "";
    if (strcmp($_POST['fn25'], '') !== 0)
    {
        $fn25 = $_POST['fn25'];
    }
    elseif (strcmp($_GET['fn25'], '') !== 0)
    {
        $fn25 = $_GET['fn25'];
    }

    $fv25  = "";
    if (strcmp($_POST['fv25'], '') !== 0)
    {
        $fv25 = $_POST['fv25'];
    }
    elseif (strcmp($_GET['fv25'], '') !== 0)
    {
        $fv25 = $_GET['fv25'];
    }


    $fn26  = "";
    if (strcmp($_POST['fn26'], '') !== 0)
    {
        $fn26 = $_POST['fn26'];
    }
    elseif (strcmp($_GET['fn26'], '') !== 0)
    {
        $fn26 = $_GET['fn26'];
    }

    $fv26  = "";
    if (strcmp($_POST['fv26'], '') !== 0)
    {
        $fv26 = $_POST['fv26'];
    }
    elseif (strcmp($_GET['fv26'], '') !== 0)
    {
        $fv26 = $_GET['fv26'];
    }


    $fn27  = "";
    if (strcmp($_POST['fn27'], '') !== 0)
    {
        $fn27 = $_POST['fn27'];
    }
    elseif (strcmp($_GET['fn27'], '') !== 0)
    {
        $fn27 = $_GET['fn27'];
    }

    $fv27  = "";
    if (strcmp($_POST['fv27'], '') !== 0)
    {
        $fv27 = $_POST['fv27'];
    }
    elseif (strcmp($_GET['fv27'], '') !== 0)
    {
        $fv27 = $_GET['fv27'];
    }


    $fn28  = "";
    if (strcmp($_POST['fn28'], '') !== 0)
    {
        $fn28 = $_POST['fn28'];
    }
    elseif (strcmp($_GET['fn28'], '') !== 0)
    {
        $fn28 = $_GET['fn28'];
    }

    $fv28  = "";
    if (strcmp($_POST['fv28'], '') !== 0)
    {
        $fv28 = $_POST['fv28'];
    }
    elseif (strcmp($_GET['fv28'], '') !== 0)
    {
        $fv28 = $_GET['fv28'];
    }


    $fn29  = "";
    if (strcmp($_POST['fn29'], '') !== 0)
    {
        $fn29 = $_POST['fn29'];
    }
    elseif (strcmp($_GET['fn29'], '') !== 0)
    {
        $fn29 = $_GET['fn29'];
    }

    $fv29  = "";
    if (strcmp($_POST['fv29'], '') !== 0)
    {
        $fv29 = $_POST['fv29'];
    }
    elseif (strcmp($_GET['fv29'], '') !== 0)
    {
        $fv29 = $_GET['fv29'];
    }


    $fn30  = "";
    if (strcmp($_POST['fn30'], '') !== 0)
    {
        $fn30 = $_POST['fn30'];
    }
    elseif (strcmp($_GET['fn30'], '') !== 0)
    {
        $fn30 = $_GET['fn30'];
    }

    $fv30  = "";
    if (strcmp($_POST['fv30'], '') !== 0)
    {
        $fv30 = $_POST['fv30'];
    }
    elseif (strcmp($_GET['fv30'], '') !== 0)
    {
        $fv30 = $_GET['fv30'];
    }


    $fn31  = "";
    if (strcmp($_POST['fn31'], '') !== 0)
    {
        $fn31 = $_POST['fn31'];
    }
    elseif (strcmp($_GET['fn31'], '') !== 0)
    {
        $fn31 = $_GET['fn31'];
    }

    $fv31  = "";
    if (strcmp($_POST['fv31'], '') !== 0)
    {
        $fv31 = $_POST['fv31'];
    }
    elseif (strcmp($_GET['fv31'], '') !== 0)
    {
        $fv31 = $_GET['fv31'];
    }


    $fn32  = "";
    if (strcmp($_POST['fn32'], '') !== 0)
    {
        $fn32 = $_POST['fn32'];
    }
    elseif (strcmp($_GET['fn32'], '') !== 0)
    {
        $fn32 = $_GET['fn32'];
    }

    $fv32  = "";
    if (strcmp($_POST['fv32'], '') !== 0)
    {
        $fv32 = $_POST['fv32'];
    }
    elseif (strcmp($_GET['fv32'], '') !== 0)
    {
        $fv32 = $_GET['fv32'];
    }


    $fn33  = "";
    if (strcmp($_POST['fn33'], '') !== 0)
    {
        $fn33 = $_POST['fn33'];
    }
    elseif (strcmp($_GET['fn33'], '') !== 0)
    {
        $fn33 = $_GET['fn33'];
    }

    $fv33  = "";
    if (strcmp($_POST['fv33'], '') !== 0)
    {
        $fv33 = $_POST['fv33'];
    }
    elseif (strcmp($_GET['fv33'], '') !== 0)
    {
        $fv33 = $_GET['fv33'];
    }


    $fn34  = "";
    if (strcmp($_POST['fn34'], '') !== 0)
    {
        $fn34 = $_POST['fn34'];
    }
    elseif (strcmp($_GET['fn34'], '') !== 0)
    {
        $fn34 = $_GET['fn34'];
    }

    $fv34  = "";
    if (strcmp($_POST['fv34'], '') !== 0)
    {
        $fv34 = $_POST['fv34'];
    }
    elseif (strcmp($_GET['fv34'], '') !== 0)
    {
        $fv34 = $_GET['fv34'];
    }

       
    //   
	  $postdata = http_build_query
      (
        array(
          'entry'  => str_replace(" ", "_", $mapEntry),
          'serial' => str_replace(" ", "_", $mapSerial),
          'suburb' => str_replace(" ", "_", $mapSuburb),
          'lat'    => $mapLat,
          'lon'    => $mapLon,
          'installed_date'    => $mapInstalled_date,
          $fn1  => str_replace(" ", "_", $fv1),
          $fn2  => str_replace(" ", "_", $fv2),
          $fn3  => str_replace(" ", "_", $fv3),
          $fn4  => str_replace(" ", "_", $fv4),
          $fn5  => str_replace(" ", "_", $fv5),
          $fn6  => str_replace(" ", "_", $fv6),
          $fn7  => str_replace(" ", "_", $fv7),
          $fn8  => str_replace(" ", "_", $fv8),
          $fn9  => str_replace(" ", "_", $fv9),
          $fn10  => str_replace(" ", "_", $fv10),
          $fn11  => str_replace(" ", "_", $fv11),
          $fn12  => str_replace(" ", "_", $fv12),
          $fn13  => str_replace(" ", "_", $fv13),
          $fn14  => str_replace(" ", "_", $fv14),
          $fn15  => str_replace(" ", "_", $fv15),
          $fn16  => str_replace(" ", "_", $fv16),
          $fn17  => str_replace(" ", "_", $fv17),
          $fn18  => str_replace(" ", "_", $fv18),
          $fn19  => str_replace(" ", "_", $fv19),
          $fn20  => str_replace(" ", "_", $fv20),
          $fn21  => str_replace(" ", "_", $fv21),
          $fn22  => str_replace(" ", "_", $fv22),
          $fn23  => str_replace(" ", "_", $fv23),
          $fn24  => str_replace(" ", "_", $fv24),
          $fn25  => str_replace(" ", "_", $fv25),
          $fn26  => str_replace(" ", "_", $fv26),
          $fn27  => str_replace(" ", "_", $fv27),
          $fn28  => str_replace(" ", "_", $fv28),
          $fn29  => str_replace(" ", "_", $fv29),
          $fn30  => str_replace(" ", "_", $fv30),
          $fn31  => str_replace(" ", "_", $fv31),
          $fn32  => str_replace(" ", "_", $fv32),
          $fn33  => str_replace(" ", "_", $fv33),
          $fn34  => str_replace(" ", "_", $fv34)
        )
      );
  }
  else
  {   
    echo "ndb not found<br>";
            
	  $postdata = http_build_query
    (
      array(
        'entry'  => str_replace(" ", "_", $mapEntry),
        'serial' => str_replace(" ", "_", $mapSerial),
        'suburb' => str_replace(" ", "_", $mapSuburb),
        'lat'    => $mapLat,
        'lon'    => $mapLon, 
        'installed_date'    => $mapInstalled_date
      )
    );
  }
  
  $opts = array('https' =>
    array(
      'method'  => 'POST',
      'header'  => 'Content-type: application/x-www-form-urlencoded',
      'content' => $postdata,
  	  'max_redirects' => '0',
      'ignore_errors' => '1'
    )
  );

  $context  = stream_context_create($opts);

	
	if (strcmp($_POST['ndb'], '') !== 0 || strcmp($_GET['ndb'], '') !== 0)
  {
    $GET_String =
      '?fn1='.$fn1.'&fv1='.$fv1.'&fn2='.$fn2.'&fv2='.$fv2.'&fn3='.$fn3.'&fv3='.$fv3.'&fn4='.$fn4.'&fv4='.$fv4.'&fn5='.$fn5.'&fv5='.$fv5.
                    '&fn6='.$fn6.'&fv6='.$fv6.'&fn7='.$fn7.'&fv7='.$fv7.'&fn8='.$fn8.'&fv8='.$fv8.'&fn9='.$fn9.'&fv9='.$fv9.'&fn10='.$fn10.'&fv10='.$fv10.
                    '&fn11='.$fn11.'&fv11='.$fv11.'&fn12='.$fn12.'&fv12='.$fv12.'&fn13='.$fn13.'&fv13='.$fv13.'&fn14='.$fn14.'&fv14='.$fv14.'&fn15='.$fn15.
                    '&fv15='.$fv15.'&fn16='.$fn16.'&fv16='.$fv16.'&fn17='.$fn17.'&fv17='.$fv17.'&fn18='.$fn18.'&fv18='.$fv18.'&fn19='.$fn19.'&fv19='.$fv19.
                    '&fn20='.$fn20.'&fv20='.$fv20.'&fn21='.$fn21.'&fv21='.$fv21.'&fn22='.$fn22.'&fv22='.$fv22.'&fn23='.$fn23.'&fv23='.$fv23.'&fn24='.$fn24.
                    '&fv24='.$fv24.'&fn25='.$fn25.'&fv25='.$fv25.'&fn26='.$fn26.'&fv26='.$fv26.'&fn27='.$fn27.'&fv27='.$fv27.'&fn28='.$fn28.'&fv28='.$fv28.
                    '&fn29='.$fn29.'&fv29='.$fv29.'&fn30='.$fn30.'&fv30='.$fv30.'&fn31='.$fn31.'&fv31='.$fv31.'&fn32='.$fn32.'&fv32='.$fv32.'&fn33='.$fn33.
                    '&fv33='.$fv33.'&fn34='.$fn34.'&fv34='.$fv34.'&entry='.$mapEntry.
                    '&serial='.$mapSerial.'&suburb='.$mapSuburb.'&lat='.$mapLat.'&lon='.$mapLon.'&installed_date='.$mapInstalled_date.'&chip_id='.$chip_id;

                
    $addEntryURL = "http://$home_url/iGloo/cgi-bin/addentry_2.php";
	    
	  echo "<br><br>--------------************$GET_String: ".$GET_String."****************-----------<br><br>";
	}
	else
	{
    $GET_String = '?entry='.$mapEntry.'&serial='.$mapSerial.'&suburb='.$mapSuburb.'&lat='.
			    $mapLat.'&lon='.$mapLon.'&installed_date='.$mapInstalled_date.
			   '&chip_id='.$chip_id ; 
			
    $addEntryURL = "http://$home_url/iGloo/cgi-bin/addentry.php";
	}

	$addEntryURLWith_GET = $addEntryURL.str_replace(" ", "%20", $GET_String);
	
	echo "addEntryURLWith_GET: ".$addEntryURLWith_GET."<br>";
      
  $result = file_get_contents ($addEntryURLWith_GET, false, $context);
      
  echo "result: '".$result."'<br>";
	
  /*
  entry=PWO_NOTEM025MIN009MAX035THM_NOFLM001FAN002AU1000AU2000CLD_NOHOO1234567TSTENAU220120150126PMTMLOff
  serial=6661234
  suburb=Avalon
  lat=-33.6336
  lon=151.3326
  installed_date=2014-06-05

  http://$home_url/iGloo/cgi-bin/s_test.php?
  n=DState.ds&
  d=HG14898122&c=PWOYESTEM022MIN009MAX035THM_NOFLM000FAN000AU1000AU2000CLD_NOHOO1234567TSTENAU210120150504PMTML22X%09123321%09Manly%09-33.7962%09151.2827%097.61728382%097.60031414%097.63293791%090.00000000&t=874bab9ec8b4accf4057042d487b6ed06eec67804158d1d78c7a818bb12daa0d
  */

  if (strcmp($notify, '') !== 0)
  {
    processAlerts($debug, $directory, $deviceToken);
  }
                  
    
  if (0 && strcmp($syncDatabase, "n") !== 0)
  {
    error_reporting(~0);
    ini_set ('display_errors', 1);

    $url_GET = "http://lasertrail.com/iGloo/s.php?n=" . $name . "&d=" . $directory . "&c=" . $commandPrefix.$command . "&sdb=n";

    $result = file_get_contents($url_GET);

    echo 'result: '.var_dump($result).'<br>';
          
    # Output information about allow_url_fopen:
    if (ini_get('allow_url_fopen') == 1) 
    {
      echo '<p style="color: #0A0;">fopen is allowed on this host.</p>';
    }
    else 
    {
       echo '<p style="color: #A00;">fopen is not allowed on this host.</p>';
    }

  }
}
else
{
  error_log ( "Error: Name = '" . $name . "', filename = '" . $directory . "'." );
}
    
    
function processAlerts($pDebug, $pCodeID, $originalDeviceToken)
{
  if ($pDebug == 'y')
  {
    echo '<br>processAlerts<br>';
  }
      
  $deviceURL = $db_dir. "/". $pCodeID. '/dd.xml';

  if (file_exists($deviceURL))
  {
    $deviceData = new SimpleXMLElement($deviceURL, NULL, TRUE);

    if ($pDebug == 'y')
    {
   	  echo '<br>'.$deviceData.'<br>';
    }

    if ($deviceData)
    {
      $alertMessage = 'iGloo Energy Control: Device Changed';
      $soundFile = 'Notification_HeaterOn.m4a';

      if (file_exists($db_dir. "/". $pCodeID. '/DState.ds'))
      {
        $currentState = file_get_contents("http://$home_url/iGloo/database/". $pCodeID. '/DState.ds');
        $powerCode = strtoupper(substr($currentState, 0, 6));

        if ($pDebug == 'y')
        {
          echo '<br>'.$currentState.'<br>';
        }

        if (strcmp($powerCode, 'PWO_NO') == 0)
        {
          $alertMessage = 'iGloo Energy Control: device is now off';
          //$soundFile = 'LogFireNowOff.m4a';
          $soundFile = 'iGloo Jingle.m4a';
        }
        else
        {
          $alertMessage = 'iGloo Energy Control: device is now on';
          //$soundFile = 'LogFireNowOn.m4a';
          $soundFile = 'iGloo Jingle.m4a';
        }
      }


      foreach ($deviceData->subscriber as $subscriber)
	    {
        $currentDeviceToken = $subscriber[0]['token'];
        $stateNotifications = $subscriber[0]['state'];

	      $zoneID   = $subscriber[0]['zoneID'];
    	  $deviceID = $subscriber[0]['deviceID'];

	      //$currentDeviceToken = '874bab9ec8b4accf4057042d487b6ed06eec67804158d1d78c7a818bb12daa0d';
	            
	      if ($pDebug == 'y')
        {
          echo '<br>token: ('.$currentDeviceToken.')<br>';
          echo '<br>state: ('.$subscriber[0]['state'].')<br>';
          echo '<br>zoneID: ('.$subscriber[0]['zoneID'].')<br>';
          echo '<br>deviceID: ('.$subscriber[0]['deviceID'].')<br>';
        }

        //if ($stateNotifications == 'y' && strcmp($originalDeviceToken, $currentDeviceToken) !== 0)
        if ($stateNotifications == 'y' && $originalDeviceToken != $currentDeviceToken)
        {
	        try 
	        { 		    
            alertClient ($pDebug, $currentDeviceToken, $alertMessage, $soundFile, $zoneID, $deviceID, $pCodeID, 'iGlooPushPublic.pem');
    			} 
    			catch (Exception $e) 
    			{
        			echo "Push Notification Error: ".$e;
    			}
	                
	      }
	    }
	  }
	}
}

define ('PROWL_OK',                     0);
define ('PROWL_ERR_IMPLEMENTATION',  -100);
define ('PROWL_ERR_INVALID_API_KEY', -101);
define ('PROWL_ERR_REQUESTS_LIMIT',  -102);
define ('PROWL_ERR_NOT_APPROVED',    -103);
define ('PROWL_ERR_INTERNAL',        -104); 
define ('PROWL_ERR_UNKNOWN',         -105);
define ('PROWL_ERR_OUT_OF_MEMORY',   -106);
define ('PROWL_ERR_INVALID_HANDLE',  -107);

define ('MGR_READ_FILE',               -1);
define ('MGR_SEND_REQUEST',            -2);
define ('MGR_OPEN_URI',                -3);
define ('MGR_CONNECT_SERVER',          -4);
define ('MGR_INTERNET_OPEN',           -5);

    
function alertClient($pDebug, $deviceToken, $message, $alertSoundFile, $pZoneID, $pDeviceID, $pCodeID, $pemFileName)
{  	   
  if ($pDebug == 'y')
  {
    echo 'alertClient<br>';
  }

  $passphrase = 'iGlooPush666';
  $ctx = stream_context_create();    
  stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFileName);
  stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
  
  // Open a connection to the APNS server
  try 
	{          
    $fp = stream_socket_client ('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
  } 
  catch (Exception $e) 
  {
    echo "Push Notification Error: ".$e." (".$fp.")<br>";
  }
	
  if ($fp)
  {
    echo '<br>It worked!<br>';

    // Create the payload body
    $body['aps'] = array('alert' => $message, 'sound' => $alertSoundFile, 'zoneID' => $pZoneID, 'deviceID' => $pDeviceID, 'directory' => $pCodeID);

    // Encode the payload as JSON
    $payload = json_encode($body);

    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

    // Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));

    echo "pemFileName (".$pemFileName."), ";
    echo "passphrase (".$passphrase."), ";
    echo "result (".$result.")  ";

    if ($pDebug == 'y')
    {
      if (!$result)
      echo 'Message not delivered:'. $deviceToken . PHP_EOL."<br>";
        else
      echo 'Message successfully delivered: '.$deviceToken . PHP_EOL."<br>";

      echo "<br>Result: ".$result."<br>";

      switch (intval($result))
	    {
        case PROWL_OK:
        echo "Result: PROWL_OK (".$result.")<br>";
        break;

        case PROWL_ERR_IMPLEMENTATION:
        echo "Result: PROWL_ERR_IMPLEMENTATION (".$result.")<br>";
        break;

        case PROWL_ERR_INVALID_API_KEY:
        echo "Result: PROWL_ERR_INVALID_API_KEY (".$result.")<br>";
        break;

        case PROWL_ERR_REQUESTS_LIMIT:
        echo "Result: PROWL_ERR_REQUESTS_LIMIT (".$result.")<br>";
        break;

        case PROWL_ERR_NOT_APPROVED:
        echo "Result: PROWL_ERR_NOT_APPROVED (".$result.")<br>";
        break;

        case PROWL_ERR_INTERNAL:
        echo "Result: PROWL_ERR_INTERNAL (".$result.")<br>";
        break;

        case PROWL_ERR_UNKNOWN:
        echo "Result: PROWL_ERR_UNKNOWN (".$result.")<br>";
        break;

        case PROWL_ERR_OUT_OF_MEMORY:
        echo "Result: PROWL_ERR_OUT_OF_MEMORY (".$result.")<br>";
        break;

        case PROWL_ERR_INVALID_HANDLE:
        echo "Result: PROWL_ERR_INVALID_HANDLE (".$result.")<br>";
        break;

        case MGR_READ_FILE:
        echo "Result: MGR_READ_FILE (".$result.")<br>";
        break;

        case MGR_SEND_REQUEST:
        echo "Result: MGR_SEND_REQUEST (".$result.")<br>";
        break;

        case MGR_OPEN_URI:
        echo "Result: MGR_OPEN_URI (".$result.")<br>";
        break;

        case MGR_CONNECT_SERVER:
        echo "Result: MGR_CONNECT_SERVER (".$result.")<br>";
        break;

        case MGR_INTERNET_OPEN:
        echo "Result: MGR_INTERNET_OPEN (".$result.")<br>";
        break;

        default:
        echo "Result: (".$result.")<br>";
        break;

      }
    }

    // Close the connection to the server
    fclose($fp);
  }
}

