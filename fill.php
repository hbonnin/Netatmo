<?php

function fill($stationId,$devicelist,$alt,$res,$tmin,$tmax)
	{$station = $devicelist["station_name"];
	$int_name = $devicelist["module_name"];
	$ext_name = $devicelist["modules"][0]["module_name"];
	$nModule = count($res);
	$dat0 = date('d/m/Y',$res[0]['time']);
	$time0 = date('H:i',$res[0]['time']);
	$dat1 = date('d/m/Y',$res[1]['time']);
	$time1 = date('H:i',$res[1]['time']);
	$pres = intval($res[0]['Pressure']+.5);
	echo("
		
	<table class='icone'>
	<tr>
	<td colspan='7' class='th'>$station</td>
	</tr><tr>
	<td  colspan='7' class='alt'>(${alt}m)</td>	
	</tr><tr>
	<td colspan='3' class='name'>$ext_name</td>
	<td></td>
	<td colspan='3' class='name'>$int_name</td>
	</tr><tr>
	<td><img src='sun.png' ALT='outside' height='40'/></td> 
	<td  class='c1' colspan='2'>{$res[1]['Temperature']}°</td>
	<td></td>
	<td><img src='maison.png' ALT='insideside' height='40'/></td> 
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
	</tr><tr>
");	
	if($nModule > 2)
		{echo("	
		<td class='a'  colspan='7'>
		<a href='#' class='tooltip'>
  		Autres modules:	
  		<div>
  		<table>
  		<tr><td></td>	<td> Temp</td>	<td>Hum</td>	<td>CO2</td></tr>
  		");
  		for($j = 2; $j < $nModule ; $j++)
  			{$name = $res[$j]["module_name"];
  			$temp = $res[$j]["Temperature"];
  			$hum = $res[$j]["Humidity"];
  			$co2 = $res[$j]["CO2"];		
  			echo("<tr>
  			<td style='width:80px;'>$name</td>
  			<td style='color:#ff0000;'>{$temp}°</td>
  			<td class='h'>$hum %</td>
  			<td class='c'>$co2 ppm</td>
  			</tr>");
  			}
  			
  		echo("</table>");
  		echo("</div></a> </td></tr></table>");
  		}
	else
		echo("	
		<td class='a' colspan='7'>
		&nbsp;
		</td></tr>
		</table>
		");
}
?>
