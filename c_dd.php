<?php

require_once "config.php";

// like c.php, with delete
// igloohomecontrol.com/iGloo/cgi-bin/c_dd.php?c=BC10050146&et=TR
    
$debug     = '';
$zoneID    = "";
$deviceID  = "";
    
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
    
    
// event type, what event    
if (strcmp($_POST['et'], '') !== 0)
{
  $eventType = $_POST['et'];
}
elseif (strcmp($_GET['et'], '') !== 0)
{
  $eventType = $_GET['et'];
}
else
{
  $eventType = "";
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

// delete file    
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
  processAlerts($debug, $codeID);
}


function processAlerts($pDebug, $pCodeID)
{
  global $db_dir;

  $deviceURL = $db_dir. "/". $pCodeID. '/dd.xml';

  if (file_exists($deviceURL))
  {
    $deviceData = new SimpleXMLElement($deviceURL, NULL, TRUE);

    if ($pDebug == 'y')
    {
   	  echo '<br>'.$deviceData.'<br>';
    }

    switch (strtoupper(substr($eventType, 0, 2)))
    {
      case 'RM':
	      $alertMessage = 'Ring Marco, Please!';
	      $soundFile = 'Notification-RingMarco.m4a';
        break;

      case 'TR':
	   	  $alertMessage = 'Rain Trigger: Living Room Blind Closed';
	      $soundFile = 'Notification-RainTrigger.m4a';
        break;

      case 'TW':
        $alertMessage = 'Wind Trigger: Living Room Blind Closed';
        $soundFile = 'Notification-WindTrigger.m4a';
        break;

      case 'TG':
        $alertMessage = 'Weather Trigger: Living Room Blind Closed';
        $soundFile = 'Notification-WeatherTrigger.m4a';
        break;

      case 'TS':
        $alertMessage = 'Sun Trigger: Living Room Blind Closed';
        $soundFile = 'Notification-SunTrigger.m4a';
        break;

      case 'DA':
        $alertMessage = 'Energy Control: Living Room Blind Closed';
        $soundFile = 'Notification_David.m4a';
        break;

      default:
        $alertMessage = 'Weather Trigger: Living Room Blind Closed';
        $soundFile = 'Notification-WeatherTrigger.m4a';
    }

    if ($deviceData)
    {
      foreach ($deviceData->subscriber as $subscriber)
	    {
	      $currentDeviceToken = $subscriber[0]['token'];
	      $stateNotifications = $subscriber[0]['state'];

	      $zoneID = $subscriber[0]['zoneID'];
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
