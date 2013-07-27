<?php
require_once 'AppliCommonPublic.php';

function fill($stationId,$devices,$alt,$res,$tmin,$tmax)
	{$station = $devices["station_name"];
	$int_name = $devices["module_name"];
	$ext_name = $devices["modules"][0]["module_name"];
	$dat0 = date('d/m/Y',$res[0]['time']);
	$time0 = date('H:i',$res[0]['time']);
	$dat1 = date('d/m/Y',$res[1]['time']);
	$time1 = date('H:i',$res[1]['time']);
	$pres = intval($res[0]['Pressure']+.5);
	    
	echo("		
	<table class='icone'>
	<tr>
	<td colspan='7' class='th'>$station</td>
	</tr>
	<tr>
	<td><img src='icone/sun.png' ALT='outside' height='40'/></td> 
	<td  class='c1' colspan='2'>{$res[1]['Temperature']}°</td>
	<td></td>
	<td><img src='icone/maison.png' ALT='insideside' height='40'/></td> 
	<td class='c1' colspan='2'>{$res[0]['Temperature']}°</td>
	</tr><tr>
	
	<td class='pl'>MinMax</td><td class='minimax' colspan='2' >${tmin}°&nbsp;<span style='color:#bb0000;'>${tmax}°</span></td>
	<td class='e'></td>
	<td class='cl'>CO2</td>
	<td class='c'>{$res[0]['CO2']} </td><td class='cunit'>ppm</td>
	</tr><tr>

	<td class='hl'>Humidité</td>
	<td class='h'>{$res[1]['Humidity']} </td><td class='hunit'>%</td>
	<td class='e'></td>	
	<td class='hl'>Humidité</td>
	<td class='h'>{$res[0]['Humidity']} </td><td class='hunit'> %</td>
	</tr><tr>
	
	<td class='pl'>Pression</td>
	<td class='p'>{$pres} </td><td class='punit'>mb</td>
	<td class='e'></td>
	<td class='nl'>Noise</td>
	<td class='n'>{$res[0]['Noise']}</td><td class='nunit'> db</td>
	</tr><tr>

	<td class='s'>$dat1</td><td class='s' style='text-align:right;'>$time1</td>
	<td></td><td></td>
	<td class='s' >$dat0</td><td class='s' style='text-align:right;'>$time0</td>
	<td></td>
    </tr>
    ");
    // WiFi
	$wifi = $devices["wifi_status"];
	$wifiTime = $devices["last_status_store"];
	$wifiT = $wifi. '  '.date("d/m/y H:i",$devices["last_status_store"]);
	if( $wifiTime < time() - 30*60)$wifi = 100;
	$wifiImage = getWiFiImage($wifi);
	$firmware = $devices["firmware"];
/*	
	if(isset($devices['last_fw_update']))
	    $firmwareDate = date("d/m/Y",$devices['last_fw_update']); 
	else
	    $firmwareDate = 'unknown';
*/	    
    $firmwareDate = date("d/m/Y",$devices['last_upgrade']); 
	// RADIO
    $numStations = count($res) ; 
    for($i = 0;$i < $numStations;$i++)
        $nameStations[$i] = $res[$i]['module_name'];

echo("
	<tr><td class='tooltip' colspan='7'>
		<a href='#' class='tooltip'>
  		Autres informations:		
        <div >
        <table class='info'>
        <tr><td style='width:90px;'>$nameStations[0]</td>
        <td colspan='2' style='text-align:center;'><img title='$wifiT' src=$wifiImage ALT='wifi' height='13' /></td>
        <td title=\"$firmwareDate\">$firmware</td>
        </tr>
"); 

    for($i = 0;$i < $numStations -1;$i++)
        {$name = $nameStations[$i + 1];
        $last_message = date("d/m/y H:i",$devices['modules'][$i]['last_message']);
        $radio = $devices['modules'][$i]['rf_status'];
        $radioImage = getRadioImage($radio);
        $battery = $devices['modules'][$i]['battery_vp']; 
        $batteryImage = getBatteryImage($battery);
        $firmware = $devices['modules'][$i]['firmware']; 
        $radioT   = $radio. ' '. $last_message;
        $batteryT = $battery. ' '. $last_message;
        echo("<tr>
        <td>$name</td>
        <td style='text-align:center;'><img title='$radioT' src=$radioImage ALT='signal' height='13' /></td>
        <td style='text-align:center;'><img title='$batteryT' src=$batteryImage ALT='battery' height='13' /></td>
        <td>$firmware</td>
        </tr>");
        }

echo("
        </table>
        </div>
	</a> </td>  	
	</tr></table>
");	
}
// 56 71 86   
function getWiFiImage($wifi)
    {if($wifi >= 100)return 'icone/wifi_unknown.png';
    if($wifi > NAWifiRssiThreshold::RSSI_THRESHOLD_0) return 'icone/wifi_low.png';
    if($wifi > NAWifiRssiThreshold::RSSI_THRESHOLD_1) return 'icone/wifi_medium.png';
    if($wifi > NAWifiRssiThreshold::RSSI_THRESHOLD_2) return 'icone/wifi_high.png';    
    return 'icone/wifi_full.png';
    } 
// 60,70,80,90   
function getRadioImage($radio)
    {if($radio > NARadioRssiTreshold::RADIO_THRESHOLD_0) return 'icone/signal_verylow.png';
    if($radio > NARadioRssiTreshold::RADIO_THRESHOLD_1) return 'icone/signal_medium.png';
    if($radio > NARadioRssiTreshold::RADIO_THRESHOLD_2) return 'icone/signal_medium.png';
    if($radio > NARadioRssiTreshold::RADIO_THRESHOLD_3) return 'icone/signal_high.png';
    return 'icone/signal_full.png';
    }  
function getBatteryImage($battery)
    {if($battery > 5500) return 'icone/battery_full.png';
    if($battery > 1000) return 'icone/battery_high.png';
    return 'icone/battery_verylow.png';
    }
?>
