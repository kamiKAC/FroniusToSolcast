# FroniusToSolcast
Scripts to send PV production data to Solcast.com and fetch production forecast to mySQL database

## Sending data to Solcast
<ul>
<li>To send data to Solcast you need to register on Solcast.com and create your site,
<li>After creating site copy it's 'Resource Id' and paste in get_pv_data.php into $resource_id variable,
<li>Click on your name in top right corner of solcast.com website and choose 'Your API key'. Create new key and copy it to get_pv_data.php into $api_key variable,
<li>Put your Fronius IP address into $get_addr variable of get_pv_data.php.
<li>Put proper value into $period variable. It should be the same as configured in Fronius DataManager recording period (5 minutes = 300s).
</ul>
For testing use following command:<br>

```
/usr/bin/php get_pv_data.php
```

If you see 'Added X measurements to Solcast' and X > 0 then you've successfully sent data from your Fronius to Solcast.<br>
To automaticaly send data to Solcast type:<br>

```
crontab -e
```

and add following line at the end of your crontab:<br>

```
55,25 5-22 * * *  /path_to_your_scripts/get_pv_data.php
```

This will send data to Solcast every 30 minutes between 5:25am and 10:55pm


## Getting forecast data from Solcast and inserting into mySQL

To get forecast you need to configure mySQL database first:
<ul>
<li>Log in into your mySQL and issue following SQL commands:<br>

```
CREATE DATABASE IF NOT EXISTS `grafana_data` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `grafana_data`;
CREATE TABLE `pv_forecast` (`period_end` int(11) NOT NULL, `period_end_iso8061` text NOT NULL,`period` text NOT NULL,`pv_estimate` text NOT NULL, `pv_estimate10` text NOT NULL, `pv_estimate90` text NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `pv_forecast` ADD PRIMARY KEY (`period_end`);
```

<li>Then fill get_pv_forecast.php with $db_host, $db_user, $db_pass, $db_name (grafana_data)
  <li>Test your configuration by issuing <code>/usr/bin/php get_pv_forecast.php</code><br>
If you see output similar to this:
  
```
Trying to get forecast data... Forecast data fetched.
SQL DB connected successfully.
Found 336 forecast records, will try to update database
Imported 336 forecast records
```

you successfully got your forecast data,
<li>To automaticaly get forecast data from Solcast type:<br>

```
crontab -e
```

and add following line at the end of your crontab:<br>

```
0,30 7-23 * * *  /usr/bin/php /path_to_your_scriptsb/get_pv_forecast.php
```

This will fetch Solcast PV forecast every 30 minutes between 7:00am and 23:30pm

