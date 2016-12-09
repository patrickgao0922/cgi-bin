<?php
  require_once('connect.php');
	
  // Basically, it inserts into the map server, which we don't use it.
	/*Do insertion here*/

  // thermostat parameters
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
  // serial
	$entry = $_GET['entry'];
	$db_SERIAL = $_GET['serial'];
	
  // serial number
	if ($db_SERIAL === 'Serial Number')
	{
		$db_SERIAL = 'No Entry';
	}

  // latitude
	$db_LATITUDE = floatval($_GET['lat']);

  // longtitude
	$db_LONGITUDE = floatval($_GET['lon']);

  // suburb
	$db_SUBURB = $_GET['suburb'];

  // db install date
	$db_INSTALL_DATE = '2015-01-23';//$_GET['installed_date'];
	
  // chip id
	$chip_id = $_GET['chip_id'];

  // db thermostat
	$db_TML = '';

  // db time
	$db_TIME = '';
	
	//$dealer ='Specialized A/C';
	//$heater_name='Bonaire MB3';

  // dealer
	$dealer ='Jetmaster';

  // heater name
	$heater_name='Heat&Glo';
	
	//PWO_NOTEM030MIN009MAX035THM_NOFLM000FAN000AU1000AU2000CLD_NOHOO1234567TSTENAU3112014126PMTMLOff	Serial Number	Brunswick West	-37.76171112	144.94235229

  // power on or not
	$powerOnStr = substr($entry,3,3);
	if ($powerOnStr === '_NO')
		$db_PWR = 0;
	else
		$db_PWR = 1;
	
  // what is the temperature.
	$tmpOnStr = substr($entry,9,3);
	$db_TMP = floatval($tmpOnStr);
	
  // min temp
	$minimumtempOnStr = substr($entry,15,3);
	$db_MIN = floatval($minimumtempOnStr);
	
  // max temp
	$maximumtempOnStr = substr($entry,21,3);
	$db_MAX = floatval($maximumtempOnStr);
	
  // thermostat
	$thermostatOnStr = substr($entry,27,3);
	if ($thermostatOnStr === '_NO')
		$db_THM = 0;
	else
		$db_THM = 1;
	
  // flame level
	$flameLevelOnStr = substr($entry,33,3);
	$db_FLM = floatval($flameLevelOnStr);
	
  // fan value
	$fanValueOnStr = substr($entry,39,3);
	$db_FAN = floatval($fanValueOnStr);
	
  // aux 1
	$aux1ValueOnStr = substr($entry, 45,3);
	$db_AU1 = floatval($aux1ValueOnStr);
	
  // aux 2
	$aux2ValueOnStr = substr($entry, 51,3);
	$db_AU2 = floatval($aux2ValueOnStr);
	
  // child lock
	$childLockOnStr = substr($entry, 57,3);
	if ($childLockOnStr === '_NO')
		$db_CLD = 0;
	else
		$db_CLD = 1;
	
  // hour to operation
	$hoursofoperationOnStr = substr($entry,63,7);
	$db_HOO = floatval($hoursofoperationOnStr);
	
  // timestap
	$timestampOnStr = substr($entry, 73,18);

  // language
	$db_LANGUAGE = substr($timestampOnStr,0,2);

  // country
	$db_COUNTRY = substr($timestampOnStr,2,2);
	
  // day, month, year
	$day = substr($timestampOnStr,4,2);
	$month = substr($timestampOnStr,6,2);
	$year = substr($timestampOnStr,8,4);
	
	$date = $year."-".$month."-".$day;
	
  // hour
	$hour = intval(substr($timestampOnStr,12,2));

  // min
	$minute = substr($timestampOnStr,14,2);

  // am or pm
	$meridians = substr($timestampOnStr,16,2);

	if ($meridians === 'PM')
		$hour = $hour + 12;
	
  // hour	
	$hours = $hour.":".$minute.":00";
	
  // db time
	$db_TIME = $date." ".$hours;
	
  // db thermostat
	$db_TML = substr($entry,94);
	
	/*$sqlquery = "INSERT INTO state_record (serial_number,latitude,longitude,suburb,language,
				country,timestamp,PWO,TEM,MIN,MAX,THM,FLM,FAN,AU1,AU2,CLD,HOO,TML,
				heater_name) VALUES ('"
				.$db_SERIAL."',".$db_LATITUDE.",".$db_LONGITUDE.",'".$db_SUBURB."','".$db_LANGUAGE
				."','".$db_COUNTRY."','".$db_TIME."',".$db_PWR.",".$db_TMP.",".$db_MIN.",".$db_MAX
				.",".$db_THM.",".$db_FLM.",".$db_FAN.",".$db_AU1.",".$db_AU2.",".$db_CLD.",".$db_HOO
				.",'".$db_TML."','".$heater_name."');";
	*/
	
  // insert into state_record
	$db_TIME = '2015-01-24 21:51:00';
	$sqlquery = "INSERT INTO state_record (serial_number,latitude,longitude,suburb,language,
				country,timestamp,PWO,TEM,MIN,MAX,THM,FLM,FAN,AU1,AU2,CLD,HOO,TML,
				heater_name,dealer,installed_date,chip_id,zone_id) VALUES ('"
				.$db_SERIAL."',".$db_LATITUDE.",".$db_LONGITUDE.",'".$db_SUBURB."','".$db_LANGUAGE
				."','".$db_COUNTRY."',NOW(),".$db_PWR.",".$db_TMP.",".$db_MIN.",".$db_MAX
				.",".$db_THM.",".$db_FLM.",".$db_FAN.",".$db_AU1.",".$db_AU2.",".$db_CLD.",".$db_HOO
				.",'".$db_TML."','".$heater_name."','".$dealer."','".$db_INSTALL_DATE."','".$chip_id."','".$chip_id."');";
			
	/*
	$sqlquery = "INSERT INTO state_record (serial_number,latitude,longitude,suburb,language,
				country,timestamp,PWO,TEM,MIN,MAX,THM,FLM,FAN,AU1,AU2,CLD,HOO,TML,
				heater_name,dealer) VALUES ('"
				.$db_SERIAL."',".$db_LATITUDE.",".$db_LONGITUDE.",'".$db_SUBURB."','".$db_LANGUAGE
				."','".$db_COUNTRY."','".$db_TIME."',".$db_PWR.",".$db_TMP.",".$db_MIN.",".$db_MAX
				.",".$db_THM.",".$db_FLM.",".$db_FAN.",".$db_AU1.",".$db_AU2.",".$db_CLD.",".$db_HOO
				.",'".$db_TML."','".$heater_name."','".$dealer."');";
	*/
		
	//echo $sqlquery;
					
  // run query		
	if ($connect->query($sqlquery) === TRUE) 
	{
		//echo "New record created successfully";
	} 
	else 
	{
		echo "Error: " . $sqlquery . "<br>" . $connect->error;
	}
	
	// close mysql connection
	$connect->close();
?>
