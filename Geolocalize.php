<?php

function geolocalize($lat,$lng)
	{//global $use_google_key,$google_key;   
    //if(!$use_google_key){$_SESSION['LogMsg'] .= $date.':geolocalize: No Google key <br>';return "BAD";}
    $url="https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lng&sensor=false";    
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,15);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER ,TRUE);
	$result = curl_exec($ch);
    curl_close($ch);
    $decode = json_decode($result, TRUE);
    $txt = $decode['status'];
    $date = date("D H:i s",time());
    if($decode['status'] != "OK"){$_SESSION['LogMsg'] .= $date.':geolocalize: '.$txt.'<br>';return "BAD";}
    return explode(",", $decode['results'][0]['formatted_address']);
	} 
?>	
