<?php
function drawLogoutBack($draw=true)
	{
?>
<!-- drawLogoutBack -->
<table style='margin:auto; '>
	<tr>
	<td>
	<form  action='iconesExt.php' method='post'>
	<input type='submit' value="Main menu" style='color:black; background-color:#ddd;'/>
	</form>
	</td><td>
	<form action='logout.php' method='post'>
	<input type='submit' value='Logout' style='color:#a00; background-color:#ddd;' />	
	</form>	
	</td>
	<td>
	<a href='http://www.000webhost.com/' target='_blank' ><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='10'/></a>
	</td>		
	<td style='font-size:11px;'>
<?php
	if(isset($_SESSION['width']))
		echo("{$_SESSION['width']} x {$_SESSION['height']}");
?>
	</td></tr>
	</table>
<?php	
	if($draw)
		echo("
		<table class='ds_box'  id='ds_conclass' style='display: none;' >
		<caption id='id_caption' class='ds_caption'>xxxx</caption>
		<tr><td id='ds_calclass'>aaa</td></tr>
		</table>
		");
?>
<!-- end drawLogoutBack -->	
<?php
	}
function drawMenuStation($h = '')
	{global $datebeg,$dateend,$num,$mesures;
	
	if(isset($_SESSION['stationId']))
	    $stationId = $_SESSION['stationId'];
	else
	    {$stationId = 0;
	    $_SESSION['stationId'] = $stationId;
	    }
?>	
<!-- DrawMenu Station -->
	<form method='post' action='graphiques.php' onsubmit='return valider(this);'>
<?php	
	if($h)
		echo("<table class='graphic' style='height:$h; width:220px;'>");
	else
		echo("<table class='graphic' style='border-spacing:2px;'>");
?>	
	<tr><td colspan='2' style='text-align:center; font-weight:bold; padding-top:0px;  padding-bottom:5px'>Graphiques d'une station</td> 
	</tr>
	<tr>
	<td style='height:25px;'>Début</td>
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
		<td style='height:25px;'>Choisir une station</td>
		<td>			
		<?php
		echo("<table>\n");
		for($i = 0;$i < $num;$i++)
			{$stat = $mesures[$i]['station_name'];
			$arr = explode(" ",$stat);
			$stat = $arr[0];
			if($i == $stationId)
				echo("<tr><td style='font-size:12px;'><input style='font-size:12px;' type='radio' name='station' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td style='font-size:12px;'><input  type='radio' name='station' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");
		?>
		</td>
	<tr><td>
	<input type='submit'  style='background-color:#ddd;'>
	</td><td></td>		
	</tr>
	</table>
	</form>	
	
<!-- End DrawMenu Station -->	
<?php	
	}
function drawMenuCompare($h ='')
	{global $datebeg,$dateend,$num,$mesures;
	
	if(isset($_SESSION['viewCompare']))
	    $view = $_SESSION['viewCompare'];
	else
	    {for($i = 1 ;$i < $num; $i++)
	        $view[$i] = 0;
	    $view[0] = 1;
	    $_SESSION['viewCompare'] = $view;
        }
    if(isset($_SESSION['selectMesureCompare']))
        $selectMesure = $_SESSION['selectMesureCompare'];
    else 
        {$selectMesure = 'T';
        $_SESSION['selectMesureCompare'] = $selectMesure;
        }
       
?>
<!-- DrawMenu Compare -->
	<form method='post' action='compareALL.php' onsubmit='return valider(this);'>	
<?php	
	if($h)
		echo("<table class='graphic' style='height:$h; width:220px; '>");
	else
		echo("<table class='graphic' style='border-spacing:2px;'>");
?>	

	<tr><td colspan='2' style='text-align:center; font-weight:bold; padding-top:0px; padding-bottom:5px'>
	Comparaison de stations</td></tr>
	
	<tr>
	<td style='height:25px;'>Début</td>
	<td><input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'></td>
	</tr>
	
	<tr>
	<td style='height:25px;'>Fin</td>
	<td><input class='date'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' ></td>
	</tr>
	<tr>
	<td style='height:25px;'>Fréquence
	</td>	
	<td>
		<select name='select' onChange='Allow(this);'>
		<option value='1week' > 1 semaine </option>
		<option value='1day' selected='selected' > 1 journée </option>
		</select>		
	</td>	
	</tr>
	
	<tr>
	<td style='height:25px;'>Mesure
	</td>	
	<td>
		<select name='selectMsesure'>
<?php
        if($selectMesure == 'T')
            echo("<option value='T' selected='selected'> T° </option>
    		    <option value='H'  > H % </option>
            ");
        else
            echo("<option value='T'> T° </option>
    		    <option value='H'  selected='selected'> H % </option>
            ");
        
?>
 		</select>		
	</td>	
	</tr>	

	<tr>
		<td style='height:25px;'>Choisir des stations</td>
		<td>
<?php
		echo("<table>\n");
		for($i = 0;$i < $num;$i++)
			{$stat = $mesures[$i]['station_name'];
			$arr = explode(" ",$stat);
			$stat = $arr[0];
			if($view[$i])
				echo("<tr><td style='font-size:12px;'><input style='font-size:12px;' type='checkbox' name='stats[]' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td style='font-size:12px;'><input  type='checkbox' name='stats[]' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");	
?>			
		</td>
	</tr>
	
	<tr><td><input type='submit'  style='background-color:#ddd;'></td>
	<td></td></tr>
	</table>
	</form>	
	
<!-- End DrawMenu Compare -->


<?php
	}
function drawCharts()
	{
	echo("<table style='padding:0px; width:100%; margin-bottom:-5px;'>
	<tr>
	<td style='padding:0px; vertical-align:bottom;'>
	");
	$hh = 310;
    $h = $hh . 'px';
    $h1 = $hh+2 .'px';
	drawMenuCompare($h1); 
	echo("
	</td>
		<td  style='padding:0px; vertical-align:bottom; width:100%;'>
		<div id='chart0' class='chart' style='height:$h'></div></td>
	 </tr>
	 <tr>
	 <td style='padding:0px; vertical-align:bottom;'>
	 ");
	drawMenuStation($h1);
	echo("
	 </td>
		<td style='padding:0px; vertical-align:bottom; width:100%;'>
		<div id='chart1' class='chart' style='height:$h'></div></td>
	</tr>
	</table>
	");
	drawLogoutBack(); 
	}
	
function drawMenuModules($stationNum,$h ='')
	{global $datebeg,$dateend,$numStations,$nameStations;

	if(isset($_SESSION['viewModule']))
	    $view = $_SESSION['viewModule'];
	else
	    {for($i = 0 ;$i < $numStations; $i++)
	        $view[$i] = 1;
	    $_SESSION['viewModule'] = $view;
        }	        
	
    if(isset($_SESSION['selectMesureModule']))
        $selectMesure = $_SESSION['selectMesureModule'];
    else 
        {$selectMesure = 'T';
        $_SESSION['selectMesureModule'] = $selectMesure;
        }
    
?>	
	<!-- DrawMenu Modules -->
	<form method='post' action='modules.php?stationNum=<?php echo $stationNum;?>' onsubmit='return valider(this);'>	
	
<?php	
	
	if($h)
		echo("<table class='graphic' style='height:$h; width:220px; border-spacing:2px; '>");
	else
		echo("<table class='graphic' style='border-spacing:2px;'>");
?>	

	<tr><td colspan='2' style='text-align:center; font-weight:bold; padding-top:0px; padding-bottom:5px'>
	Comparaison de modules</td></tr>
	
	<tr>
	<td style='height:25px;'>Début</td>
	<td><input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'></td>
	</tr>
	
	<tr>
	<td style='height:25px;'>Fin</td>
	<td><input class='date'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' ></td>
	</tr>
	
	<tr>
	<td style='height:25px;'>Fréquence
	</td>	
	<td>
		<select name='select' onChange='Allow(this);'>
		<option value='1week' > 1 semaine </option>
		<option value='1day' selected='selected' > 1 journée </option>
		<!--<option value='3hours' > 3 heures </option>-->
		</select>		
	</td>	
	</tr>
	
	<tr>
	<td style='height:25px;'>Mesure
	</td>	
	<td>
		<select name='selectMsesure'>

<?php
        if($selectMesure == 'T')
            echo("<option value='T' selected='selected'> T° </option>
    		    <option value='H'  > H % </option>
    		    <option value='C'  > CO2 </option>
            ");
        else if($selectMesure == 'H')
            echo("<option value='T'> T° </option>
    		    <option value='H'  selected='selected'> H % </option>
    		    <option value='C'  > CO2 </option>   
            ");
        else
            echo("<option value='T'> T° </option>
    		    <option value='H' > H % </option>
    		    <option value='C'  selected='selected'  > CO2 </option>   
            ");
        
?>
		</select>		
	</td>	
	</tr>	
	
	<tr>
		<td style='height:25px;'>Choisir des stations</td>
		<td>
<?php
		echo("<table>\n");
		for($i = 0;$i < $numStations;$i++)
			{$stat = $nameStations[$i];
			$arr = explode(" ",$stat);
			$stat = $arr[0];
			if($view[$i])
				echo("<tr><td style='font-size:12px;'><input style='font-size:12px;' type='checkbox' name='stats[]' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td style='font-size:12px;'><input  type='checkbox' name='stats[]' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");	
?>			
		</td>
	</tr>
	
	<tr><td><input type='submit'  style='background-color:#ddd;'></td>
	<td></td></tr>
	</table>
	</form>		
<!-- End DrawMenu Module -->
	
<?php } ?>	

