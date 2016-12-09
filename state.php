<?php

require_once "config.php";

global $db_dir;
global $home_url;

$debug = '';

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


if (strcmp($codeID, '') !== 0)
{
  if (!file_exists($db_dir. "/". $codeID. '/DState.ds')) 
  {
    echo '';
  }
  else
  {
    $deviceState = file_get_contents("http://". $home_url. "/iGloo/database/". $codeID. '/DState.ds');

    $commandInProgress = file_exists($db_dir. "/". $codeID. '/CMDS.ig'); 

    echo $deviceState.'\t'.$commandInProgress;
  }
}

?>
