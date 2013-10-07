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
        $txt = tr($interval['opt'][$i][1]);
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
    $n_mesure = min(1024,($date_end-$date_beg)/($inter));
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
    <script>
    function getXMLHttp()
        {var xmlHttp
        try
            {xmlHttp = new XMLHttpRequest();//Firefox, Opera 8.0+, Safari
            }
        catch(e)
            {try //Internet Explorer
                {xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
                }
            catch(e)
                {try
                    {xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                catch(e)
                    {alert("Your browser does not support AJAX!")
                    return false;
                    }
                }
            }
          return xmlHttp;
        }
    function MakeRequestLog()
        {var xmlHttp = getXMLHttp();
        xmlHttp.onreadystatechange = function()
            {if(xmlHttp.readyState == 4)
                {HandleResponseLog(xmlHttp.responseText);
                }
            }
        xmlHttp.open("GET","logsession.php", true); 
        xmlHttp.send(null);
        }	
    function HandleResponseLog(response)
        {popup = window.open('','Log','titlebar=yes,menubar=no,status=no,scrollbars=yes,location=no,toolbar=no,height=900,width=1000');
        //popup = open(); // nouveau tab
        var tmp = popup.document;
        tmp.write('<html><head><title>Log</title>');
        tmp.write('</head><body>');
        tmp.write('<p>'+response+'</p>');
        tmp.write('</body></html>');
        tmp.close();
        }	
    </script>

<table style='margin:auto;'>
	<tr>
	<td>  
        <form method='post' action='graphiques.php'>
		<input type='submit' class='submit' value='<?php echo tr("Graphiques d&#39;une station");?>' />
	</form>
	</td>
	<td>
	    <form method='post' action="modules.php?stationNum=<?php echo $stationId; ?>">
	    <input type='submit' class='submit' value='<?php echo tr("Modules d&#39;une station");?>'  />
	    </form>
	</td>
    <td>
        <form method='post' action='compareALL.php'>
		<input type='submit' class='submit' value='<?php echo tr("Comparaison de stations");?>' />
	</form>
	</td>
    <td>
        <form method='post' action='compareHIST.php'>
		<input type='submit' class='submit' value='<?php echo tr("Comparaison année");?>' />
	</form>
	</td>
	<td>
	    <form method='post' action='iconesExt.php'>
    	<input type='submit' class='submit' value='<?php echo tr("Menu principal");?>'/>
    	</form>
	</td>
	<td>
	<input type='submit' class='submit' value='Show Log' style='color:#080;' onClick='MakeRequestLog();' />    
	</td>	
	<td>
	<form action='logout.php' method='post'>
	<input type='submit' class='submit' value='<?php echo tr("Quitter");?>' style='color:#900; ' />	
	</form>	
	</td>
	</tr>
</table>
<table style='margin:auto;'>
	<tr>
	<td id='timer' style='font-size:12px; text-align=center;'> 
	<?php 
	$time_left = $_SESSION['timeToken'] + $_SESSION['expires_in'] - time() -5*60;
	$time_left = max($time_left,5);
	?>
        <script>
        <?php echo("var refresh = \"$time_left\";\n");?>
        var Id0 = setInterval(function(){Timer()},1000);
        var Id1 = setInterval(function(){reload()},refresh*1000);
        var d0 = new Date();
        var t0 = d0.getTime();
        function Timer()
            {var d1 = new Date();
            var t = d1.toLocaleTimeString();
            var t1 = d1.getTime();
            var w = window,
            d = document,
            e = d.documentElement,
            g = d.getElementsByTagName('body')[0],
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight|| e.clientHeight|| g.clientHeight;
            size = x+' x '+y;
            var dt = Math.round(refresh - (t1-t0)/1000);
            document.getElementById("timer").innerHTML='Time:'+t+'&nbsp;&nbsp;Window:'+size+'&nbsp;&nbsp;Reload in: '+dt+'s';
            }
        function reload()
            {url = window.location;
            window.open(url,'_self');
            }
        </script>
	</td>
	</tr>
	</table>
	    
<?php
	}

/* -- DrawMenuHist ************************************************************************* */
function drawMenuHist($h = '',$charts = 0)
	{
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	$stationId = $_SESSION['stationId'];
	$mydevices = $_SESSION['mydevices']; 
    $num = $mydevices['num'];
    $selectMesures = $_SESSION['selectMesures'];
?>	
     <form method='post' action='compareHIST.php'>
<?php
    if($charts == 0)
        echo("<table class='G' style=\"height:$h;\">");
 else
        {
        echo("<script>
        var h = heightChart() + 2;
        var txt = '<table class=\"G\" style=\"height:'+h+'px;\">';
        document.write(txt);
        </script>
        ");
        }
?>        
	<tr>
	<td class='g' style='height:3px;'>
	<div class='f' style='height:3px;'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px;  vertical-align:bottom;'>
    <div class='f' style='height:30px;'><?php echo tr("Comparaison année");?>
    </div></td></tr>	

	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Début");?></div>
	<div class='fr'>
    <input class="date" id="id_date0" type="text" name="date0" value='<?php echo($datebeg); ?>'  onclick="ds_sh(this,0);">
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fin");?></div>	
	<div class='fr'>
	<input class='date' id='id_date1'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>
	
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fréquence");?></div>	
	<div class='fr'>
	<select name='select'>
<?php	
     drawSelectInter("H"); 
     $hist = $_SESSION['hist']; 
?>
	</select>		
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Décalage");?></div>	
	<div class='fr'>
	<select name='hist'>
<?php
    $t6 = '6 '.tr('mois');
    $t12 = '12 '.tr('mois');
    if($hist == 6)
        echo("<option value='6' selected='selected'>$t6</option>
        <option value='12' >$t12</option>");
    else
        echo("<option value='6'>$t6</option>
        <option value='12' selected='selected'>$t12</option>");
    
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
	    <td></td>

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
	<input type='submit' value='<?php echo tr("Envoyer");?>' class='g'>	
	</div>
	<!--<div.clear></div>-->
	</td></tr>
	</table>
	</form>	
	
<?php	
	}
/* -- DrawMenuStation ************************************************************************* */
function drawMenuStation($h = '',$charts = 0)
	{
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	$stationId = $_SESSION['stationId'];
	$mydevices = $_SESSION['mydevices']; 
    $num = $mydevices['num'];
    $selectMesures = $_SESSION['selectMesures'];
?>	
     <form method='post' action='graphiques.php'>
<?php
    if($charts == 0)
        echo("<table class='G' style=\"height:$h;\">");
 else
        {
        echo("<script>
        var h = heightChart() + 2;
        var txt = '<table class=\"G\" style=\"height:'+h+'px;\">';
        document.write(txt);
        </script>
        ");
        }
?>        
	<tr>
	<td class='g' style='height:3px;'>
	<div class='f' style='height:3px;'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px;  vertical-align:bottom;'>
    <div class='f' style='height:30px;'><?php echo tr("Graphiques d'une station");?>
    </div></td></tr>	

	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Début");?></div>
	<div class='fr'>
    <input class="date" id="id_date0" type="text" name="date0" value='<?php echo($datebeg); ?>'  onclick="ds_sh(this,0);">
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fin");?></div>	
	<div class='fr'>
	<input class='date' id='id_date1'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fréquence");?></div>	
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
		    $select = array("Temp",tr("Humidité"),"CO2",tr("Pression"),tr("Bruit"));
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
	<input type='submit' value='<?php echo tr("Envoyer");?>' class='g'>	
	</div>
	<!--<div.clear></div>-->
	</td></tr>
	</table>
	</form>	
	
<?php	
	}
/* drawMenuCompare ******************************************************************************/
function drawMenuCompare($h ='',$charts = 0)
	{global $Temperature_unit;
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	$view = $_SESSION['viewCompare'];
	$selectMesure = $_SESSION['selectMesureCompare'];
	$mydevices = $_SESSION['mydevices']; 
    $num = $mydevices['num'];
?>
    <form method='post' action='compareALL.php'>
 <?php
    if($charts == 0)
        echo("<table class='G' style=\"height:$h;\">");
 else
        {
        echo("<script>
        var h = heightChart() + 2;
        var txt = '<table class=\"G\" style=\"height:'+h+'px;\">';
        document.write(txt);
        </script>
        ");
        }
?>        
   
	<tr>
	<td class='g' style='height:3px;'>
	<div class='f' style='height:3px;'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px; vertical-align:bottom;'>
    <div class='f' style='height:30px;'><?php echo tr("Comparaison de stations");?>
    </div></td></tr>	

	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Début");?></div>
	<div class='fr'>
	<input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'>
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fin");?></div>	
	<div class='fr'>
	<input class='date' type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'><span><?php echo tr("Fréquence");?></span></div>	
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
        $cu = $Temperature_unit ? '°':' F';
        $txt = "<select name='selectMsesure'>";
        if($selectMesure == 'T')
            $txt .="<option value='T' selected='selected'> T$cu </option>
    		    <option value='H'> H % </option>;
    		    <option value='P'> P mb </option>";
        else if($selectMesure == 'H')
            $txt .="<option value='T'> T$cu </option>
    		    <option value='H' selected='selected'> H % </option>;
    		    <option value='P'> P mb </option>";
        else
            $txt .="<option value='T'> T$cu </option>
    		    <option value='H'> H % </option>;
    		    <option value='P' selected='selected'> P mb </option>";
    	$txt .= "</select>";
?>
 			

	<tr><td>
	    <table style='height:100%; width:100%;'>
	    <tr>
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
	<input type='submit' value='<?php echo tr("Envoyer");?>' class='g'>	
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
            'C' => array ('drawMenuHist','drawMenuCompare'),
            'M' => array ('drawMenuCompare','drawMenuModules'),
            'H' => array ('drawMenuCompare','drawMenuHist'),
            );
?>    

 	<!-- Invisible table -->
    <table class='ds_box'  id='ds_conclass' style='display: none;' >
    <caption id='id_caption' class='ds_caption'>xxxx</caption>
    <tr><td id='ds_calclass'>aaa</td></tr>
    </table>
	
	<table style='padding:0px; width:100%; margin-bottom:-5px;'>
	<tr>
	<td style='padding:0px; vertical-align:bottom;'>
	
    <?php $menu[$order][0](0,1);?>
	
	</td>
		<td  style='padding:0px; vertical-align:bottom; width:100%;'>
		<script>
		    h = heightChart();
		    w = widthChart();
		    var txt = "<div id='chart0' class='chart' style='width:"+w+"px; height:"+h;
		    txt += "px;'></div></td>";
		    document.write(txt);
		</script>
		
	 </tr>
	 <tr>
	 <td style='padding:0px; vertical-align:bottom;'>
	
	<?php $menu[$order][1](0,1);?>

	 </td>
		<td style='padding:0px; vertical-align:bottom; width:100%;'>
		<script>
		    h = heightChart();
		    w = widthChart();
		    var txt = "<div id='chart1' class='chart' style='width:"+w+"px; height:"+h;
		    txt += "px;'></div></td>";
		    document.write(txt);
		</script>

	</tr>
	</table>
	
	<?php drawLogoutBack(); ?>
<?php	
	}
/* drawMenuModules ************************************************************************/	
function drawMenuModules($h ='',$charts = 0)
	{global $Temperature_unit;
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
    $stationNum = $_SESSION['stationId']; 
    $mydevices = $_SESSION['mydevices']; 
    $numStations = $mydevices[$stationNum]['modules']['num'] + 1;
    $num = $mydevices['num'];
    $nameStations[0] = $mydevices[$stationNum]["module_name"]; 
    for($i = 1;$i < $numStations;$i++) // station et modules
        $nameStations[$i] = $mydevices[$stationNum]["modules"][$i-1]["module_name"];   
    $stationName = $mydevices[$stationNum]['station_name'];
    $selectMesure = $_SESSION['selectMesureModule'];
    $viewModules = $_SESSION['viewModules'];
    $view = $viewModules[$stationNum];
?>	

    <form method='post' action="modules.php?stationNum=<?php echo $stationNum; ?>">
<?php
    if($charts == 0)
        echo("<table class='G' style=\"height:$h;\">");
 else
        {
        echo("<script>
        var h = heightChart() + 2;
        var txt = '<table class=\"G\" style=\"height:'+h+'px;\">';
        document.write(txt);
        </script>
        ");
        }
?>        
	<tr>
	<td class='g' style='height:3px;'>
	<div class='f' style='height:3px;'>
	</div></td></tr>
    
    <tr>
    <td class='title' style='height:30px; vertical-align:bottom;'>
    <div class='f' style='height:30px;'><?php echo tr("Modules d'une station");?>
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
	<div class='fl'><?php echo tr("Début");?></div>
	<div class='fr'>
	<input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'>
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fin");?></div>	
	<div class='fr'>
	<input class='date' type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'><span><?php echo tr("Fréquence");?></span></div>	
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
        $cu = $Temperature_unit ? '°':' F';
        $txt = "<select name='selectMsesure'>";
        if($selectMesure == 'T')
            $txt .= "<option value='T' selected='selected'> T$cu </option>
    		    <option value='H'  > H % </option>
    		    <option value='C'  > CO2 </option>";
        else if($selectMesure == 'H')
            $txt .= "<option value='T'> T$cu </option>
    		    <option value='H'  selected='selected'> H % </option>
    		    <option value='C'  > CO2 </option>  "; 
        else
            $txt .="<option value='T'> T$cu </option>
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
	<input type='submit'  value='<?php echo tr("Envoyer");?>' class='g'>	
	<!--<span.clear></span>	-->
	</div>
	</td></tr>
	</table>
	</form>	

	
<!-- End DrawMenu Module -->
	
<?php } 
?>	

