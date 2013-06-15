<?php

function fill($station,$alt,$res,$tmin,$tmax)
	{$dat0 = date('d/m/Y H:i',$res[0]['time']);
	$dat1 = date('d/m/Y H:i',$res[1]['time']);
	echo("
	<table class='t'>
	<tr>
	<th colspan='5'>$station</th>
	<tr></tr>
	<td  colspan='5' class='alt'>(${alt}m)</th>	
	</tr><tr>
	<td><IMG SRC='sun.png' ALT='outside' height='40'></td> 
	<td  class='c1'>{$res[1]['Temperature']}°</td>
	<td class='e'></td>
	<td><IMG SRC='maison.png' ALT='insideside' height='40'></td> 
	<td class='c1'>{$res[0]['Temperature']}°</td>
	</tr><tr>
	<td class='pl'>MinMax</td><td class='minimax' >${tmin}°&nbsp<font color='red'>${tmax}°</font></td>	
	<td></td>
	<td class='cl'>CO2</td>
	<td class='c'>{$res[0]['CO2']}ppm</td>
	</tr><tr>
	<td class='hl'>Humidité</td>
	<td class='h'>{$res[1]['Humidity']}%</td>
	<td></td>
	<td class='hl'>Humidité</td>
	<td class='h'>{$res[0]['Humidity']}%</td>
	</tr><tr>
	<!--<td></td><td></td><td></td>-->
	<td class='pl'>Pression</td>
	<td class='p'>{$res[0]['Pressure']}mb</td>
	<!--</tr><tr>
	<td></td><td>--></td><td></td>
	<td class='nl'>Noise</td>
	<td class='n'>{$res[0]['Noise']}db</td>
	</tr><tr>
	<td class='s'colspan='2'>$dat1</td>
	<td></td>
	<td class='s' colspan='2'>$dat0</td>
	</tr>
	</table>
");
}
?>