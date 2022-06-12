<?php
// Begin of user configured variables
$resource_id = "xxx-xxxx-xxxx-xxxx";
$api_key = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
$get_addr = "fronius_ip_addr";
$period = 300; //seconds - should be the same as configured in Fronius DataManager
// End of user configured variables

$post_url = "https://api.solcast.com.au/rooftop_sites/" .$resource_id. "/measurements";
$get_url = "http://" .$get_addr. "/solar_api/v1/GetArchiveData.cgi?Scope=System&Channel=EnergyReal_WAC_Sum_Produced";

function ISO8061_Time($epoch_time) {
        $timeoffset = intval(date("Z",$epoch_time));
        $iso8061_time = date("Y-m-d\TH:i:s\.0000000\Z",$epoch_time-$timeoffset);
        return $iso8061_time;
}
$period_iso8061 = "PT". ($period / 60) ."M";
$date = date("d.m.Y",strtotime( 'today' ));
echo("Fetching measurements from Fronius...\n");
$ch = curl_init($get_url ."&StartDate=". $date ."&EndDate=". $date);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($http_code == 200)
{
        echo("Measurements fetched. ");
}
else
{
        die("Can't get measurements!\nHTTP Error $http_code \n" .var_dump($data));
}
$array = json_decode($data, true);
$start_time = $array["Body"]["Data"]["inverter/1"]["Start"];
echo ("Start time: $start_time \n");
$start_epoch = strtotime($start_time);

$output = array();
$output ["measurements"] = array();
$i = 0;
foreach($array["Body"]["Data"]["inverter/1"]["Data"]["EnergyReal_WAC_Sum_Produced"]["Values"] as $offset => $value) //Extract the Array Values by using Foreach Loop
{
        //echo("offset: " .$offset. ", value: " .$value ."\n");
        $output ["measurements"][$i]["period_end"] = ISO8061_time($start_epoch + $offset);
        $output ["measurements"][$i]["period"] = $period_iso8061;
        $output ["measurements"][$i]["total_power"] = (3600 * $value / ($period * 1000));
        $i++;
}
if ($i > 0)
{
        echo("Found $i items\n");
}
else
{
        die("Couldn't find measurements. Aborting.");
}

echo ("post_url: $post_url\n");
echo "Sending $i measurements of $date...\n";
$post_data = json_encode($output);
// Make request via cURL.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $post_url);

// Set options necessary for request.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
curl_setopt($ch, CURLOPT_USERPWD, $api_key . ":");
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

// Send request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($http_code == 200)
{
        $result = json_decode($response);
        //var_dump ($result);
        echo ("Added " .count($result->measurements). " measurements of $date to Solcast\n");
}
elseif ($http_code == 404)
{
        echo ("Something went wrong:\n$response\n");
}


?>
