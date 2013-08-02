<?php

function checkSelect($select,$menu)
    {$interval  = $_SESSION['MenuInterval'];
    $iselect = selectIndex($interval['opt'],$select); 
    $selected = min($iselect,$interval[$menu]);
    return  $interval['opt'][$selected][0];        
    }
function selectIndex($opt,$select)
    {$max = count($opt);
    for($i = 0; $i < $max;$i++)
        if($select == $opt[$i][0])return $i;
    return 1;    
    }
function maxIndexMenu($menu)
    {$interval  = $_SESSION['MenuInterval'];
    return $interval[$menu];
    }
function drawSelectInter($menu)
    {$interval  = $_SESSION['MenuInterval'];
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
function chkDates($date0,$date1,$interval,$inter)
    {$txt = explode("/",$date1);
    $date_end = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
    $date_end = min($date_end,time());
    $txt = explode("/",$date0);
    $date_beg = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]); 
    $date_beg =	min($date_beg,$date_end);

    switch($interval)
        {case '1week': $date_beg = min($date_beg,$date_end - 26*7*24*60*60);
                        break;
         case '1day': $date_beg = min($date_beg,$date_end - 30*24*60*60);
                        break;
         case '3hours': $date_beg = min($date_beg,$date_end - 15*24*60*60);
                        break;
         case '30min': $date_beg = min($date_beg,$date_end - 49*60*60);
                        break;
         case  'max':  $date_beg = min($date_beg,$date_end - 12*60*60);
                        break;
        }
        
    // 1024 max théorique
    $n_mesure = min(512,($date_end-$date_beg)/($inter));
    $date_beg = max($date_beg,($date_end - $n_mesure*$inter));
    
    $_SESSION['datebeg'] = date("d/m/Y",$date_beg); 
    $_SESSION['dateend'] = date("d/m/Y",$date_end); 
    if($interval == '1week')$date_beg -= idate('w',$date_beg)*24*60*60;  // -> dimanche    
    if($interval == '1day')$date_beg -= 24*60*60;  
    $_SESSION['date_beg'] = $date_beg;
    $_SESSION['date_end'] = $date_end; 
    
    }    
function drawLogoutBack()
	{$stationId = $_SESSION['stationId'];
?>
<!-- drawLogoutBack -->
<table style='margin:auto; '>
	<tr>
	<td>
    <script  type="text/javascript">
        document.write('<form method=\'post\' action='+'graphiques.php?'+writeSize()+'>');
    </script>
		<input type='submit' class='submit' value="Graphiques d'une station" />
	</form>
	</td>
	
	<td>
    <script  type="text/javascript">
        <?php echo("stationNum = \"$stationId\";\n"); ?>
        document.write('<form method=\'post\' action='+'modules.php?'+writeSize()+'&stationNum='+stationNum+'>');
    </script>	
	<input type='submit' class='submit' value="Modules d'une station"  />
	</form>
	</td>
 
    <td>
    <script  type="text/javascript">
        document.write('<form method=\'post\' action='+'compareALL.php?'+writeSize()+'>');
    </script>
		<input type='submit' class='submit' value="Comparaison de stations" />
	</form>
	</td>
	
	<td>
    <script  type="text/javascript">
        document.write('<form method=\'post\' action='+'iconesExt.php?'+writeSize()+'>');
    </script>
	<input type='submit' class='submit' value="Menu principal"/>
	</form>
	</td>
	
	<td>
	<form action='logout.php' method='post'>
	<input type='submit' class='submit' value='Logout' style='color:#700; ' />	
	</form>	
	</td>

	<td style='font-size:12px;'>
    <?php //if(isset($_SESSION['width']))echo("size:{$_SESSION['width']} x {$_SESSION['height']}");?>
	</td>
	</tr>
	</table>
	    
<?php if($_SERVER['SERVER_NAME'] != 'turlututu')return; ?>
        <table style='margin:auto; font-size:11px;'>
        <tr><td>	
	    <a href='http://www.000webhost.com/' target='_blank' ><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='15'/></a>
	    </td></tr>
	    </table>    
        <table style='margin:auto; font-size:11px; display:none;'>	    
        <tr><td>
        <script src='http://www.counter160.com/js.js?img=15'></script>
        <br>
        <a href='http://www.000webhost.com'>
        <img src='http://www.counter160.com/images/15/left.png' alt='Free web hosting' style='border:0px;'>
        </a>
        <a href='http://www.hosting24.com'>
        <img alt='Web hosting' src='http://www.counter160.com/images/15/right.png' style='border:0px;' >
        </a>
        </td>
	    </tr>
	    </table>   
<?php
	}
/* -- DrawMenuStation ************************************************************************* */
function drawMenuStation($h = '')
	{
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	$stationId = $_SESSION['stationId'];
	$mydevices = $_SESSION['mydevices']; 
    $num = $mydevices['num'];
    $selectMesures = $_SESSION['selectMesures'];
    
?>	

    <script  type="text/javascript">
        document.write('<form method=\'post\' action='+'graphiques.php?'+writeSize()+'>');
    </script>

    <table class='G' style="height:<?php echo $h.';' ?>">
	<tr>
	<td class='g' style='height:3px'>
	<div class='f' style='height:3px'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px;  vertical-align:bottom;'>
    <div class='f' style='height:30px; '>Graphiques d&#39;une station
    </div></td></tr>	

	<tr>
	<td class='g'>
	<div class='fl'>Début</div>
	<div class='fr'>
    <input class="date" id="id_date0" type="text" name="date0" value='<?php echo($datebeg); ?>'  onclick="ds_sh(this,0);">
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'>Fin</div>	
	<div class='fr'>
	<input class='date' id='id_date1'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>
	<tr>
	<td class='g'>
	<div class='fl'>Fréquence</div>	
	<div class='fr'>
	<select name='select'>
<?php	
     drawSelectInter("G"); 
?>
	</select>		
	</div></td></tr>
		
	<tr>
	<td class='g' style ='height:10px;'>
	<div class='f' style ='height:10px;'>
	</div></td></tr>

	<tr><td>
	    <table style='height:100%; width:100%;'>
	    <tr>
		<td class='l'>
		    <?php
		    $select = array("Temp","Hum","CO2","Pressure","Noise");
		  	echo("<table class='chk'>\n");
		    for($i = 0;$i < 5;$i++)
			    {if($selectMesures[$i])
				    echo("<tr><td><input  type='checkbox' name='smesure[]' value='$i' checked='checked'> $select[$i] </td></tr>\n");
			    else
			    	echo("<tr><td ><input  type='checkbox' name='smesure[]' value='$i'> $select[$i] </td></tr>\n");		
			    }
		    echo("</table>\n");	  
		    ?>
		</td>
        <td>			
            <?php
            echo("<table class='chk'>\n");
            for($i = 0;$i < $num;$i++)
                {$stat = $mydevices[$i]['station_name'];
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
	<!--<div.clear></div>-->
	</td></tr>
	</table>
	</form>	
	
<?php	
	}
/* drawMenuCompare ******************************************************************************/
function drawMenuCompare($h ='')
	{
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	$view = $_SESSION['viewCompare'];
	$selectMesure = $_SESSION['selectMesureCompare'];
	$mydevices = $_SESSION['mydevices']; 
    $num = $mydevices['num'];
?>
    <script  type="text/javascript">
        document.write('<form method=\'post\' action='+'compareALL.php?'+writeSize()+'>');
    </script>
    
    <table class='G'  style="height:<?php echo $h.';' ?>">
	<tr>
	<td class='g' style='height:3px'>
	<div class='f' style='height:3px'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px;  vertical-align:bottom;'>
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
	<select name='select'>
<?php	
     drawSelectInter("C"); 
?>
	</select>		
	</div></td></tr>

	<tr>
	<td class='g' style ='height:10px;'>
	<div class='f' style ='height:10px;'></div>	<!-- contenait selectMesure -->
	</td></tr>
		
<?php
        $txt = "<select name='selectMsesure'>";
        if($selectMesure == 'T')
            $txt .="<option value='T' selected='selected'> T° </option>
    		    <option value='H'> H % </option>;
    		    <option value='P'> P mb </option>";
        else if($selectMesure == 'H')
            $txt .="<option value='T'> T° </option>
    		    <option value='H' selected='selected'> H % </option>;
    		    <option value='P'> P mb </option>";
        else
            $txt .="<option value='T'> T° </option>
    		    <option value='H'> H % </option>;
    		    <option value='P' selected='selected'> P mb </option>";
    	$txt .= "</select>";
?>
 			

	<tr><td>
	    <table style='height:100%; width:100%;'>
	    <tr>
		<!--<td class='l'>Station</td>-->
		<td class='l'><?php echo("$txt");?></td>
        <td>			
<?php
		echo("<table class='chk'>\n");
		for($i = 0;$i < $num;$i++)
			{$stat = $mydevices[$i]['station_name'];
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
	<!--<span.clear></span>	-->
	</div>
	</td></tr>
	</table>
	</form>
<!-- End DrawMenu Compare -->
<?php
	}
function drawCharts($order='G')
	{
    require_once 'calendrier.php';    
	$menu = array (
            'G' => array ('drawMenuCompare','drawMenuStation'),
            'C' => array ('drawMenuStation','drawMenuCompare'),
            'M' => array ('drawMenuCompare','drawMenuModules'),
            );
    if($_SESSION['Ipad'])
        $hh = 290; 
    else
	    $hh = 310;
    $h = $hh . 'px';
    $h1 = $hh+2 .'px';            
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
    $menu[$order][0]($h1);
	echo("
	</td>
		<td  style='padding:0px; vertical-align:bottom; width:100%;'>
		<div id='chart0' class='chart' style='height:$h'></div></td>
	 </tr>
	 <tr>
	 <td style='padding:0px; vertical-align:bottom;'>
	 ");
	$menu[$order][1]($h1);
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
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
    $stationNum = $_SESSION['stationId']; 
    $mydevices = $_SESSION['mydevices']; 
    $num = $mydevices['num'];
    $stationName = $mydevices[$stationNum]['station_name'];
    $selectMesure = $_SESSION['selectMesureModule'];
    $viewModules = $_SESSION['viewModules'];
    $view = $viewModules[$stationNum];
?>	  
    <script  type="text/javascript">
        <?php echo("stationNum = \"$stationNum\";\n"); ?>
        document.write('<form method=\'post\' action='+'modules.php?'+writeSize()+'&stationNum='+stationNum+'>');
    </script>

  <table class='G'  style="height:<?php echo $h.';' ?>">
	<tr>
	<td class='g' style='height:3px'>
	<div class='f' style='height:3px'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px;  vertical-align:bottom;'>
    <div class='f' style='height:30px; '>Modules d'une station
    </div></td></tr>	

	<tr>
	<td class='g'>
	<div class='fl'><span>Station</span></div>	
	<div class='fr'>
		<select name='selectStation'>            
 		<?php
		for($i = 0;$i < $num;$i++)
			{$stat = $mydevices[$i]['station_name'];
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
	<select name='select'>
<?php	
     drawSelectInter("M"); 
?>
	</select>		
	</div></td></tr>
	
	<tr>
	<td class='g'  style ='height:5px;'>
	<!--<div class='fl'><span>Mesure</span></div>-->	
	<div class='fl' style ='height:5px;'></div>	
	<div class='fr' style ='height:5px;'>
<?php
        $txt = "<select name='selectMsesure'>";
        if($selectMesure == 'T')
            $txt .= "<option value='T' selected='selected'> T° </option>
    		    <option value='H'  > H % </option>
    		    <option value='C'  > CO2 </option>";
        else if($selectMesure == 'H')
            $txt .= "<option value='T'> T° </option>
    		    <option value='H'  selected='selected'> H % </option>
    		    <option value='C'  > CO2 </option>  "; 
        else
            $txt .="<option value='T'> T° </option>
    		    <option value='H' > H % </option>
    		    <option value='C'  selected='selected'  > CO2 </option>"; 
    	$txt .= "</select>";	    
                
?>
				
	</div></td></tr>
		
	<tr><td>
	    <table style='height:100%; width:100%;'>
	    <tr>
		<!--<td class='l'>Station</td>-->
		<td class='l'><?php echo ("$txt"); ?></td>
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
	<!--<span.clear></span>	-->
	</div>
	</td></tr>
	</table>
	</form>	

	
<!-- End DrawMenu Module -->
	
<?php } ?>	

