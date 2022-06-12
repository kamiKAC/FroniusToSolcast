<?php
// Begin of user configured data
$db_host = "localhost";
$db_user = "db_user";
$db_pass = "db_pass";
$db_name = "db_name";
$resource_id = "xxxx-xxxxx-xxxx-xxxx";
$api_key = "xxxxxxxxxxxxxxxxxxxxxxxxxxx";
// End of user configured data

$get_url = "https://api.solcast.com.au/rooftop_sites/" .$resource_id. "/forecasts?format=json";

echo ("Trying to get forecast data... ");
$ch = curl_init($get_url ."&api_key=". $api_key);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);......
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($http_code == 200)
{
  echo("Forecast data fetched.\n");
}
else
{
  die("Can't get forecast data!\nHTTP Error $http_code \n" .var_dump($data));
}

$connect = mysqli_connect($db_host, $db_user, $db_pass, $db_name); //Connect PHP to MySQL Database
if (!$connect) {
  die("Connection to SQL failed: " . mysqli_connect_error());
}

echo "SQL DB connected successfully.\n";
$query = 'REPLACE INTO pv_forecast(period_end, period_end_iso8061, period, pv_estimate, pv_estimate10, pv_estimate90) VALUES ';
$array = json_decode($data, true); //Convert JSON String into PHP Array
//var_dump($array);
$i = 0;
foreach($array["forecasts"] as $row) //Extract the Array Values by using Foreach Loop
{
  if ( $i > 0 ) {
    $query .= ",";
    }
  $query .= "('".strtotime($row["period_end"])."', '".$row["period_end"]."', '".$row["period"]."', '".$row["pv_estimate"]."', '".$row["pv_estimate10"]."', '".$row["pv_estimate90"]."')";
  $i++;
  }
$query .= ";";

if ($i>0) {
  echo ("Found $i forecast records, will try to update database\n");
  }
else {
  die ("No forecast recods found. Exiting.");
  }

if( $result = mysqli_query($connect, $query)) {
  echo ("Imported $i forecast records\n");
  }
else {
  var_dump($result);
  }
?>
