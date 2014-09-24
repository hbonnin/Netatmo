<?php
require_once 'AppliCommonPublic.php';
/*
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
*/   
function dewpoint($t,$h) // input in celsius
    {$dew = $t - (14.55 + 0.114 * $t) * (1 - (0.01 * $h)) - pow((2.5 + 0.007 * $t) * (1 - (0.01 * $h)), 3) - (15.9 + 0.117 * $t) * pow(1 - (0.01 * $h), 14);
    $dew = degree2($dew);
    return round($dew,1);
    }
function heatIndex($t,$h) // input in celsius
    {if($t < 25)return '-';
    $F = (1.8*$t)+32;
    $F2 = $F*$F;
    $h2 = $h*$h;
    $heati = -42.379 + 2.04901523*$F + 10.14333127*$h - 0.22475541*$F*$h - .0068378*$F2 - .05481717*$h2 + .00122874*$F2*$h + .00085282*$F*$h2 - .00000199*$F2*$h2;
    $heati = degree2($heati);
    return round(5/9 * ($heati -32),0);
    }
function humidex($t,$dew) // input in celsius
    {$dew += 273.15;
    $hu = $t+0.5555*(6.11*exp(5417.7530*(1/273.16-1/($dew)))-10);
    return round(degree2($hu),0);
    }
function fill($stationId,$devices,$mydevices,$dashboard)
	{$Temperature_unit = $_SESSION['Temperature_unit'];
	$cu = $Temperature_unit ? '°':'F';
	$station = $devices["station_name"];
	$int_name = $devices["module_name"];
	$ext_name = $devices["modules"][0]["module_name"];
	$titre = "({$mydevices['latlng']['latitude']}°,{$mydevices['latlng']['longitude']}°,{$mydevices['latlng']['altitude']}m)";
    $drecent = $dashboard[-1]["time_utc"];
	$dateInt = date('d/m/Y H:i',$drecent);
	$dateExt = date('d/m/Y H:i',$dashboard[0]["time_utc"]);	
    $recentData = (time() - $drecent) > 24*60*60 ? 0:1;
    $thum = tr('Humidité');
    $tson = tr('Bruit');
    $tpression = tr('Pression');
    $tint = degree2($dashboard[-1]["Temperature"]);
    $textcelsius = $dashboard[0]["Temperature"];
    $text = degree2($textcelsius);
    $tmin = degree2($dashboard[0]["min_temp"]);
    $tmax = degree2($dashboard[0]["max_temp"]);
    $dtmax = $dashboard[0]["date_max_temp"];
    $dtmin = $dashboard[0]["date_min_temp"];  
    $dateMinMax = 'min:'.date('d/m/Y H:i',$dtmin).' max:'.date('d/m/Y H:i',$dtmax);
    $humInt = $dashboard[-1]["Humidity"];
    $humExt = $dashboard[0]["Humidity"];
    $co2 = $dashboard[-1]["CO2"];
    $particule = $dashboard[-1]["Particle"];
    $db = $dashboard[-1]["Noise"];
    $pres = intval($dashboard[-1]["Pressure"] + .5);
    $dew = dewpoint($textcelsius,$humExt);
    $heati = heatIndex($textcelsius,$humExt);
    $hu = humidex($textcelsius,$dew);
    if(!$recentData)$tint = $text ='OLD';
    $numModules = count($dashboard)-1;
    $pluvio = $mydevices["pluviometre"];
    if($pluvio)
            {$rain24 = $dashboard[$pluvio]["sum_rain_24"];
            $rain24 = intval($rain24*10+.5)/10;
            $rain1 = $dashboard[$pluvio]["sum_rain_1"];
            $rain1 = intval($rain1*10+.5)/10;
            $rain = $dashboard[$pluvio]["Rain"];
            $rain = intval($rain*10+.5)/10;
            $rainTitle = 'r: '.$rain.' 1h:'.$rain1.'mm 24h:'.$rain24.'mm';
            }
	echo("		
	<table class='icone'>
	<tr>
	<td colspan='7' class='th' title=\"$titre\">$station</td>
	</tr>
	<tr>
	<td style='height=40px;'><img src='icone/sun.png' ALT='outside' height='40'/></td> 
	<td  class='c1' colspan='2' title=\"$dateExt  Heat Index: $heati$cu Humidex: $hu$cu\">$text$cu <span style='color:#aa0000; font-size: 12px;'>($dew$cu)</span></td>
	<td></td>
	<td><img src='icone/maison.png' ALT='insideside' height='40'/></td> 
	<td class='c1' colspan='2' title=\"$dateInt\">$tint $cu</td>
	</tr><tr>
	
	<td class='pl'>MinMax</td><td class='minimax' colspan='2' title=\"$dateMinMax\">
	        ${tmin}$cu&nbsp;<span style='color:#bb0000;'>${tmax}$cu</span></td>
	<td class='e'></td>
	<td class='cl'>CO2</td>
	<td class='c' title=\"particules:$particule $dateInt\">$co2 </td><td class='cunit'>ppm</td>
	</tr><tr>

	<td class='hl'>$thum</td>
	<td class='h' title=\"$dateExt\">$humExt </td><td class='hunit'>%</td>
	<td class='e'></td>	
	<td class='hl'>$thum</td>
	<td class='h' title=\"$dateInt\">$humInt </td><td class='hunit'> %</td>
	</tr><tr>
	
	<td class='pl'>$tpression</td>
	<td class='p' title=\"$dateInt\">{$pres} </td><td class='punit'>mb</td>
	<td class='e'></td>
	<td class='nl'>$tson</td>
	<td class='n' title=\"$dateInt\">$db</td><td class='nunit'> db</td>
	</tr>
	");

    // WiFi
	$wifi = $devices["wifi_status"];
	$wifiTime = $devices["last_status_store"];
	$wifiT = $wifi. '  '.date("d/m/y H:i",$devices["last_status_store"]);
	if( $wifiTime < time() - 60*60)$wifi = 100;
	$wifiImage = getWiFiImage($wifi);
	$firmware = $devices["firmware"];	
	if(isset($devices['last_upgrade']))
        $firmwareDate = date("d/m/Y",$devices['last_upgrade']); 
    else 
        $firmwareDate = 'unknown';
	// RADIO
    $numStations = count($dashboard)-1;
    for($j = 0;$j < $numStations;$j++)
        $nameStations[$j] = $mydevices["modules"][$j]["module_name"];
    $nameInt = $mydevices["module_name"];
 
$tinfo = tr("Autres informations");
$train = tr("Pluie");
if($pluvio == -2)
    echo("
        <tr><td class='rl'></td><td class='r'></td><td class='cunit'></td><td class='e'></td>
        ");
else
    echo("
        <tr><td class='rl'>$train</td><td class='r' title=\"$rainTitle\"> $rain24</td><td class='cunit'>mm</td><td class='e'></td>   
        ");
echo("  <td class='tooltip' colspan='3'>
		<a href='#' class='tooltip'>
  		$tinfo:		
        <div >
        <table class='info'>
        <tr><td style='width:90px;'>$nameInt</td>
        <td colspan='2' style='text-align:center;'><img title='$wifiT' src=$wifiImage ALT='wifi' height='13' /></td>
        <td title=\"$firmwareDate\">$firmware</td>
        </tr>
"); 

    for($i = 0;$i < $numStations;$i++)
        {$name = $nameStations[$i];
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

function getWiFiImage($wifi)
    {if($wifi >= 100)return 'icone/wifi_unknown.png';
    if($wifi >= NAWifiRssiThreshold::RSSI_THRESHOLD_0) return 'icone/wifi_low.png';    //86
    if($wifi >= NAWifiRssiThreshold::RSSI_THRESHOLD_1) return 'icone/wifi_medium.png'; //71
    if($wifi >= NAWifiRssiThreshold::RSSI_THRESHOLD_2) return 'icone/wifi_high.png';   //56 
    return 'icone/wifi_full.png';
    } 
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
            {if($battery >= NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_0) return "icone/battery_full.png";
             if($battery >= NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_1) return "icone/battery_high.png";
             if($battery >= NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_2) return "icone/battery_medium.png";
             if($battery >= NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3) return "icone/battery_low.png";   
            return "icone/battery_verylow.png";
            }
        default: 
            {if($battery >= NABatteryLevelModule::BATTERY_LEVEL_0) return "icone/battery_full.png";
            if($battery >= NABatteryLevelModule::BATTERY_LEVEL_1) return "icone/battery_high.png";
            if($battery >= NABatteryLevelModule::BATTERY_LEVEL_2) return "icone/battery_medium.png";
            if($battery >= NABatteryLevelModule::BATTERY_LEVEL_3) return "icone/battery_low.png";   
            return "battery_verylow.png";
            }
        }

    }
?>
