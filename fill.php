<?php

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
	//$wifiTime = $devices["last_status_store"];
	//if( $wifiTime < time() - 24*60*60)$wifi = 100;
	$wifiImage = getWiFiImage($wifi);
	//$wifi = 100 - $wifi;
	// RADIO
    $numStations = count($res) ; 
    for($i = 0;$i < $numStations;$i++)
        $nameStations[$i] = $res[$i]['module_name'];

echo("
	<tr><td style='text-align:left;' colspan='7'>
		<a href='#' class='tooltip' style='font-size:8px;'>
  		Autres informations:		
        <div >
        <table class='info'>
        <tr><td style='width:105px;'>$nameStations[0]</td>
        <td>WiFi</td> <!--<td>$wifi</td>-->
        <td><img src=$wifiImage ALT='wifi' height='20' /></td>
        </tr></table>
"); 
echo("
    <table class='info'>
    <th style='width:105px;'></th> <th>Radio</th> <th>Batterie</th>
");    
    for($i = 0;$i < $numStations -1;$i++)
        {$name = $nameStations[$i + 1];
        $radio = $devices['modules'][$i]['rf_status'];
        $radioImage = getRadioImage($radio);
        $battery = $devices['modules'][$i]['battery_vp']; 
        $batteryImage = getBatteryImage($battery);
        echo("<tr>
        <td>$name</td>
        <td><img src=$radioImage ALT='signal' height='13' /></td>
        <td><img src=$batteryImage ALT='battery' height='13' /></td>
        </tr>");
        }

echo("
        </table>
        </div>

	</a> </td>  
	
	</tr></table>
");	
}

/* 56,71,86,100 */	    
function getWiFiImage($wifi)
    {if($wifi > 90) return 'icone/wifi_low.png';
    if($wifi > 60) return 'icone/wifi_medium.png';
    if($wifi > 40) return 'icone/wifi_high.png';    
    return 'icone/wifi_full.png';
    }
function getRadioImage($radio)
    {if($radio > 90) return 'icone/signal_verylow.png';
    if($radio > 80) return 'icone/signal_low.png';
    if($radio > 70) return 'icone/signal_medium.png';
    if($radio > 60) return 'icone/signal_high.png';
    return 'icone/signal_full.png';
    }
function getBatteryImage($battery)
    {if($battery > 5500) return 'icone/battery_full.png';
    return 'icone/battery_high.png';
    }
?>
