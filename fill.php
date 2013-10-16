<?php
require_once 'AppliCommonPublic.php';

 function drawGauge($width,$val,$zh = '1')
    // image 490x32
    {$h = intval($zh*32*$width/490+.5);
    $pos = intval($width * ($val/100));
    $txt = "<div style='height:"."$h"."px;'>";
    $txt .= "<div><img src='icone/gauge_full.png' alt='full' width=\"$width\" height=\"$h\" style=\"position:absolute;\"/>\n";
    $txt .= "</div>\n"; 
    $sty = " position:relative; background-size:${width}px ${h}px ;";
    $sty .= " background-image:url(icone/gauge_empty.png); background-repeat:no-repeat;";
    $sty .= " background-position: ${pos}px 0px; width:${width}px; height:${h}px;";
    $txt .="<div style='"."$sty"."'>\n";
    $txt .="</div></div>\n";
    return $txt;
    }   
function fill($stationId,$devices,$mydevices,$res,$tmin,$tmax,$dtmin,$dtmax)
	{$Temperature_unit = $_SESSION['Temperature_unit'];
	$cu = $Temperature_unit ? '°':'F';
	$station = $devices["station_name"];
	$int_name = $devices["module_name"];
	$ext_name = $devices["modules"][0]["module_name"];
	$pres = intval($res[0]['Pressure']+.5);
	$titre = "({$mydevices['latlng']['latitude']}°,{$mydevices['latlng']['longitude']}°,{$mydevices['latlng']['altitude']}m)";
	$dateInt = date('d/m/Y H:i',$res[0]['time']);
	$dateExt = date('d/m/Y H:i',$res[1]['time']);
	$dateMinMax = 'min:'.date('H:i',$dtmin).' max:'.date('H:i',$dtmax);
    // Qualité air
/*    
    if(isset($devices['extra']))
        {$qa = $devices['extra']['air_quality']['data'][0]['value'][0][0];
        $polluant = $devices['extra']['air_quality']['data'][0]['value'][0][1];
        $gauge = drawGauge(95,$qa,1.5);
        }
*/	
    $tint = degree2($res[0]['Temperature']);
    $text = degree2($res[1]['Temperature']); 
    $thum = tr('Humidité');
    $tson = tr('Bruit');
    $tpression = tr('Pression');
	echo("		
	<table class='icone'>
	<tr>
	<td colspan='7' class='th' title=\"$titre\">$station</td>
	</tr>
	<tr>
	<td style='height=40px;'><img src='icone/sun.png' ALT='outside' height='40'/></td> 
	<td  class='c1' colspan='2' title=\"$dateExt\">$text $cu</td>
	<td></td>
	<td><img src='icone/maison.png' ALT='insideside' height='40'/></td> 
	<td class='c1' colspan='2' title=\"$dateInt\">$tint $cu</td>
	</tr><tr>
	
	<td class='pl'>MinMax</td><td class='minimax' colspan='2' title=\"$dateMinMax\">
	        ${tmin}$cu&nbsp;<span style='color:#bb0000;'>${tmax}$cu</span></td>
	<td class='e'></td>
	<td class='cl'>CO2</td>
	<td class='c' title=\"$dateInt\">{$res[0]['CO2']} </td><td class='cunit'>ppm</td>
	</tr><tr>

	<td class='hl'>$thum</td>
	<td class='h' title=\"$dateExt\">{$res[1]['Humidity']} </td><td class='hunit'>%</td>
	<td class='e'></td>	
	<td class='hl'>$thum</td>
	<td class='h' title=\"$dateInt\">{$res[0]['Humidity']} </td><td class='hunit'> %</td>
	</tr><tr>
	
	<td class='pl'>$tpression</td>
	<td class='p' title=\"$dateInt\">{$pres} </td><td class='punit'>mb</td>
	<td class='e'></td>
	<td class='nl'>$tson</td>
	<td class='n' title=\"$dateInt\">{$res[0]['Noise']}</td><td class='nunit'> db</td>
	</tr>
	");
	if(isset($devices['extra']))
	echo("<tr>
	<td class='cl'>Pollution</td>
	<td class='c'>$qa</td>
	<td class='cunit'> $polluant</td>
	<td class='e'></td>		
	<td colspan= '3'>$gauge</td>
	</tr>
    ");

    // WiFi
	$wifi = $devices["wifi_status"];
	$wifiTime = $devices["last_status_store"];
	$wifiT = $wifi. '  '.date("d/m/y H:i",$devices["last_status_store"]);
	if( $wifiTime < time() - 30*60)$wifi = 100;
	$wifiImage = getWiFiImage($wifi);
	$firmware = $devices["firmware"];	
	if(isset($devices['last_upgrade']))
        $firmwareDate = date("d/m/Y",$devices['last_upgrade']); 
    else 
        $firmwareDate = 'unknown';
	// RADIO
    $numStations = count($res) ; 
    for($i = 0;$i < $numStations;$i++)
        $nameStations[$i] = $res[$i]['module_name'];
$tinfo = tr("Autres informations");
echo("
	<tr><td class='tooltip' colspan='7'>
		<a href='#' class='tooltip'>
  		$tinfo:		
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
        $batteryType = $devices['modules'][$i]['type']; 
        $batteryImage = getBatteryImage($battery,$batteryType);
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
    if($wifi >= NAWifiRssiThreshold::RSSI_THRESHOLD_0) return 'icone/wifi_low.png';
    if($wifi >= NAWifiRssiThreshold::RSSI_THRESHOLD_1) return 'icone/wifi_medium.png';
    if($wifi >= NAWifiRssiThreshold::RSSI_THRESHOLD_2) return 'icone/wifi_high.png';    
    return 'icone/wifi_full.png';
    } 
// 60,70,80,90   
function getRadioImage($radio)
    {if($radio >= NARadioRssiTreshold::RADIO_THRESHOLD_0) return 'icone/signal_verylow.png';//90
    if($radio >= NARadioRssiTreshold::RADIO_THRESHOLD_1) return 'icone/signal_low.png';//80
    if($radio >= NARadioRssiTreshold::RADIO_THRESHOLD_2) return 'icone/signal_medium.png';//70
    if($radio >= NARadioRssiTreshold::RADIO_THRESHOLD_3) return 'icone/signal_high.png';//60
    return 'icone/signal_full.png';
    }  
function getBatteryImage($battery,$batteryType)
    {switch($batteryType)
        {case "NAModule4":
            {if($battery > 5640) return "icone/battery_full.png";
            if($battery > 5280) return "icone/battery_high.png";
            if($battery > 4920) return "icone/battery_medium.png";
            if($battery > 4560) return "icone/battery_low.png";   
            else return "icone/battery_verylow.png";
            }
        default: 
            {if($battery > 5500) return "icone/battery_full.png";
            if($battery > 5000) return "icone/battery_high.png";
            if($battery > 4500) return "icone/battery_medium.png";
            if($battery > 4000) return "icone/battery_low.png";   
            else return "battery_verylow.png";
            }
        }

    }
?>
