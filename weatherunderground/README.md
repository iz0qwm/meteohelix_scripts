	
# index.php to Forward data to Wunderground
This script receives the data coming from a Forwarder configured on the weather.allmeteo.com website.
 
* Create e new weather station on Wunderground. Keep note of Station_ID and Station_Key
* Configure this script with your Wunderground Station_ID and Station_Key
* Configure this script with the altitude of the Weather Station in meters to have the pressure corrected to the sea level.
* Put this script in a directory of you website i.e. http://mysite/wu/index.php
* Configure a Forwarder on weather.allmeteo.com - HTTP/Post - that points to this script. Record must be Default
	
## Parameters to set
```
  # Settings: General

  $wu_id = "XXXXXX";
  $wu_station_key = "XXXXXX";
  $altitude = XXX;  # Altitude of the weather station in meters.
```
