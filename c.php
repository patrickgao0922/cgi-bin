<?php

include_once "./config.php";

global $db_dir;
global $home_url;

// Add devices but in file.
// igloohomecontrol.com/iGloo/cgi-bin/c.php?c=HG10301114N&d=y

// debug, zone_id, device_id    
$debug     = '';
$zoneID    = "";
$deviceID  = "";
    
// c is the device name
if (strcmp($_POST['c'], '') !== 0)
{
  $type = "Post: ";
  $codeID = $_POST['c'];
}
elseif (strcmp($_GET['c'], '') !== 0)
{
  $type = "Get: ";
  $codeID = $_GET['c'];
}
else
{
  $type = "Unknown: ";
  $codeID = "";
}
    
// debug    
if (strcmp($_POST['db'], '') !== 0)
{
  $debug = $_POST['db'];
}
elseif (strcmp($_GET['db'], '') !== 0)
{
  $debug = $_GET['db'];
}
else
{
  $debug = "";
}
 
// delete   
if (strcmp($_POST['d'], '') !== 0)
{
  $deleteFileWhenDone = $_POST['d'];
}
elseif (strcmp($_GET['d'], '') !== 0)
{
  $deleteFileWhenDone = $_GET['d'];
}
else
{
  $deleteFileWhenDone = "";
}


if (strcmp($codeID, '') !== 0)
{
  // if cmds.ig here, used json
  if (!file_exists($db_dir. "/". $codeID. '/CMDS.ig'))
  {
    echo 'iGloo:XXX666';
  }
  else
  {
    // grab json from cmds.ig
    $lines = file_get_contents("http://". $home_url. "/iGloo/database/". $codeID. '/CMDS.ig');
         
    // what is p-cmds?
    if (file_exists($db_dir. "/". $codeID. '/PCMDS.ig'))
    {
      echo 'iGloo:XXX666';
      $deleteFileWhenDone = 'y';
    }
    else
    {
      echo $lines;
    }

    // no delete
    // write cmd to p-cmds.ig 
    if (strcmp($deleteFileWhenDone, 'n') == 0)
    {
      #echo '<p>Do not Delete '.$codeID.'/CMDS.ig';
      file_put_contents ($db_dir. "/". $codeID. '/PCMDS.ig', $lines);
 
      //unlink($db_dir. "/". $codeID. '/CMDS.ig');
    }
    else if (strcmp($deleteFileWhenDone, 'y') == 0)
    {
      // Yes, delete cmds.ig
      #echo '<p>Delete '.$codeID.'/CMDS.ig';
      unlink($db_dir. "/". $codeID. '/CMDS.ig');
            
      // rename cmds.ig1 to cmds.ig
      if (file_exists($db_dir. "/". $codeID. '/CMDS.ig1'))
      {
        rename ($db_dir. "/". $codeID. '/CMDS.ig1', $db_dir. "/". $codeID. '/CMDS.ig');
      }
    }
  }
      
  if (strcmp($deleteFileWhenDone, 'y') == 0 && file_exists($db_dir. "/". $codeID. '/PCMDS.ig'))
  {
    if ($debug == 'y')
    {
   	  echo '<br>about to process<br>';
    }

    // alert
    processAlerts($debug, $codeID);

    // remove p-cmds.ig
    unlink($db_dir. "/". $codeID. '/PCMDS.ig');
  }
}


function processAlerts($pDebug, $pCodeID)
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
           $alertMessage = 'iGloo Energy Control: Log Fire is now off';
           $soundFile = 'LogFireNowOff.m4a';
        }
        else
        {
           $alertMessage = 'iGloo Energy Control: Log Fire is now on';
           $soundFile = 'LogFireNowOn.m4a';
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

        if ($stateNotifications == 'y')
        {
          alertClient($pDebug, $currentDeviceToken, $alertMessage, $soundFile, $zoneID, $deviceID);
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
    
function alertClient($pDebug, $deviceToken, $message, $alertSoundFile, $pZoneID, $pDeviceID)
{  	   
  if ($pDebug == 'y')
  {
    echo 'alertClient<br>';
  }
  $passphrase = 'iGlooPush666';

  $ctx = stream_context_create();
  stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
  stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

  // Open a connection to the APNS server
  $fp = stream_socket_client ('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

  if ($fp)
  {
    // Create the payload body
    $body['aps'] = array('alert' => $message, 'sound' => $alertSoundFile, 'zoneID' => $pZoneID, 'deviceID' => $pDeviceID);

    // Encode the payload as JSON
    $payload = json_encode($body);

    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

    // Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));

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
