<?php
$opt = array (
            0 => array ('1week','1 semaine'),
            1 => array ('1day','1 journée'),
            2 => array ('3hours','3 heures'),
            3 => array ('30min','30 miniutes'),
            4 => array ('max','5 minutes')
            );
$interval = array ("G" => 4,
                    "C"  => 1,
                    "M"  => 2, 
                    "opt" => $opt
            );

function checkSelect($select,$menu)
    {$opt = array (
            0 => array ('1week','1 semaine'),
            1 => array ('1day','1 journée'),
            2 => array ('3hours','3 heures'),
            3 => array ('30min','30 miniutes'),
            4 => array ('max','5 minutes')
            );
    $interval = array ("G" => 4,
                    "C"  => 1,
                    "M"  => 2, 
                    "opt" => $opt
            );
    $iselect = selectIndex($opt,$select); 
    $selected = min($iselect,$interval[$menu]);
    return  $interval['opt'][$selected][0];        
    }
function selectIndex($opt,$select)
    {$max = count($opt);
    for($i = 0; $i < $max;$i++)
        if($select == $opt[$i][0])break;
    return $i;    
    }
function drawSelectInter($menu)
    {
    $opt = array (
            0 => array ('1week','1 semaine'),
            1 => array ('1day','1 journée'),
            2 => array ('3hours','3 heures'),
            3 => array ('30min','30 miniutes'),
            4 => array ('max','5 minutes')
            );
    $interval = array ("G" => 4,
                    "C"  => 1,
                    "M"  => 2, 
                    "opt" => $opt
            );
    $select  = $_SESSION['selectedInter'];   
    $iselect = selectIndex($interval['opt'],$select); 
    $selected = min($iselect,$interval[$menu]);
    for($i = 0; $i <= $interval[$menu];$i++)
        {$val = $interval['opt'][$i][0];
        $txt = $interval['opt'][$i][1];
        $sel ='';
        if($i == $selected)$sel = "selected='selected'";
        echo "<option value="."'" .$val."' ". $sel."/>". $txt.' </option>'."<br>";       
        }   
    }            
function drawLogoutBack()
	{if(!isset($_SESSION['stationId'] ))
        $_SESSION['stationId'] = 0;
    $menuModules = 'modules.php?stationNum=' . $_SESSION['stationId'];
?>
<!-- drawLogoutBack -->
<table style='margin:auto; '>
	<tr>
	<td>
	<form  action='graphiques.php?extern=1' method='post'>
	<input type='submit' class='submit' value="Graphiques d'une station" />
	</form>
	</td>
	<td>
	<form  action=<?php echo $menuModules; ?> method='post'>
	<input type='submit' class='submit' value="Modules d'une station"  />
	</form>
	</td>
	<td>
	<form  action='iconesExt.php' method='post'>
	<input type='submit' class='submit' value="Menu principal"/>
	</form>
	</td>
	<td>
	<form action='logout.php' method='post'>
	<input type='submit' class='submit' value='Logout' style='color:#700; ' />	
	</form>	
	</td>
    </tr></table>
<table style='margin-left:auto; margin-right:auto; margin-top:-5px;'>
	<tr>
	<td>
	
	<a href='http://www.000webhost.com/' target='_blank' ><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='10'/></a>
	
	</td>		
	<td style='font-size:11px;'>
<?php
	if(isset($_SESSION['width']))
		echo("{$_SESSION['width']} x {$_SESSION['height']}");
?>
	</td>
	<!--
    <td style='display: none;'>
    <script src='http://www.counter160.com/js.js?img=15'></script>
    <br>
    <a href='http://www.000webhost.com'>
    <img src='http://www.counter160.com/images/15/left.png' alt='Free web hosting' style='border:0px'>
    </a>
    <a href='http://www.hosting24.com'>
    <img alt='Web hosting' src='http://www.counter160.com/images/15/right.png' style='border:0px' >
    </a>
    </td>
    -->
    <td></td>
</tr></table>	
	</tr>
	</table>
<!-- end drawLogoutBack -->	
<?php
	}
//<!-- =============================================================================================== -->	
function drawMenuStation($h = '')
	{global $num,$mesures;
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	
	
	if(isset($_SESSION['stationId']))
	    $stationId = $_SESSION['stationId'];
	else
	    {$stationId = 0;
	    $_SESSION['stationId'] = $stationId;
	    }
?>	
<!-- DrawMenu Station ------------------------------------------------------------------------------------------->
	<form method='post' action='graphiques.php' onsubmit='return valider(this);'>
<?php	
	if($h)
		echo("<table class='graphic' style='height:$h;'>");
	else
		echo("<table class='graphic' style='height:370px;'>");
?>	
	<tr><td colspan='2'  class='title'>
	Graphiques d&#39;une station</td> </tr>	
	<tr>
	<td>Début</td>
<?php
$interval = $_SESSION['selectedInter']; 
if(($interval != '30min') && ($interval != 'max') )
    echo("
    	<td><input class=\"date\" id=\"id_date0\" type=\"text\" name=\"date0\" value=\"$datebeg\" onclick=\"ds_sh(this,0);\"></td>
    ");
else 
    echo("
    	<td><input class=\"date\" style=\"visibility:hidden; \" id=\"id_date0\" type=\"text\" name=\"date0\" value=\"$datebeg\" onclick=\"ds_sh(this,0);\"></td>
    ");
?>

	</tr>
	<tr>
	<td>Fin</td>
	<td><input class='date' id='id_date1'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' ></td>
	</tr>

	<tr>
	<td id='id_duree'>Fréquence 
	</td>	
	<td>
		<select name='select' onChange='Allow(this);'>

<?php	
     drawSelectInter("G"); 
?>
<!--
		<option value='1week' > 1 semaine </option>
		<option value='1day' selected='selected' > 1 journée </option>
		<option value='3hours' > 3 heures </option>
		<option value='30min'> 30 minutes </option>
		<option value='max' > 5 minutes </option>
-->		
		</select>		
	</td>	
	</tr>
	<tr>
		<td>Station</td>
		<td>			
		<?php
		echo("<table class='chk'>\n");
		for($i = 0;$i < $num;$i++)
			{$stat = $mesures[$i]['station_name'];
			$arr = explode(" ",$stat);
			$stat = $arr[0];
			if($i == $stationId)
				echo("<tr><td><input type='radio' name='station' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td><input  type='radio' name='station' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");
		?>
		</td>
	<tr><td class='submitG'>
	<input type='submit' class='submitG' >
	</td><td class='submitG'></td>		
	</tr>
	</table>
	</form>	
	
<!-- End DrawMenu Station ---------------------------------->	
<?php	
	}
function drawMenuCompare($h ='')
	{global $num,$mesures;
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	
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
<!-- DrawMenu Compare ------------------------------------------------------------------------------------------->
	<form method='post' action='compareALL.php' onsubmit='return valider(this);'>	
<?php	
	if($h)
		echo("<table class='graphic' style='height:$h;'>");
	else
		echo("<table class='graphic' style='height:370px;' >");
?>	

	<tr><td colspan='2'  class='title'>
	Comparaison de stations</td></tr>
	
	<tr>
	<td >Début</td>
	<td><input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'></td>
	</tr>
	
	<tr>
	<td >Fin</td>
	<td><input class='date'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' ></td>
	</tr>
	<tr>
	<td >Fréquence
	</td>	
	<td>
		<select name='select' onChange='Allow(this);'>
<?php	
     drawSelectInter("C"); 
?>		
		</select>		
	</td>	
	</tr>
	
	<tr>
	<td >Mesure
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
		<td>Stations</td>
		<td>
<?php
		echo("<table class='chk'>\n");
		for($i = 0;$i < $num;$i++)
			{$stat = $mesures[$i]['station_name'];
			$arr = explode(" ",$stat);
			$stat = $arr[0];
			if($view[$i])
				echo("<tr><td><input  type='checkbox' name='stats[]' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td ><input  type='checkbox' name='stats[]' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");	
?>			
		</td>
	</tr>
	<tr><td class='submitG'><input type='submit' class='submitG' ></td>
	<td class='submitG'></td></tr>
	</table>
	</form>	
	
<!-- End DrawMenu Compare -->


<?php
	}
function drawCharts()
	{
	echo("
		<table class='ds_box'  id='ds_conclass' style='display: none;' >
		<caption id='id_caption' class='ds_caption'>xxxx</caption>
		<tr><td id='ds_calclass'>aaa</td></tr>
		</table>
		");
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
/*************************************************************************/	
function drawMenuModules($h ='')
	{global $numStations,$nameStations;

	$mesures = $_SESSION['mesures'];
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	
    $stationNum = $_SESSION['stationId']; 
    $devicelist = $_SESSION['devicelist'];
    $stationName = $devicelist['devices'][$stationNum]['station_name'];
    $num = count($devicelist["devices"]);

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
	<!-- DrawMenu Modules --------------------------------------------------------------------------------------->
	<form method='post' action='modules.php?stationNum=<?php echo $stationNum;?>' onsubmit='return valider(this);'>	
	
<?php	
	
	if($h)
		echo("<table class='graphic' style='height:$h;'>");
	else
		echo("<table class='graphic'>");
?>	

	<tr><td colspan='2' class='title'>
	Comparaison de modules</td></tr>
    </tr>
  <!--  station -->
    <tr>
    <td> Station </td>
	    <td>
    		<select name='selectStation'>
		<?php
		for($i = 0;$i < $num;$i++)
			{$stat = $mesures[$i]['station_name'];
			$arr = explode(" ",$stat);
			$stat = $arr[0];
			if($i == $_SESSION['stationId'])
			    echo("<option value='$i' selected='selected'>$stat</option>");
            else
			    echo("<option value='$i'>$stat</option>");            
			}
    	?>
            </select>
    </td>    
    </tr>  
	<tr>
	<td style=''>Début</td>
	<td><input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'></td>
	</tr>
	
	<tr>
	<td style=''>Fin</td>
	<td><input class='date'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' ></td>
	</tr>
	
	<tr>
	<td style=''>Fréquence
	</td>	
	<td>
		<select name='select'>
<?php	
     drawSelectInter("M"); 
?>	
		</select>		
	</td>	
	</tr>
	
	<tr>
	<td >Mesure
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
		<td>Modules</td>
		<td>
<?php
		echo("<table class='chk'>\n");
		for($i = 0;$i < $numStations;$i++)
			{$stat = $nameStations[$i];
			$arr = explode(" ",$stat);
			$stat = $arr[0];
			if($view[$i])
				echo("<tr><td><input type='checkbox' name='selectedModules[]' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td><input  type='checkbox' name='selectedModules[]' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");	
?>			
		</td>
	</tr>
	
	<tr>
		<td class='submitG'> <input type='submit' class='submitG' ></td>
		<td class='submitG'>
    </tr>

	</table>
	</form>		
<!-- End DrawMenu Module -->
	
<?php } ?>	

