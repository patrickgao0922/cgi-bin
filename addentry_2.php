<?php
	require_once('connect.php');
	
  // Similar to addentry.php, but no db insertion.
	/*Do insertion here*/

	$db_PWR = '';
	$db_TMP = '';
	$db_MIN = '';
	$db_MAX = '';
	$db_THM = '';
	$db_FLM = '';
	$db_FAN = '';
	$db_AU1 = '';
	$db_AU2 = '';
	$db_CLD = '';
	$db_HOO = '';
	$db_LANGUAGE = '';
	$db_COUNTRY = '';

	// entry
	$entry = $_GET['entry'];

  // serial
	$db_SERIAL = $_GET['serial'];
	
  // if db_serial == "serial number"
	if ($db_SERIAL === 'Serial Number')
	{
		$db_SERIAL = 'No Entry';
	}

  // lat
	$db_LATITUDE = floatval($_GET['lat']);

  // lon
	$db_LONGITUDE = floatval($_GET['lon']);

  // sub
	$db_SUBURB = $_GET['suburb'];

  // date
	$db_INSTALL_DATE = '2015-01-23';//$_GET['installed_date'];
	
  // chip id
	$chip_id = $_GET['chip_id'];

  // db tml
	$db_TML = '';

  // db time
	$db_TIME = '';
	
	//$dealer ='Specialized A/C';
	//$heater_name='Bonaire MB3';

  // dealer
	$dealer ='Jetmaster';

  // heat and glo
	$heater_name='Heat&Glo';


	//PWO_NOTEM030MIN009MAX035THM_NOFLM000FAN000AU1000AU2000CLD_NOHOO1234567TSTENAU3112014126PMTMLOff	Serial Number	Brunswick West	-37.76171112	144.94235229

  /*
    PWO_NO
    TEM030
    MIN009
    MAX035
    
    THM_NO
    FLM000
    FAN000
    AU1000
    AU2000
    
    CLD_NO
    HOO1234567
    TSTENAU3112014126PMTMLOff

    Serial Number
    Brunswick West
    -37.76171112
    144.94235229
    

  */

  // PWO_NO
	$powerOnStr = substr($entry,3,3);
	if ($powerOnStr === '_NO')
		$db_PWR = 0;
	else
		$db_PWR = 1;
	
  // TEM030
	$tmpOnStr = substr($entry,9,3);
	$db_TMP = floatval($tmpOnStr);
	
  // MIN009
	$minimumtempOnStr = substr($entry,15,3);
	$db_MIN = floatval($minimumtempOnStr);
	
  // MAX035
	$maximumtempOnStr = substr($entry,21,3);
	$db_MAX = floatval($maximumtempOnStr);
	
  // THM_NO
	$thermostatOnStr = substr($entry,27,3);
	if ($thermostatOnStr === '_NO')
		$db_THM = 0;
	else
		$db_THM = 1;
	
  // FLM000
	$flameLevelOnStr = substr($entry,33,3);
	$db_FLM = floatval($flameLevelOnStr);
	
  // FAN000
	$fanValueOnStr = substr($entry,39,3);
	$db_FAN = floatval($fanValueOnStr);
	
  // AU1000
	$aux1ValueOnStr = substr($entry, 45,3);
	$db_AU1 = floatval($aux1ValueOnStr);
	
  // AU2000
	$aux2ValueOnStr = substr($entry, 51,3);
	$db_AU2 = floatval($aux2ValueOnStr);
	
  // CLD_NO
	$childLockOnStr = substr($entry, 57,3);
	if ($childLockOnStr === '_NO')
		$db_CLD = 0;
	else
		$db_CLD = 1;
	
  // HOO1234567
	$hoursofoperationOnStr = substr($entry,63,7);
	$db_HOO = floatval($hoursofoperationOnStr);

  // TSTENAU3112014126PMTMLOff
  // no TST?
	$timestampOnStr = substr($entry, 73,18);

  // EN
	$db_LANGUAGE = substr($timestampOnStr,0,2);

  // AU
	$db_COUNTRY = substr($timestampOnStr,2,2);
	
  // 3
	$day = substr($timestampOnStr,4,2);

  // 11
	$month = substr($timestampOnStr,6,2);

  // 2014
	$year = substr($timestampOnStr,8,4);
	
  // 2014-11-03
	$date = $year."-".$month."-".$day;
	
  // 12
	$hour = intval(substr($timestampOnStr,12,2));

  // 6
	$minute = substr($timestampOnStr,14,2);

  // PM
	$meridians = substr($timestampOnStr,16,2);

	if ($meridians === 'PM')
		$hour = $hour + 12;
		
	$hours = $hour.":".$minute.":00";
	
	$db_TIME = $date." ".$hours;
	
  // TML
	$db_TML = substr($entry,94);
	

	$db_TIME = '2015-01-24 21:51:00';


  $fn1  = "";
  if (strcmp($_POST['fn1'], '') !== 0)
  {
      $fn1 = $_POST['fn1'];
  }
  elseif (strcmp($_GET['fn1'], '') !== 0)
  {
      $fn1 = $_GET['fn1'];
  }

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

/*
	$sqlquery = "INSERT INTO state_record (serial_number,latitude,longitude,suburb,language,
				country,timestamp,PWO,TEM,MIN,MAX,THM,FLM,FAN,AU1,AU2,CLD,HOO,TML,
				heater_name,dealer,installed_date,chip_id,zone_id) VALUES ('"
				.$db_SERIAL."',".$db_LATITUDE.",".$db_LONGITUDE.",'".$db_SUBURB."','".$db_LANGUAGE
				."','".$db_COUNTRY."',NOW(),".$db_PWR.",".$db_TMP.",".$db_MIN.",".$db_MAX
				.",".$db_THM.",".$db_FLM.",".$db_FAN.",".$db_AU1.",".$db_AU2.",".$db_CLD.",".$db_HOO
				.",'".$db_TML."','".$heater_name."','".$dealer."','".$db_INSTALL_DATE."','".$chip_id."','".$chip_id."');";
*/
			
			
/*	
	$sqlquery = "INSERT INTO state_record (serial_number,latitude,longitude,suburb,language,
				country,timestamp,PWO,TEM,MIN,MAX,THM,FLM,FAN,AU1,AU2,CLD,HOO,TML,
				heater_name,dealer,installed_date,chip_id,zone_id) VALUES ('"
				.$fv1."',".$fv2.",".$fv3.",'".$fv4."','".$fv5."','".$fv6."','".$fv7."','".$fv8."','".$fv9.","
				.$fv10."',".$fv11.",".$fv12.",'".$fv13."','".$fv14."','".$fv15."',$fv16,".$fv17.",".$fv18.",".$fv19."',"
				.$fv20."',".$fv21.",".$fv22.",'".$fv23."','".$fv24."','".$fv25."',$fv26,".$fv27.",".$fv28.",".$fv29."',"
				.$fv30."',".$fv31.",".$fv32.",'".$fv33."','".$fv34."');";
*/


	// insert into state record
	$sqlquery = "INSERT INTO state_record ("
				.$fn2.",".$fn3.",".$fn4.",".$fn5.",".$fn6.",".$fn7.",".$fn8.",".$fn9.","
				.$fn10.",".$fn11.",".$fn12.",".$fn13.",".$fn14.",".$fn15.",".$fn16.",".$fn17.",".$fn18.",".$fn19.","
				.$fn20.",".$fn21.",".$fn22.",".$fn23.",".$fn24.",".$fn25.",".$fn26.",".$fn27.",".$fn28.",".$fn29.","
				.$fn30.",".$fn32.",".$fn34.") VALUES ('"
				.$fv2."','".$fv3."','".$fv4."','".$fv5."',".$fv6.",".$fv7.",'".$fv8."','".$fv9."','"
				.$fv10."','".$fv11."',".$fv12.",".$fv13.",".$fv14.",".$fv15.",".$fv16.",".$fv17.",".$fv18.",".$fv19.","
				.$fv20.",".$fv21.",".$fv22.",'".$fv23."','".$fv24."','".$fv25."','".$fv26."','".$fv27."','".$fv28."','".$fv29."','"
				.$fv30."',".$fv32.",'".$fv34."');";
				
	//echo $sqlquery;
	
	//echo "New DB Query-->> ='".$sqlquery."'<br>";  .$fn31."," $fv31.","  ",".$fv33.  ",".$fn33.   .$fn1.","  .$fv1."','"
	
  // print something
	if (0)
	{
	   echo "<br><br>$fv34: [[".$fn34."]]<br><br>";
	   echo "<br><br>$fv34: [[".$fv34."]]<br><br>";
	   echo "<br>=======End=======<br>";
	   echo "<br><br>".$sqlquery."<br><br>";
	   echo "<br>=======End=======<br>";
	}
		
  // actual query					
	if ($connect->query($sqlquery) === TRUE) 
	{
		//echo "New record created successfully";
	} 
	else 
	{
		echo "Error: " . $sqlquery . "<br>" . $connect->error;
	}
	
  // why not close?????
	//$connect->close();
?>
