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
        echo "<option value="."'" .$val."' ". $sel.">". $txt.' </option>'."\n";       
        }   
    }            
function drawLogoutBack()
	{if(!isset($_SESSION['stationId'] ))
        $_SESSION['stationId'] = 0;
    $stationId = $_SESSION['stationId'];
    $menuModules = 'modules.php?stationNum=' .$stationId ;
?>
<!-- drawLogoutBack -->
<table style='margin:auto; '>
	<tr>
	<td>
	<form  action="graphiques.php?extern='1'" method='post'>
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
<!--	
    </tr>
    </table>
<table style='margin-left:auto; margin-right:auto; margin-top:-8px;'>
	<tr>
-->	
	<td>	
	<a href='http://www.000webhost.com/' target='_blank' ><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='10'/></a>
	</td>		
	
<?php
	if(isset($_SESSION['width']))
	    {echo("<td style='font-size:11px;'>");
		echo("size:{$_SESSION['width']} x {$_SESSION['height']}");
		echo("</td>");
		}
?>
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
<!--</tr></table>-->	
	</tr>
	</table>
	
<!-- end drawLogoutBack -->	
<?php
	}
/* -- DrawMenuStation ************************************************************************* */
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
	<form method='post' action='graphiques.php' onsubmit='return valider(this);'>

    <table class='G'>
	<tr>
	<td class='g' style='height:3px'>
	<div class='f' style='height:3px'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px; width:205px; vertical-align:bottom;'>
    <div class='f' style='height:30px; '>Graphiques d&#39;une station
    </div></td></tr>	

	<tr>
	<td class='g'>
	<div class='fl'>Début</div>
	<div class='fr'>
<?php
$interval = $_SESSION['selectedInter']; 
if(($interval != '30min') && ($interval != 'max') )
    echo("
    	<input class=\"date\" id=\"id_date0\" type=\"text\" name=\"date0\" value=\"$datebeg\" onclick=\"ds_sh(this,0);\">
    ");
else 
    echo("
    	<input class=\"date\" style=\"visibility:hidden; \" id=\"id_date0\" type=\"text\" name=\"date0\" value=\"$datebeg\" onclick=\"ds_sh(this,0);\">
    ");
?>
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'>Fin</div>	
	<div class='fr'>
	<input class='date' id='id_date1'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'><span id='id_duree' >Fréquence</span></div>	
	<div class='fr'>
	<select name='select' onChange='Allow(this);'>
<?php	
     drawSelectInter("G"); 
?>
	</select>		
	</div></td></tr>
		
	<tr>
	<td class='g'>
	<div class='f'>
	</div></td></tr>

	<tr><td>
	    <table style='height:100%; width:100%;'>
	    <tr>
		<td style='width:95px'>Station</td>
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
        </td></tr></table>
        
	</td></tr>

	<tr>
	<td class='g'  style='height:20px;'>
	<div class='f'  style='height:20px;'>	
	<input type='submit' class='g'>	
	</div>
	</td></tr>
			
	</table>
	</form>	
	
<?php	
	}
/* drawMenuCompare ******************************************************************************/
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
	<form method='post' action='compareALL.php' onsubmit='return valider(this);'>	

    <table class='G'>
	<tr>
	<td class='g' style='height:3px'>
	<div class='f' style='height:3px'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px; width:205px; vertical-align:bottom;'>
    <div class='f' style='height:30px; '>Comparaison de stations
    </div></td></tr>	

	<tr>
	<td class='g'>
	<div class='fl'>Début</div>
	<div class='fr'>
	<input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'>
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'>Fin</div>	
	<div class='fr'>
	<input class='date' type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'><span>Fréquence</span></div>	
	<div class='fr'>
	<select name='select' onChange='Allow(this);'>
<?php	
     drawSelectInter("C"); 
?>
	</select>		
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'>Mesure</div>	
	<div class='fr'>
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
	</div></td></tr>

	<tr><td>
	    <table style='height:100%; width:100%;'>
	    <tr>
		<td style='width:95px'>Station</td>
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
        </td></tr></table>
	</td></tr>

	<tr>
	<td class='g'  style='height:20px;'>
	<div class='f' style='height:20px;'>	
	<input type='submit' class='g'>	
	</div>
	</td></tr>

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
/* drawMenuModules ************************************************************************/	
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
	<form method='post' action='modules.php?stationNum=<?php echo $stationNum;?>' onsubmit='return valider(this);'>	

  <table class='G'>
	<tr>
	<td class='g' style='height:3px'>
	<div class='f' style='height:3px'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px; width:205px; vertical-align:bottom;'>
    <div class='f' style='height:30px; '>Comparaison de modules
    </div></td></tr>	

	<tr>
	<td class='g'>
	<div class='fl'><span>Station</span></div>	
	<div class='fr'>
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
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'>Début</div>
	<div class='fr'>
	<input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'>
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'>Fin</div>	
	<div class='fr'>
	<input class='date' type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'><span>Fréquence</span></div>	
	<div class='fr'>
	<select name='select' onChange='Allow(this);'>
<?php	
     drawSelectInter("M"); 
?>
	</select>		
	</div></td></tr>
	
	<tr>
	<td class='g'>
	<div class='fl'><span>Mesure</span></div>	
	<div class='fr'>
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
	</div></td></tr>
		
	<tr><td>
	    <table style='height:100%; width:100%;'>
	    <tr>
		<td style='width:95px'>Station</td>
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
        </td></tr></table>
	</td></tr>

	<tr>
	<td class='g' style='height:20px;'>
	<div class='f' style='height:20px;'>	
	<input type='submit' class='g'>	
	</div>
	</td></tr>
	
	</table>
	</form>	
	
<!-- End DrawMenu Module -->
	
<?php } ?>	

