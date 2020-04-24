	
# index.php to Forward data to Wunderground
This script receives the data coming from a Forwarder configured on the weather.allmeteo.com website.
 
* Create e new weather station on Wunderground. Keep note of Station_ID and Station_Key
* Configure this script with your Wunderground Station_ID and Station_Key
* To configure the baromhpacorrection you need to know the raw data in hPa of your Meteohelix, then add the correction to reach the value on sea-level
* Put this script in a directory of you website i.e. http://mysite/wu/index.php
* Configure a Forwarder on weather.allmeteo.com - HTTP/Post - that points to this script. Record must be Default
	
## Parameters to set
```
  # Settings: General
	
  $forward_server = "weatherstation.wunderground.com/weatherstation/updateweatherstation.php";
  $wu_id = "XXXXXX";
  $wu_station_key = "*******";
  $baromhpacorrection = 0;  # Correction for pressure on sea-level
```
