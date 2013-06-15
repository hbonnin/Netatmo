<?php

function fill($station,$alt,$res,$tmin,$tmax)
	{$dat0 = date('d/m/Y H:i',$res[0]['time']);
	$dat1 = date('d/m/Y H:i',$res[1]['time']);
	$pres = intval($res[0]['Pressure']+.5);
	echo("
	<table class='t'>
	<tr>
	<th colspan='6'>$station</th>
	<tr></tr>
	<td  colspan='6' class='alt'>(${alt}m)</th>	
	</tr><tr>
	
	<td><IMG SRC='sun.png' ALT='outside' height='40'></td> 
	<td  class='c1' colspan='2'>{$res[1]['Temperature']}°</td>
	<td class='e'></td>
	<td><IMG SRC='maison.png' ALT='insideside' height='40'></td> 
	<td class='c1' colspan='2'>{$res[0]['Temperature']}°</td>
	</tr><tr>
	
	<td class='pl'>MinMax</td><td class='minimax' colspan='2' >${tmin}°&nbsp<font color='#bb0000'>${tmax}°</font></td>
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
	<td class='s'colspan='2'>$dat1</td>
	<td></td><td></td>
	<td class='s' colspan='2'>$dat0</td>
	</tr>
	</table>
");
}
?>