<?php
function drawMenuStation()
	{global $datebeg,$dateend,$num,$mesures;
?>	
	<form method='post' action='graphiques.php' onsubmit='return valider(this);'>	
	<table class='graphic'>
	<tr><td colspan='2' style='text-align:center; font-weight:bold; padding-top:10px;  padding-bottom:5px'>Graphiques d'une station</caption> 
	</tr>
	<tr>
	<td style='height:25px; width:130px;'>Début</td>
	<td><input class='date' id='id_date0' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'></td>
	</tr>

	<tr>
	<td style='height:25px;'>Fin</td>
	<td><input class='date' id='id_date1'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' ></td>
	</tr>

	<tr>
	<td id='id_duree' style='height:25px;'>Fréquence
	</td>	
	<td>
		<select name='select' onChange='Allow(this);'>
		<option value='1week' > 1 semaine </option>
		<option value='1day' selected='selected' > 1 journée </option>
		<option value='3hours' > 3 heures </option>
		<option value='30min'> 30 minutes </option>
		<option value='max' > 5 minutes </option>
		</select>		
	</td>	
	</tr>

	<tr>
		<td>Choisir une station</td>
		<td>			
		<?php
		echo("<table>\n");
		for($i = 0;$i < $num;$i++)
			{$stat = $mesures[$i]['station_name'];
			$arr = str_split($stat,17);
			$stat = $arr[0];
			if($i == 0)
				echo("<tr><td style='font-size:12px;'><input style='font-size:12px;' type='radio' name='station' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td style='font-size:12px;'><input  type='radio' name='station' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");
		?>	
		</td>
	</tr>
	
	<tr><td>
	
	<input type='submit' style='background-color:#ddd;'>
		</form>
	
	</td>
	<td><form  action='iconesExt.php' method='post'>
	<input type='submit' value='Main menu' style='color:black; background-color:#ddd;'/>
	</form>
	</td>
	<td>		
	<td><form action='logout.php' method='post'>
	<input type='submit' value='Logout' style='color:#a00; background-color:#ddd;' />	
	</form>
	</td>
	</tr>
	</table>	
	
<?php	
	}
function drawMenuCompare()
	{global $datebeg,$dateend,$num,$mesures;
?>
	<form method='post' action='compareALL.php' onsubmit='return valider(this);'>	
	<table class='graphic'>
	<tr><td colspan='2' style='text-align:center; font-weight:bold; padding-top:10px; padding-bottom:5px'>
	Comparaison de stations</td></tr>
	
	<tr>
	<td style='height:25px; width:130px;'>Début</td>
	<td><input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'></td>
	</tr>
	
	<tr>
	<td style='height:25px;'>Fin</td>
	<td><input class='date'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' ></td>
	</tr>
	
	<tr>
	<td style='height:25px;'>Fréquence</td>	
	<td>
		<select name='select' onChange='Allow(this);'>
		<option value='1week' > 1 semaine </option>
		<option value='1day' selected='selected' > 1 journée </option>
		</select>		
	</td>	
	</tr>
	
	<tr>
		<td>Choisir des stations</td>
		<td>
<?php
		echo("<table>\n");
		for($i = 0;$i < $num;$i++)
			{$stat = $mesures[$i]['station_name'];
			$arr = str_split($stat,17);
			$stat = $arr[0];
			if($i == 0)
				echo("<tr><td style='font-size:12px;'><input style='font-size:12px;' type='checkbox' name='stats[]' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td style='font-size:12px;'><input  type='checkbox' name='stats[]' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");	
?>			
		</td>
	</tr>
	
	<tr><td><input type='submit' style='background-color:#ddd;'></td><td></td></tr>
	</table>
	</form>

<?php
	}
?>
