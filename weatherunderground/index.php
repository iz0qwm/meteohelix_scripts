<?php


	############################################################################
	# 	
	#  Script to send data to Wunderground	
	# 
	#
	############################################################################
	#
	#	Script for Meteohelix IoT Pro Weather station by Raffaello Di Martino 
	#
	############################################################################
	#	Version and change log
	#
	# 	v1.0 - Feb 02, 2020
	# 		- initial release	
	#   v1.1 - Feb 03, 202
	#		- accumulated rain
	#   v2.0 - Feb 04, 2020
	#		- simple UV calculation	
	############################################################################
	
	# Create e new weather station on Wunderground. Keep note of Station_ID and Station_Key
	# Configure this script with your Wunderground Station_ID and Station_Key
	# To configure the baromhpacorrection you need to know the raw data in hPa of your Meteohelix, then add the correction to reach the value on sea-level
	# Put this script in a directory of you website i.e. http://mysite/wu/index.php
	# Configure a Forwarder on weather.allmeteo.com - HTTP/Post - that points to this script 
	
	########### GET DATA ############
	
	# Settings: General
	$forward_data_wu = 1;
	$forward_server = "weatherstation.wunderground.com/weatherstation/updateweatherstation.php";
	$wu_id = "XXXXXX";
	$wu_station_key = "********";
	$baromhpacorrection = 0;  # Correction for pressure to sea-level
	
	# Convert HTTP POST variables to json
	#$weather_data = $_POST;
	$weather_data = json_decode(file_get_contents('php://input'), true);
	$weather_data_forward = $_GET;
	
	# Conversion factors
	$f_mph_kmh = 1.60934;
	$f_mph_kts = 0.868976;
	$f_mph_ms = 0.44704;
	$f_in_hpa = 33.86;
	$f_in_mm = 25.4;
	$pa_in_hpa = 0.01;
	$mm_in_f = 0.03937008;
	
	# Reading accumulated rain file
	$read_acc_rain = "./accumulated_rain.txt";
	if (!file_exists($read_acc_rain)) {	
		$result_acc_rain = 0;
	} else { 
		$file = fopen($read_acc_rain, 'r');
		#while (!feof($file)){ 
			$result_acc_rain = fgets($file);
			$result_acc_rain = round((double)$result_acc_rain, 2);
		#}
		fclose($file);
	}
	# Convert data
    # Temps
    @$weather_data['tempc'] = round( $weather_data['temperature'] - 273.15, 2 );
    @$weather_data['dewptc'] = round( $weather_data['dewPoint'] - 273.15, 2 );
    @$weather_data['tempf'] = round( ($weather_data['tempc'] * 1.8) + 32, 2 ); 
	@$weather_data['dewptf'] = round( ($weather_data['dewptc'] * 1.8) + 32, 2 );
    
    # Distances
	@$weather_data['dailyrainmm'] = $weather_data['rain'] + $result_acc_rain ;
	@$weather_data['dailyrainin'] = $weather_data['dailyrainmm'] * $mm_in_f ;
    #@$weather_data['rainmm'] = round( $weather_data['rainin'] * $f_in_mm, 2 );
    #@$weather_data['dailyrainmm'] = round( $weather_data['dailyrainin'] * $f_in_mm, 2 );
    #@$weather_data['weeklyrainmm'] = round( $weather_data['weeklyrainin'] * $f_in_mm, 2 );
    #@$weather_data['monthlyrainmm'] = round( $weather_data['monthlyrainin'] * $f_in_mm, 2 );
    #@$weather_data['yearlyrainmm'] = round( $weather_data['yearlyrainin'] * $f_in_mm, 2 );
    #@$weather_data['rainratemm'] = round( $weather_data['rainratein'] * $f_in_mm, 2 );
    
    # Baros
    @$weather_data['baromabshpa'] = round( $weather_data['pressure'] * $pa_in_hpa, 2 );
	@$weather_data['baromrelhpa'] = round( $weather_data['baromabshpa'] + $baromhpacorrection, 2);
	@$weather_data['baromrelf'] = round( $weather_data['baromrelhpa'] / $f_in_hpa, 2 );
    #@$weather_data['baromrelhpa'] = round( $weather_data['baromrelin'] * $f_in_hpa, 2 );
    
	# UV simple calculation
	if ( $weather_data['irradiation'] < 70 ) 
	{
		$weather_data['uv'] = 0;
	} elseif ( $weather_data['irradiation'] >= 70 && $weather_data['irradiation'] < 440 ) {
		$weather_data['uv'] = 1;
		if ( $weather_data['tempc'] > 28 ) {
			$weather_data['uv'] = 2;
		}
	} elseif ( $weather_data['irradiation'] >= 440 && $weather_data['irradiation'] < 600 ) {
		$weather_data['uv'] = 2;
		if ( $weather_data['tempc'] > 28 ) {
			$weather_data['uv'] = 3;
		}
	} elseif ( $weather_data['irradiation'] >= 600 && $weather_data['irradiation'] < 800 ) {
		$weather_data['uv'] = 3;
		if ( $weather_data['tempc'] > 28 ) {
			$weather_data['uv'] = 4;
		}
	} elseif ( $weather_data['irradiation'] >= 800 && $weather_data['irradiation'] < 1000 ) {	
		$weather_data['uv'] = 4;
		if ( $weather_data['tempc'] > 30 ) {
			$weather_data['uv'] = 6;
		} elseif ( $weather_data['tempc'] > 28 ) {
			$weather_data['uv'] = 4;
		}	
	} elseif ( $weather_data['irradiation'] >= 1000 ) {	
		$weather_data['uv'] = 5;
		if ( $weather_data['tempc'] > 30 ) {
			$weather_data['uv'] = 7;
		} elseif ( $weather_data['tempc'] > 28 ) {
			$weather_data['uv'] = 6;
		}
	}	
	
    # Date and time
	#$weather_data['dateutc'] = gmdate("Y-m-d\TH:i:s\Z");
    #$weather_data['dayutc'] = gmdate("Y-m-d\T");
	#$weather_data['hoursutc'] = gmdate("H");
	#$weather_data['minutesutc'] = gmdate("i\Z");
	$time = new DateTime('NOW');
	$h=$time->format('H');
	$m=$time->format('i');
	#$weather_data['secondsutc'] = gmdate("s\Z");
	#$weather_data['dateutc'] = $weather_data['dayutc'] . "+" . $weather_data['hoursutc'] . "%3A" . $weather_data['minutesutc'] . "%3A" . $weather_data['secondsutc'];
	#  https://weatherstation.wunderground.com/weatherstation/updateweatherstation.php?ID=ISCAND6&PASSWORD=kgFHu4aa&dateutc=now&tempf=70&baromin=29.1&dewptf=68.2&humidity=90&softwaretype=meteohelix%20version01&action=updateraw
	
	# Forward data to meteotemplate server
	if ( $forward_data_wu == 1 ) 
	{
		@$weather_data_forward['dateutc'] = "now";
		@$weather_data_forward['wu_id'] = $wu_id;
		@$weather_data_forward['wu_station_key'] = $wu_station_key;
		@$weather_data_forward['softwaretype'] = "meteohelix%20version01" ;
		@$weather_data_forward['action'] = "updateraw" ;
		@$weather_data_forward['tempf'] = $weather_data['tempf'] ;
		@$weather_data_forward['humidity'] = $weather_data['humidity'] ;
		@$weather_data_forward['baromin'] = $weather_data['baromrelf'] ;
		@$weather_data_forward['dewptf'] = $weather_data['dewptf'] ;
		@$weather_data_forward['solarradiation'] = $weather_data['irradiation'] ;
		@$weather_data_forward['dailyrainin'] = $weather_data['dailyrainin'] ;
		@$weather_data_forward['UV'] = $weather_data['uv'] ;
		
		#@$weather_data['forward_url'] = "http://" . $forward_server . $_SERVER[REQUEST_URI];
		@$weather_data_forward['forward_url'] = "http://" . $forward_server ;
		@$weather_data_forward['forward'] = file_get_contents($weather_data_forward['forward_url'] . "?" . "ID=" . @$weather_data_forward['wu_id'] . "&PASSWORD=" . @$weather_data_forward['wu_station_key'] . "&dateutc=" . @$weather_data_forward['dateutc'] . "&tempf=" . @$weather_data_forward['tempf'] . "&baromin=" . @$weather_data_forward['baromin'] ."&humidity=" . @$weather_data_forward['humidity'] . "&dewptf=" . @$weather_data_forward['dewptf'] . "&solarradiation=" . @$weather_data_forward['solarradiation'] . "&UV=" . @$weather_data_forward['UV'] . "&dailyrainin=" . @$weather_data_forward['dailyrainin'] . "&softwaretype=" . @$weather_data_forward['softwaretype'] . "&action=" . @$weather_data_forward['action'] );
	}
	
	#
	# Writing accumulated rain file
	#
	$write_acc_rain = "./accumulated_rain.txt";
    $file = fopen($write_acc_rain, 'w');
	$stringa = round($weather_data['dailyrainmm'], 2) . "\n";
    fwrite($file, $stringa);
    fclose($file);
	
	#echo $h;
	#echo "\n";
	#echo $m;
	# Check if midnight =  reset file
	if( $h == 23 && $m >= 55 && $m <= 59 || $h == 0 && $m >= 0 && $m <= 5) {  
		print("Delete rain file \n");
		$write_acc_rain = "./accumulated_rain.txt";
		$file = fopen($write_acc_rain, 'w');
		$stringa = "0\n";
		fwrite($file, $stringa);
		fclose($file);
	}
	


	print("Success. Update done\n");
?>
