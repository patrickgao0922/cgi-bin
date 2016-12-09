<?php

require_once "./config.php";

global $db_dir;
global $home_url;

    # http://igloohomecontrol.com/iGloo/cgi-bin/s.php?n=CMDS.ig&d=HG10301114N&c=l8L1l9l4L1l8l1L1

    # http://igloohomecontrol.com/iGloo/cgi-bin/s.php?n=DState.ds&d=HG14898122&c=PWOYESTEM022MIN009MAX035THM_NOFLM002FAN002AU1000AU2000CLD_NOHOO1234567TSTENAU130420150407PMTML22X%09Serial%20Number%09Brunswick%20West%09-37.76195526%09144.94239807%090.00000000%090.00000000%090.00000000%090.00000000&t=874bab9ec8b4accf4057042d487b6ed06eec67804158d1d78c7a818bb12daa0d&notify=y

    // Require https
    /*if ($_SERVER['HTTPS'] != "on") 
    {
        $url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        header("Location: $url");
        exit;
    }*/
    
/*
    if (strcmp($_POST['n'], '') !== 0)
    {
       $temp  = $_POST['n'];
       echo ' name: '.$temp.'<br>\n';

    }
    else
    {
       echo 'No n:';
    }

    if (strcmp($_POST['d'], '') !== 0)
    {
       $temp  = $_POST['d'];
       echo ' directory: '.$temp.'<br>\n';

    }
    else
    {
       echo 'No d:';
    }

    if (strcmp($_POST['c'], '') !== 0)
    {
       $temp  = $_POST['c'];
       echo ' command: '.$temp.'<br>\n';

    }
    else
    {
       echo 'No c:';
    }
*/

/*
    if (strcmp($_POST['n'], '') !== 0) echo " $_POST['n']: ".$_POST['n'].'<br>\n';
    if (strcmp($_POST['d'], '') !== 0) echo " $_POST['d']: ".$_POST['d'].'<br>\n';
    if (strcmp($_POST['c'], '') !== 0) echo " $_POST['c']: ".$_POST['c'].'<br>\n';

    if (strcmp($_GET['n'], '') !== 0) echo " $_GET['n']: ".$_GET['n'].'<br>\n';
    if (strcmp($_GET['d'], '') !== 0) echo " $_GET['d']: ".$_GET['d'].'<br>\n';
    if (strcmp($_GET['c'], '') !== 0) echo " $_GET['c']: ".$_GET['c'].'<br>\n';
*/

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
    

// n == name
// d == directory
// c == command
// sdb == sync_db    
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


// c1 == command1
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
    
// original cmd    
$originalCommand = $command;

/*
  echo ' type: '.$type.'<br>\n';
  echo ' name: '.$name.'<br>\n';
  echo ' directory: '.$directory.'<br>\n';
  echo ' command: '.$command.'<br>\n';
  echo ' syncDatabase: '.$syncDatabase.'<br>\n';
 */

if (strcmp($type, 'Unknown: ') !== 0)
{
  if (!file_exists($db_dir. "/". $directory)) 
  {
    mkdir ($db_dir. "/". $directory, 0777, true);
  }
        
  //if (strcmp($name, 'CMDS.ig') == 0)
  //{
  //   $command = 'iGloo:'.$command;
  //}
  //else
  //{
  //    $command = substr($command, 0, 92)."??".substr($command, 92, strlen($command) - 93);
  //}

  if (strcmp($name, 'CMDS.ig') !== 0)
  {
    //$command = substr($command, 0, 92)."??".substr($command, 92, strlen($command) - 93);

    $labelPosition = strpos($command, 'TML', 1);

    if ($labelPosition !== false)
    {
      if ($labelPosition < 91)
      {
        $dummyStr = substr('??????????', 0, 91 - $labelPosition);
        $command = substr($command, 0, $labelPosition).$dummyStr.substr($command, $labelPosition, strlen($command) - ($labelPosition + 1));
        echo 'labelPosition: '.$labelPosition.'   ';
      }
    }
  }

  $commandPrefix = '';
       
  if (strcmp($name, 'CMDS.ig') == 0)
  {
    $commandPrefix = 'iGloo:';
  }
 
 	if (strcmp($deviceToken, '') !== 0)
 	{
    $command = $command."\t".$deviceToken."\t1"."\t2"."\t3";
  }

  file_put_contents ($db_dir. "/". $directory. "/". $name, $commandPrefix. $command);

  if (strcmp($command1, '') !== 0)
  {
    file_put_contents ($db_dir. "/". $directory. "/". $name. "1", $commandPrefix. $command1);  
  }

  //echo $type."Name = '".$name."', directory = '".$directory."', command = '".$command."'";

  //echo 'xxxxx'.$deviceToken.'yyyyy';

  // set the default timezone to use. Available since PHP 5.1

  //date_default_timezone_set ('UTC');
  date_default_timezone_set('Australia/Melbourne');

	//echo $directory.': ('.$mtime.')  :'.date('Y-m-d', $mtime).'<br>';
	
	echo $command.'<br>';
	
	$chip_id = $directory;

	$firstTabPos = strpos ($command, "\t");
	
	$mapEntry = substr ($command, 0, $firstTabPos);
	
	echo "entry='".$mapEntry."'<br>";
	
	$secondTabPos = strpos ($command, "\t", $firstTabPos + 1);
	
	$mapSerial = substr ($command, $firstTabPos + 1, $secondTabPos - $firstTabPos - 1);

	echo "serial='".$mapSerial."'<br>";

	$thirdTabPos = strpos ($command, "\t", $secondTabPos + 1);
	
	$mapSuburb = substr ($command, $secondTabPos + 1, $thirdTabPos - $secondTabPos- 1);
	//$mapSuburb = mysql_escape_string(substr ($command, $secondTabPos + 1, $thirdTabPos - $secondTabPos- 1));
	
	echo "suburb='".$mapSuburb."'<br>";
	
	
	$forthTabPos = strpos ($command, "\t", $thirdTabPos + 1);
	
	$mapLat = substr ($command, $thirdTabPos + 1, $forthTabPos - $thirdTabPos - 1);
	
	echo "lat='".$mapLat."'<br>";
	
	
	$fifthTabPos = strpos ($command, "\t", $forthTabPos + 1);
	
	$mapLon = substr ($command, $forthTabPos + 1, $fifthTabPos - $forthTabPos - 1);
	
	echo "lon='".$mapLon."'<br>";
	
	
	$directoryPath = $db_dir. "/". $directory. '/';
	$mtime = @filemtime($directoryPath);
	//$mtime = file_exists($directoryPath)?filemtime($directoryPath):'';
	
	$mapInstalled_date = date('Y-m-d', $mtime);

	echo "installed_date='".$mapInstalled_date."'<br>";

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

	$GET_String = '?entry='.$mapEntry.'&serial='.$mapSerial.'&suburb='.$mapSuburb.'&lat='.
				$mapLat.'&lon='.$mapLon.'&installed_date='.$mapInstalled_date.
				'&chip_id='.$chip_id ;
	
	$addEntryURL = "http://". $home_url. "/iGloo/cgi-bin/addentry.php";
	
	$addEntryURLWith_GET = $addEntryURL.str_replace(" ", "%20", $GET_String);
	
	echo "addEntryURLWith_GET: ".$addEntryURLWith_GET."<br>";

  //$result = file_get_contents ('http://igloohomecontrol.com/iGloo/cgi-bin/addentry.php', false, $context);
  $result = file_get_contents ($addEntryURLWith_GET, false, $context);    
  echo "result: '".$result."'<br>";
	
  /*
  entry=PWO_NOTEM025MIN009MAX035THM_NOFLM001FAN002AU1000AU2000CLD_NOHOO1234567TSTENAU220120150126PMTMLOff
  serial=6661234
  suburb=Avalon
  lat=-33.6336
  lon=151.3326
  installed_date=2014-06-05

  http://igloohomecontrol.com/iGloo/cgi-bin/s_test.php?
  n=DState.ds&
  d=HG14898122&c=PWOYESTEM022MIN009MAX035THM_NOFLM000FAN000AU1000AU2000CLD_NOHOO1234567TSTENAU210120150504PMTML22X%09123321%09Manly%09-33.7962%09151.2827%097.61728382%097.60031414%097.63293791%090.00000000&t=874bab9ec8b4accf4057042d487b6ed06eec67804158d1d78c7a818bb12daa0d
  */

  if (strcmp($notify, '') !== 0)
  {
    processAlerts($debug, $directory, $deviceToken);
  }
                          
  /*
  echo ' type: '.$type.'<br>\n';
  echo ' name: '.$name.'<br>\n';
  echo ' directory: '.$directory.'<br>\n';
  echo ' command: '.$command.'<br>\n';
  echo ' syncDatabase: '.$syncDatabase.'<br>\n';*/
    
  if (0 && strcmp($syncDatabase, "n") !== 0)
  {
    error_reporting(~0);
    ini_set ('display_errors', 1);

  /*
              $postdata = http_build_query
              (
                  array(
                        'n' => $name,
                        'd' => $directory,
                        'c' => 'iGloo:'.$command,
                      'sdb' => 'n'
                       )
              );

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

              $result = file_get_contents('http://lasertrail.com/iGloo/s.php', false, $context);


              //echo '...context:'.$context.'<br>';
  */

  /*

              $sUrl = 'http://lasertrail.com/iGloo/s.php';
    
              $params = array('http' => array(
                              'method' => 'POST',
                              'content' => 'n='.$name.'&d='.$directory.'&c=iGloo'.$command.'&sdb=n'
                             ));

              $ctx = stream_context_create($params);
              $fp = @fopen($sUrl, 'rb', false, $ctx);
              if (!$fp)
              {
                  throw new Exception("Problem with $sUrl, $php_errormsg");
              }

              $result = @stream_get_contents($fp);
              if ($result === false) 
              {
                  throw new Exception("Problem reading data from $sUrl, $php_errormsg");
              }
   */
              
    $url_GET = "http://lasertrail.com/iGloo/s.php?n=" . $name . "&d=" . $directory . "&c=" . $commandPrefix.$command . "&sdb=n";

    $result = file_get_contents($url_GET);

    //echo 'url_GET: '.$url_GET.'<br>';            
    //echo 'result: '.$result.'<br>';
    //$jsonData = file_get_contents($url);

    echo 'result: '.var_dump($result).'<br>';
    //print '<p>', var_dump($jsonData), '</p>';

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
  global $db_dir;
  global $home_url;

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
        $currentState = file_get_contents("http://". $home_url. "/iGloo/database/". $pCodeID. '/DState.ds');

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
  //$passphrase = '';

  $ctx = stream_context_create();
  //stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
  //stream_context_set_option($ctx, 'ssl', 'local_cert', 'iGlooPush.pem');

  stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFileName);
  stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

  //stream_context_set_option($ctx, 'tls', 'local_cert', $pemFileName);
  //stream_context_set_option($ctx, 'tls', 'passphrase', $passphrase);

        
  // Open a connection to the APNS server
  try 
	{
    //$fp = stream_socket_client ('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);            
    //$fp = stream_socket_client ('tls://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
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
    
?>
