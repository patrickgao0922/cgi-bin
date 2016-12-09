<?php

require_once "config.php";

global $db_dir;
global $home_url

# http://igloohomecontrol.com/iGloo/cgi-bin/weather.php?dc=HG10301114N&e=tr

$currentDeviceToken = '3fd0736253e9af9a3ee78577859a5ede64520144f412c7fadc52ada5762f1609';  // iPad 2

$alertMessage = 'iGloo Energy Control: Living Room Blind Closed';

$eventType = '';
$deviceURL = '';
    
if (strcmp($_GET['dc'], '') !== 0)
{
  $deviceURL = $db_dir. "/". $_GET['dc']. '/dd.xml';
}
elseif (strcmp($_POST['dc'], '') !== 0)
{
  $deviceURL = $db_dir. "/". $_POST['dc']. '/dd.xml';
}


if (strcmp($deviceURL, '') !== 0)
{
  if (!file_exists($deviceURL))
  {
    echo 'Device Data not found'."<br>";
    echo 'Device URL: '.$deviceURL."<br>";
  }
  else
  {
    $currentDeviceData = new SimpleXMLElement($deviceURL, NULL, TRUE);
    processWeather($currentDeviceData);
  }
}


function processWeather($deviceData)
{
  if (strcmp($_GET['e'], '') !== 0)
  {
    $eventType = $_GET['e'];
    echo 'Event Type: '.$eventType."<br>";

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
  }

  $state = $deviceData->location->state;
	echo 'State: '.$state."<br>";

  $suburb = $deviceData->location->suburb;
	echo 'Suburb: '.$suburb."<br>";

$latitude = floatval($deviceData->location->latitude);
 	echo 'Latitude: '.$latitude."<br>";

$longitude = floatval($deviceData->location->longitude);
 	echo 'Longitude: '.$longitude."<br>";

  $numberOfSubscriber = $deviceData->subscriber->count();
  echo 'Number of Subscribers: '.$numberOfSubscriber."<br>";

$subscriberName = strval($deviceData->subscriber[0]['name']);
 	echo 'Subscriber: '.$subscriberName."<br>";

  //echo '$currentDeviceToken: '.$currentDeviceToken."<br>";

  switch (strtoupper(substr($state, 0, 3)))
  {
    case 'VIC':
    $weatherURL = 'ftp://ftp2.bom.gov.au/anon/gen/fwo/IDV60920.xml';
    break;

    case 'TAS':
    $weatherURL = 'ftp://ftp2.bom.gov.au/anon/gen/fwo/IDT60920.xml';
    break;

    case 'NOR':
    $weatherURL = 'ftp://ftp2.bom.gov.au/anon/gen/fwo/IDD60920.xml';
    break;

    case 'SOU':
    $weatherURL = 'ftp://ftp2.bom.gov.au/anon/gen/fwo/IDS60920.xml';
    break;

    case 'WES':
    $weatherURL = 'ftp://ftp2.bom.gov.au/anon/gen/fwo/IDW60920.xml';
    break;

    case 'QUE':
    $weatherURL = 'ftp://ftp2.bom.gov.au/anon/gen/fwo/IDQ60920.xml';
    break;

    case 'NSW':
    $weatherURL = 'ftp://ftp2.bom.gov.au/anon/gen/fwo/IDN60920.xml';
    break;

    default:
    $weatherURL = 'nil';
  }


  if (!file_exists($weatherURL))
  {
    echo "Could not load the weather from '".$weatherURL."'";
  }
  else
  {	
    $nearestDistance = 1000000.0;
    $stationID = 'Not Found';

    $weather = new SimpleXMLElement($weatherURL, NULL, TRUE);

    $timeIssued = $weather->amoc->{'issue-time-local'};
    echo 'Time Issued: '.$timeIssued."<br>";
	      
    $stationIndex = -1;
    $index = 0;

    foreach ($weather->observations->station as $station) 
    {
      $stationID = $station['wmo-id'];  
      $stationLatitude  = floatval($station['lat']);
      $stationLongitude = floatval($station['lon']);

      $distance = distance ($stationLatitude, $stationLongitude, $latitude, $longitude, "K");

      if (floatval($nearestDistance) > floatval($distance)) 
      {
        $nearestDistance = $distance;
        $closestStationID = $stationID;
        	          
        $stationIndex = $index;
      }
      
      $index++;
    }
	      
    if ($stationIndex > -1)
    {
      //echo '--Found Station: '.$weather->observations->station[$stationIndex]['wmo-id']."<br>";
    }
	
	  foreach ($weather->observations->station as $station) 
	  {
      $stationID = $station['wmo-id'];  
      $stationLatitude  = floatval($station['lat']);
      $stationLongitude = floatval($station['lon']);
	   
	    if ($stationID == $closestStationID)  
	    { 	   
        #echo 'Place: '.$station['stn-name'],"<br>";
        #echo 'Lat: '.$station['lat'].'  Lon: '.$station['lon'], "<br>";
        #echo "Distance from weather station: ".$distance." km<br>";

        echo $station['stn-name'].'  Distance: '.(intval($nearestDistance * 100.0) / 100.0).' km  Lat: '.$stationLatitude.'  Lon: '.$stationLongitude,"<br>";

        $cloud            = $station->period->level->xpath('element[@type="cloud"]')[0];
        $air_temperature  = $station->period->level->xpath('element[@type="air_temperature"]')[0];
        $wind_spd_kmh     = $station->period->level->xpath('element[@type="wind_spd_kmh"]')[0];
        $wind_dir         = $station->period->level->xpath('element[@type="wind_dir"]')[0];
        $gust_kmh         = $station->period->level->xpath('element[@type="gust_kmh"]')[0];
        $maximum_gust_kmh = $station->period->level->xpath('element[@type="maximum_gust_kmh"]')[0];
        $maximum_gust_dir = $station->period->level->xpath('element[@type="maximum_gust_dir"]')[0];
        $rainfall         = $station->period->level->xpath('element[@type="rainfall"]')[0];
        $rain_ten         = $station->period->level->xpath('element[@type="rain_ten"]')[0];
        $rain_hour        = $station->period->level->xpath('element[@type="rain_hour"]')[0];
        $vis_km           = $station->period->level->xpath('element[@type="vis_km"]')[0];


        if (1)
        {
          if (strcmp($cloud,            '') !== 0) echo '$cloud: '           .$cloud."<br>";
          if (strcmp($air_temperature,  '') !== 0) echo '$air_temperature: ' .$air_temperature."<br>";
          if (strcmp($wind_spd_kmh,     '') !== 0) echo '$wind_spd_kmh: '    .$wind_spd_kmh."<br>";
          if (strcmp($wind_dir,         '') !== 0) echo '$wind_dir: '        .$wind_dir."<br>";
          if (strcmp($gust_kmh,         '') !== 0) echo '$gust_kmh: '        .$gust_kmh."<br>";
          if (strcmp($maximum_gust_kmh, '') !== 0) echo '$maximum_gust_kmh: '.$maximum_gust_kmh."<br>";
          if (strcmp($maximum_gust_dir, '') !== 0) echo '$maximum_gust_dir: '.$maximum_gust_dir."<br>";
          if (strcmp($rainfall,         '') !== 0) echo '$rainfall: '        .$rainfall."<br>";
          if (strcmp($rain_ten,         '') !== 0) echo '$rain_ten: '        .$rain_ten."<br>";
          if (strcmp($rain_hour,        '') !== 0) echo '$rain_hour: '       .$rain_hour."<br>";
          if (strcmp($vis_km ,          '') !== 0) echo '$vis_km : '         .$vis_km ."<br>";
	   	  }
	   	   
	   	  if (strcmp($rain_ten, '') !== 0 || 1)
	   	  {
          //$alertMessage = 'iGloo Energy Control: Living Room Blind Closed';
          echo 'Alert Message: '.$alertMessage.$element."<br>";

          foreach ($deviceData->subscriber as $subscriber)
          {
            $currentDeviceToken = $subscriber->token;
            //echo '$token: '.$currentDeviceToken."<br>";
            alertClient($currentDeviceToken, $alertMessage, $soundFile);
          }
        }


        if (0)
        {
          foreach ($station->period->level->element as $element)
          {
            switch((string) $element['type']) 
            {
              case 'cloud':
              echo 'Cloud Cover: '.$element."<br>";
              break;

              case 'air_temperature':
              echo 'Air Temperature: '.$element." Deg C<br>";
              break;

              case 'wind_spd_kmh':
              echo 'Wind Speed: '.$element." kmh<br>";
              break;

              case 'wind_dir':
              echo 'Wind Direction: '.$element."<br>";
              break;

              case 'gust_kmh':
              echo 'Gust: '.$element." kmh<br>";
              break;

              case 'maximum_gust_kmh':
              echo 'Maximum Gust: '.$element." kmh<br>";
              break;

              case 'maximum_gust_dir':
              echo 'Maximum Gust Direction: '.$element."<br>";
              break;

              case 'rainfall':
              echo 'Rainfall: '.$element."<br>";
              break;

              case 'rain_ten':
              echo 'Rain ten: '.$element."<br>";
              break;

              case 'rain_hour':
              echo 'Rain hour: '.$element."<br>";
              break;

              case 'vis_km':
              echo 'Visibility: '.$element.' km'."<br>";
              break;

              default:
              #echo '-'.$element['type'],': '.$element."<br>";
              break;
    		    }	
	        }
	      }
	    }
	  }
	    
    echo "<br>";
  }
}
        
    
//alertClient($currentDeviceToken, $alertMessage);


function distance($lat1, $lon1, $lat2, $lon2, $unit) 
{
  $theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);
	 
	if ($unit == "K")
	{
	  return ($miles * 1.609344);
	} 
	else if ($unit == "N")
	{
	  return ($miles * 0.8684);
	}
	else
	{
    return $miles;
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


function alertClient($deviceToken, $message, $alertSoundFile)
{
  $passphrase = 'iGlooPush666';

  $ctx = stream_context_create();
  stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
  stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

  // Open a connection to the APNS server
  $fp = stream_socket_client ('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

  if (!$fp)
	  exit("Failed to connect: $err $errstr" . PHP_EOL);
    
  echo 'Connected to APNS' . PHP_EOL;

  // Create the payload body
  $body['aps'] = array('alert' => $message, 'sound' => $alertSoundFile, 'zoneID' => 'xxxx', 'deviceID' => 'yyyy');
  //$body['aps'] = array('alert' => $message, 'sound' => 'Notification_David.m4a', 'zoneID' => 'xxxx', 'deviceID' => 'yyyy');
  //$body['aps'] = array('alert' => $message, 'sound' => 'notification.m4a', 'zoneID' => 'xxxx', 'deviceID' => 'yyyy');
  //$body['aps'] = array('alert' => $message, 'sound' => 'iGloo Jingle.m4a', 'zoneID' => 'xxxx', 'deviceID' => 'yyyy');
  //$body['aps'] = array('alert' => $message, 'sound' => 'iGloo Jingle.m4a');

  //$body['aps'] = array('alert' => $message, 'sound' => 'default');
        
	echo 'alert: '.$message."<br>";
	echo 'sound: '.$alertSoundFile."<br>";

  // Encode the payload as JSON
  $payload = json_encode($body);

  // Build the binary notification
  $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

  // Send it to the server
  $result = fwrite($fp, $msg, strlen($msg));

  if (!$result)
    echo 'Message not delivered:'. $deviceToken . PHP_EOL."<br>";
  else
    echo 'Message successfully delivered: '.$deviceToken . PHP_EOL."<br>";

  echo "Result: ".$result."<br>";

  /*
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
*/

  // Close the connection to the server
  fclose($fp);
}

?>
