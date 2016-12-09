<?php

require_once "config.php"

global $db_dir;
global $home_url;

# https://igloohomecontrol.com/iGloo/cgi-bin/timer.php?dc=yHG10301114N&un=n&tv=bb45dbd55478210197d1289a30a9695f99fa73eace35318870a8d41a5d253377&an=name&av=Marco%27s+iPhone&s0=y&s1=y&s2=y

$unsubscribe    = "";
$attributeValue = "";
$attributeName  = "";
$zoneID         = "";
$deviceID       = "";

$dateTimeOn_00     = "";
$dateTimeOn_00     = "";

$deviceCode = '';
$tokenValue = '';

$fileName = 'dd.xml';
    
$subscription0 = ''; // Wind Subscription
$subscription1 = ''; // Rain Subscription
$subscription2 = ''; // Sun Subscription
$subscription3 = ''; // Device State


if (strcmp($_GET['di'], '') !== 0)
{
  $deviceID = $_GET['di'];
}
elseif (strcmp($_POST['di'], '') !== 0)
{
  $deviceID = $_POST['di'];
}

if (strcmp($_GET['zi'], '') !== 0)
{
  $zoneID = $_GET['zi'];
}
elseif (strcmp($_POST['zi'], '') !== 0)
{
  $zoneID = $_POST['zi'];
}


if (strcmp($_GET['dc'], '') !== 0 && strcmp($_GET['tv'], '') !== 0)
{
  $deviceCode = $_GET['dc'];
  $tokenValue = $_GET['tv'];
}
elseif (strcmp($_POST['dc'], '') !== 0 && strcmp($_POST['tv'], '') !== 0)
{
  $deviceCode = $_POST['dc'];
  $tokenValue = $_POST['tv'];
}     

if (strcmp($deviceCode, '') !== 0 && strcmp($tokenValue, '') !== 0)
{
  //echo '$deviceCode'.$deviceCode."<br>";
  //echo '$tokenValue'.$tokenValue."<br>";

  if (strcmp($_GET['s0'], '') !== 0)
    $subscription0 = $_GET['s0'];
  elseif (strcmp($_POST['s0'], '') !== 0)
    $subscription0 = $_POST['s0'];

  if (strcmp($_GET['s1'], '') !== 0)
    $subscription1 = $_GET['s1'];
  elseif (strcmp($_POST['s1'], '') !== 0)
    $subscription1 = $_POST['s1'];

  if (strcmp($_GET['s2'], '') !== 0)
    $subscription2 = $_GET['s2'];
  elseif (strcmp($_POST['s2'], '') !== 0)
    $subscription2 = $_POST['s2'];

  if (strcmp($_GET['s3'], '') !== 0)
    $subscription3 = $_GET['s3'];
  elseif (strcmp($_POST['s3'], '') !== 0)
    $subscription3 = $_POST['s3'];
      
      
  if (strcmp($_GET['un'], '') !== 0)
    $unsubscribe    = $_GET['un'];
  elseif (strcmp($_POST['un'], '') !== 0)
    $unsubscribe    = $_POST['un'];
      
  if (strcmp($_GET['av'], '') !== 0)
    $attributeValue = $_GET['av'];
  elseif (strcmp($_POST['av'], '') !== 0)
    $attributeValue = $_POST['av'];

  if (strcmp($_GET['an'], '') !== 0)
    $attributeName  = $_GET['an'];
  elseif (strcmp($_POST['an'], '') !== 0)
    $attributeName  = $_POST['an'];

  $deviceDirectory = $db_dir. "/". $deviceCode;
  $deviceURL = $deviceDirectory. '/'. $fileName;
  
  if (!file_exists($deviceDirectory)) 
  {
    //echo 'Creating Directory: '.$deviceDirectory."<br>";
    mkdir ($deviceDirectory, 0777, true);
  }

  if (!file_exists($deviceURL)) 
  {
    //echo 'Creating File: '.$deviceURL."<br>";
    file_put_contents ($deviceURL, '<?xml version="1.0"?><deviceData></deviceData>');
  }

  if (!file_exists($deviceURL))
  {
    echo 'Device Data not found'."<br>";
    echo 'Device URL: '.$deviceURL."<br>";
  }
  else
  {
    $currentDeviceData = new SimpleXMLElement($deviceURL, NULL, TRUE);

    processData($currentDeviceData, $tokenValue, $attributeName, $attributeValue, $deviceURL, $unsubscribe, $subscription0, $subscription1, $subscription2, $subscription3, $deviceID, $zoneID);
  }
}


function processData($currentDeviceData, $currentTokenValue, $currentAttributeName, $currentAttributeValue, $currentDeviceURL, $currentUnsubscribe, $pSubscription0, $pSubscription1, $pSubscription2, $pSubscription3, $pDeviceID, $pZoneID)
{
  header('Content-Type: text/xml');

  $path = '//subscriber[@token="'.$currentTokenValue.'"]';
  //$path = '//subscriber[@'.$currentAttributeName.'="'.$currentAttributeValue.'"]';
  //echo 'path: '.$path."<br>";
  $children = $currentDeviceData->xpath($path);
  $child = $children[0];
  $dom=dom_import_simplexml($child);

  if ((string )$child != '' && strtoupper($currentUnsubscribe) == 'Y')
  {
    // $dom=dom_import_simplexml($child);
    $deletedChild = $dom->parentNode->removeChild($dom);
  }
  elseif ((string )$child != '')
  {

    //$dom=dom_import_simplexml($child);
    if (strtoupper($currentAttributeName) == 'NAME')
    {
      $dom->nodeValue = $currentAttributeValue;
    }
    elseif (strcmp($currentAttributeName, '') !== 0 && strcmp($currentAttributeValue, '') !== 0)
    {
      $dom->setAttribute($currentAttributeName, $currentAttributeValue);
    }
    $dom->setAttribute('token', $currentTokenValue);

    //unset($xml –> a –> b –> c); // this would remove node c
    //$data->deleteNodes('//seg[@id="A5"]');
  }
  else
  {
    if (strtoupper($currentAttributeName) == 'NAME')
    {
    $child = $currentDeviceData->addChild('subscriber', $currentAttributeValue);
    }
    else
    {
    $child = $currentDeviceData->addChild('subscriber', 'n/a');
    }


    if (strtoupper($currentAttributeName) != 'NAME' && strcmp($currentAttributeName, '') !== 0 && strcmp($currentAttributeValue, '') !== 0)
    {
      $child->addAttribute($currentAttributeName, $currentAttributeValue);
    }

    $child->addAttribute('token', $currentTokenValue);
    $dom=dom_import_simplexml($child);

  }

  //echo '$pSubscription0: '.$pSubscription0."<br>";
  //echo '$pSubscription1: '.$pSubscription1."<br>";
  //echo '$pSubscription2: '.$pSubscription2."<br>";

  if ($pSubscription0 != '')
  {
    if (strtoupper($pSubscription0) == 'X')
    {
      if ($dom->hasAttribute('wind'))
      {
        $dom->removeAttribute('wind');
      }
    }
    else
    {
      if ($dom->hasAttribute('wind'))
        $dom->setAttribute('wind', $pSubscription0);
      else
        $child->addAttribute('wind', $pSubscription0);
    }
  }

  if ($pSubscription1 != '')
  {
    if (strtoupper($pSubscription1) == 'X')
    {
      if ($dom->hasAttribute('rain'))
      {
        $dom->removeAttribute('rain');
      }
    }
    else
    {
      if ($dom->hasAttribute('rain'))
        $dom->setAttribute('rain', $pSubscription1);
      else
        $child->addAttribute('rain', $pSubscription1);
    }
  }

  if ($pSubscription2 != '')
  {
    if (strtoupper($pSubscription2) == 'X')
    {
      if ($dom->hasAttribute('sun'))
      {
        $dom->removeAttribute('sun');
      }
    }
    else
    {
      if ($dom->hasAttribute('sun'))
        $dom->setAttribute('sun', $pSubscription2);
      else
        $child->addAttribute('sun', $pSubscription2);
    }
  }

  if ($pSubscription3 != '')
  {
    $codeName = 'state';

    if (strtoupper($pSubscription3) == 'X')
    {
      if ($dom->hasAttribute($codeName))
      {
        $dom->removeAttribute($codeName);
      }
    }
    else
    {
      if ($dom->hasAttribute($codeName))
        $dom->setAttribute($codeName, $pSubscription3);
      else
        $child->addAttribute($codeName, $pSubscription3);
    }
  }


  if ($pZoneID != '')
  {
    $zoneIDLabel = 'zoneID';

    if (strtoupper($pZoneID) == 'X')
    {
      if ($dom->hasAttribute($zoneIDLabel))
      {
        $dom->removeAttribute($zoneIDLabel);
      }
    }
    else
    {
      if ($dom->hasAttribute($zoneIDLabel))
        $dom->setAttribute($zoneIDLabel, $pZoneID);
      else
        $child->addAttribute($zoneIDLabel, $pZoneID);
    }
  }


  if ($pDeviceID != '')
  {   
    $deviceIDLabel = 'deviceID';

    if (strtoupper($pDeviceID) == 'X')
    {
      if ($dom->hasAttribute($deviceIDLabel))
      {
        $dom->removeAttribute($deviceIDLabel);
      }
    }
    else
    {
      if ($dom->hasAttribute($deviceIDLabel))
        $dom->setAttribute($deviceIDLabel, $pDeviceID);
      else
        $child->addAttribute($deviceIDLabel, $pDeviceID);
    }
  }

  $currentDeviceData->asXML($currentDeviceURL);

  echo $currentDeviceData->asXML();
}


?>
