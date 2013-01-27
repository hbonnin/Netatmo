<?php
function geolocalize($lat,$lng)
	{
    $url="http://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lng&sensor=false";    
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,15);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER ,TRUE);
	$result = curl_exec($ch);
    curl_close($ch);
    $decode = json_decode($result, TRUE);
    if($decode['status'] != "OK")return "BAD";
    return explode(",", $decode['results'][0]['formatted_address']);
	} 
?>	