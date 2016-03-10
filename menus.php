<?php
require_once 'Config.php';
require_once 'initClient.php';
require_once 'translate.php';

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
    $n = count($txt);
    if(count($txt) > 1)
        $date_end = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
    else
        $date_end = $date1;
    $date_end = min($date_end,time());
    
    $txt = explode("/",$date0);
    $n = count($txt);
    if(count($txt) > 1)
        $date_beg = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]); 
    else
        $date_beg = $date0;
    $date_beg =	min($date_beg,$date_end);
    $opt = $_SESSION['MenuInterval']['opt']; 
    
    switch($interval)
        {case '1week': $date_beg = min($date_beg,$date_end - $opt[0][3]);
                        break;
         case '1day': $date_beg = min($date_beg,$date_end - $opt[1][3]);
                        break;
         case '3hours': $date_beg = min($date_beg,$date_end - $opt[2][3]);
                        break;
         case '1hour': $date_beg = min($date_beg,$date_end - $opt[3][3]);
                        break;
         case '30min': $date_beg = min($date_beg,$date_end - $opt[4][3]);
                        break;
         case  'max':  $date_beg = min($date_beg,$date_end - $opt[5][3]);
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
	    <form method='post' action='iconesExt.php'>
    	<input type='submit' class='submit' value='<?php echo tr("Menu principal");?>'/>
    	</form>
	</td>
	<td>
	<!-- <input type='submit' class='submit' value='Show Log' style='color:#080;' onClick='MakeRequestLog();' />    -->
	<input type='submit' class='submit' value='log' style='color:#080;' onClick='window.open("logsession.php","_blank","scrollbars=yes,status=0,width=800");' />    
	</td>	
	<td>
	<input type='submit' class='submit' value='Readme' style='color:#000;' onClick='window.open("README.html","_blank","scrollbars=yes,status=0,width=800");' />    
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
	global $refreshInterval;
	$time_left = $_SESSION['timeToken'] + $_SESSION['expires_in'] - time() -5*60;
    if(isset($refreshInterval) && $refreshInterval)$time_left = min($time_left,$refreshInterval);
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
            var x = $(document).width();
            var y = $(document).height();
            var xx = $(window).width();           
            var yy = $(window).height();           
            size = 'Document:'+x+' x '+y+' Window:'+xx+' x'+yy;
            var dt = Math.round(refresh - (t1-t0)/1000);
            //if(!isMobile())
            document.getElementById("timer").innerHTML='Time:'+t+'&nbsp;&nbsp;'+size+'&nbsp;&nbsp;Reload in: '+dt+'s';
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
    //$selectMesures = $_SESSION['selectMesures'];
    $selectMesure = $_SESSION['selectMesureHist'];
    $hist = $_SESSION['hist']; 
?>	
     <form method='post' action='compareHIST.php'>
<?php
    $titre = tr("Comparaison année");
    if($charts == 0)
        {echo("<table class='G' id='hist' style=\"height:$h;\">");
        echo("<tr><td class='title'><div class='f'>");
        echo "$titre </div></td></tr>";
        }
    else 
        {
?>   
        <table class="G"  id="hist">
        <tr><td class="title">
        <div class="f" id = "dhist">
        <?php echo $titre;?>
        </div></td></tr>
        <script>
        var h = heightChart() + 2;
        $("#hist").css("height", h);
        var h2 = (heightChart()-300)/4 + 20;
        $("#dhist").css("height",h2);
        </script>   
<?php            
         }                
    if($charts > 1)
        echo("
            <script>
            $(\"#hist\").css( \"background-color\", \"#f0f0f0\" );
            </script>        
        ");
?>              


	<tr>
	<td class='g'>
	<div class='fl'><span>Station</span></div>	
	<div class='fr'>
		           
 		<?php
 		echo "<select id=\"el00h\" name='selectStation'> ";
		for($i = 0;$i < $num;$i++)
			{$stat = $mydevices[$i]['station_name'];
			$arr = explode(" ",$stat);
			$stat = substr($arr[0],0,15);
			if($i == $_SESSION['stationId'])
			    echo("<option value='$i' selected='selected'>$stat</option>");
            else
			    echo("<option value='$i'>$stat</option>");            
			}
		echo "</select>";
    	?>
            
	</div></td></tr>

<script type="text/javascript">
var selectmenu=document.getElementById("el00h")
selectmenu.onchange=function()
    {
    top.location.href='compareHIST.php?stationNum='+this.options[this.selectedIndex].value;
    }
</script>
	
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Début");?></div>
	<div class='fr'>
    <input class="date"  type="text" name="date0" value='<?php echo($datebeg); ?>'  onclick="ds_sh(this,0);">
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fin");?></div>	
	<div class='fr'>
	<input class='date'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
	</div></td></tr>
	
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fréquence");?></div>	
	<div class='fr'>
	<select name='select'>
<?php	
     drawSelectInter("H");    
?>
	</select>		
	</div></td></tr>

	<tr><td class='g'><div class='f'>
	</div></td></tr>
		
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Mesure");?></div>	
	<div class='fr'>
<?php
        $cu = ' '.tu();
        $pl = tr(Pluie);
        $txt = "<select name='selectMsesure'>";
        if(!isset($mydevices[$stationId]['modules'][10]))
            { if($selectMesure == 'R')$selectMesure = 'T';
            if($selectMesure == 'T')
                $txt .="<option value='T' selected='selected'> T </option>
                    <option value='H'> H </option>
                    <option value='h'> H int </option>";
            else if($selectMesure == 'H')
                $txt .="<option value='T'> T </option>
                    <option value='H' selected='selected'> H </option>
                    <option value='h'> H int </option>";
            else if($selectMesure == 'h')
                $txt .="<option value='T'> T </option>
                    <option value='H'> H </option>
    		         <option value='h'  selected='selected'> H int</option>";
    	    }
    	else
    	    {
             if($selectMesure == 'T')
                $txt .="<option value='T' selected='selected'> T </option>
                    <option value='H'> H </option>
                    <option value='h'> H int </option>
                    <option value='R'> $pl  </option>";
            else if($selectMesure == 'H')
                $txt .="<option value='T'> T </option>
                    <option value='H' selected='selected'> H </option>
                    <option value='h'> H int </option>
                    <option value='R'> $pl </option>";
            else if($selectMesure == 'h')
                $txt .="<option value='T'> T </option>
                    <option value='H'> H </option>
    		        <option value='h'  selected='selected'> H int </option>
    		        <option value='R'> $pl </option>";
            else if($selectMesure == 'R')
                $txt .="<option value='T'> T </option>
                    <option value='H'> H </option>
    		        <option value='h'> H int </option>
    		        <option value='R'  selected='selected'> $pl </option>"; 		        
    	    }
    	
    	echo $txt;
?>
 	</select>		
	</div></td></tr>

	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Décalage");?></div>	
	<div class='fr'>
	<select name='hist'>
<?php
    $t1 = '1 '.tr('mois');
    $t6 = '6 '.tr('mois');
    $t12 = '12 '.tr('mois');
    $t24 = '24 '.tr('mois');
    $t36 = '36 '.tr('mois');
    
    if($hist == 1)
        echo("<option value='1' selected='selected' >$t1</option>
        <option value='6' '>$t6</option>
        <option value='12' >$t12</option>
        <option value='24' >$t24</option>
        <option value='36' >$t36</option>
        ");
    else if($hist == 6)
        echo("<option value='1'>$t1</option>
        <option value='6' selected='selected'>$t6</option>
        <option value='12' >$t12</option>
        <option value='24' >$t24</option>
        <option value='36' >$t36</option>
         ");
    else if($hist == 12)
        echo("<option value='1'>$t1</option>
        <option value='6' >$t6</option>
        <option value='12'selected='selected' >$t12</option>
        <option value='24' >$t24</option>
        <option value='36' >$t36</option>        
        ");
    else if($hist == 24)
        echo("<option value='1'>$t1</option>
        <option value='6' >$t6</option>
        <option value='12' >$t12</option>
        <option value='24' selected='selected' >$t24</option>
        <option value='36' >$t36</option>        
        ");
    else
        echo("<option value='1'>$t1</option>
        <option value='6'>$t6</option>
        <option value='12' >$t12</option>
        <option value='24' >$t24</option>
        <option value='36' selected='selected'>$t36</option>        
        ");
    
?>
	</select>		
	</div></td></tr>
	
	
	<tr><td style='height:50%;'>
    </td></tr> 
    
	<tr>
	<td class='g'  style='height:20px;'>
	<div class='f'  style='height:20px;'>	
	<input type='submit' value='<?php echo tr("Go");?>' class='g'>	
	</div></td></tr>
	
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
    $titre = tr("Graphiques d&#39;une station");
    if($charts == 0) // iconesExt
        {echo("<table class='G' id='graphics' style=\"height:$h;\">");
        echo("<tr><td class='title'><div class='f'>");
        echo " $titre</div></td></tr>";
        }
    else 
        {
?>   
        <table class="G"  id="graphics">
        <tr><td class="title">
        <div class="f" id = "dgraphics">
        <?php echo $titre;?>
        </div></td></tr>
        <script>
        var h = heightChart() + 2;
        $("#graphics").css("height", h);
        var h2 = (heightChart()-300)/4 + 20;
        $("#dgraphics").css("height",h2);
        </script>   
<?php            
         }                  
    if($charts > 1)
        echo("
            <script>
            $(\"#graphics\").css( \"background-color\", \"#f0f0f0\" );
            </script>        
        ");
?>              

	<tr>
	<td class='g'>
	<div class='fl'><span>Station</span></div>	
	<div class='fr'>
		<select id="el00s" name='selectStation'>            
 		<?php
		for($i = 0;$i < $num;$i++)
			{$stat = $mydevices[$i]['station_name'];
			$arr = explode(" ",$stat);
			$stat = substr($arr[0],0,15);
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
    <input class="date"  type="text" name="date0" value='<?php echo($datebeg); ?>'  onclick="ds_sh(this,0);">
    </div></td></tr>
      
	<tr>
	<td class='g'>
	<div class='fl'><?php echo tr("Fin");?></div>	
	<div class='fr'>
	<input class='date'  type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);' >
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
	    <td>
	    <table style='height:100%; width:100%;'>
	    <tr>
		<td>
		    <?php
		    $select = array("Temp",tr("Humidité"),"CO2",tr("Pression"),tr("Bruit"));
		  	echo("<table class='chk' style='margin:auto;'>\n");
		    for($i = 0;$i < 5;$i++)
			    {if($selectMesures[$i])
				    echo("<tr><td><input  type='checkbox' name='smesure[]' value='$i' checked='checked'> $select[$i] </td></tr>\n");
			    else
			    	echo("<tr><td ><input  type='checkbox' name='smesure[]' value='$i'> $select[$i] </td></tr>\n");		
			    }
		    echo("</table>\n");	  
?>

		</td>
        <td></td>
        </tr></table>     
	</td></tr>

	<tr>
	<td class='g'  style='height:20px;'>
	<div class='f'  style='height:20px;'>	
	<input type='submit' value='<?php echo tr("Go");?>' class='g'>	
	<input type='button' value='&#8592;' class='g' onclick="top.location.href='graphiques.php?hist=-1'">	
	<input type='button' value='x' class='g' onclick="top.location.href='graphiques.php?hist=0'">	
	<input type='button' value='&#8594;' class='g' onclick="top.location.href='graphiques.php?hist=1'">	
	<input type='button' value='o' class='g' onclick="top.location.href='graphiques.php?hist=-2'">	
	</div></td></tr>
	
	</table>
	</form>	
	
<?php	
	}
/* drawMenuCompare ******************************************************************************/
function drawMenuCompare($h ='',$charts = 0)
	{
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
	$view = $_SESSION['viewCompare'];
	$selectMesure = $_SESSION['selectMesureCompare'];
	$mydevices = $_SESSION['mydevices']; 
    $num = $mydevices['num'];
?>
    <form method='post' action='compareALL.php'>
<?php
    $titre = tr("Comparaison de stations");
    if($charts == 0)
        {echo("<table class='G' id='compare' style=\"height:$h;\">");
        echo("<tr><td class='title'><div class='f'>");
        echo " $titre</div></td></tr>";
        }
    else
        {
?>   
        <table class="G"  id="compare">
        <tr><td class="title">
        <div class="f" id = "dcompare">
        <?php echo $titre;?>
        </div></td></tr>
        <script>
        var h = heightChart() + 2;
        $("#compare").css("height", h);
        var h2 = (heightChart()-300)/4 + 20;
        $("#dcompare").css("height",h2);
        </script>   
<?php            
         }                
    if($charts > 1)
        echo("
            <script>
            $(\"#compare\").css( \"background-color\", \"#f0f0f0\" );
            </script>        
        ");
?>              
          
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

<?php
        $cu = ' '.tu();
        $txt = "<select name='selectMsesure'>";
        if($selectMesure == 'T')
            $txt .="<option value='T' selected='selected'> T </option>
    		    <option value='H'> H </option>
    		    <option value='P'> Pr </option>";
        else if($selectMesure == 'H')
            $txt .="<option value='T'> T </option>
    		    <option value='H' selected='selected'> H </option>
    		    <option value='P'> Pr </option>";
        else
            $txt .="<option value='T'> T </option>
    		    <option value='H'> H </option>
    		    <option value='P' selected='selected'> Pr </option>";
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
			$stat = substr($arr[0],0,15);
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
	<input type='submit' value='<?php echo tr("Go");?>' class='g'>	
	<!--<span.clear></span>	-->
	</div></td></tr>
	
	</table>
	</form>
<!-- End DrawMenu Compare -->
<?php
	}
function drawCharts($order='G')
	{
    require_once 'calendrier.php'; 
    $h = '300px';
?>    

 	<!-- Invisible table -->
    <table class='ds_box'  id='ds_conclass' style='display: none;' >
    <caption id='id_caption' class='ds_caption'>xxxx</caption>
    <tr><td id='ds_calclass'>aaa</td></tr>
    </table>
	
	<table style='padding:0px; width:100%; margin-bottom:-5px;'>

	<tr>
	<td style='padding:0px; vertical-align:bottom;'>
    <?php $visu = ($order == 'M') ? 2:1; drawMenuModules($h,$visu); ?> 
	</td>
	
		<td  style='padding:0px; vertical-align:bottom; width:100%;'>
		<script>
		    h = heightChart();
            w = widthChart();
		    var txt = "<div id='chart0' class='chart' style='width:"+w+"px; height:"+h;
		    txt += "px;'></div>";
		    document.write(txt);
		</script>
		</td>

	<td style='padding:0px; vertical-align:bottom;'>
    <?php $visu = ($order == 'C') ? 1:0; drawMenuCompare($h,1 + $visu);?>
	</td>
		
	 </tr>
	 <tr>
	 <td style='padding:0px; vertical-align:bottom;'>
    <?php $visu = ($order == 'G') ? 1:0; drawMenuStation($h,1 + $visu);?>
	 </td>
	 
		<td style='padding:0px; vertical-align:bottom; width:100%;'>
		<script>
		    h = heightChart();
            w = widthChart();
		    var txt = "<div id='chart1' class='chart' style='width:"+w+"px; height:"+h;
		    txt += "px;'></div>";
		    document.write(txt);
		</script>
        </td>
	<td style='padding:0px; vertical-align:bottom;'>
    <?php $visu = ($order == 'H') ? 1:0; drawMenuHist($h,1 + $visu);?>
	</td>
	</tr>
	</table>
	
	<?php drawLogoutBack(); ?>
<?php	
	}
/* drawMenuModules ************************************************************************/	
function drawMenuModules($h ='',$charts = 0)
	{
	$datebeg = $_SESSION['datebeg'];
	$dateend = $_SESSION['dateend'];
    $stationNum = $_SESSION['stationId']; 
    $mydevices = $_SESSION['mydevices']; 
    $numStations = $mydevices[$stationNum]['modules']['num'] + 1;
    $num = $mydevices['num']; 
    $nameStations[0] = $mydevices[$stationNum]["module_name"]; 
    $j = 1;
    for($i = 1;$i <= 12 ;$i++) // station et modules
        {if(!isset($mydevices[$stationNum]["modules"][$i-1]))continue;
        $nameStations[$j] = $mydevices[$stationNum]["modules"][$i-1]["module_name"]; 
        ++$j;
        }
    $stationName = $mydevices[$stationNum]['station_name'];
    $selectMesure = $_SESSION['selectMesureModule'];
    $viewModules = $_SESSION['viewModules'];
    $view = $viewModules[$stationNum];
/*<form method='post' action="modules.php?stationNum=<?php echo $stationNum; ?>">*/
?>	

    <form method='post' action="modules.php">

<?php

    $titre =  tr("Modules d&#39;une station"); 
    if($charts == 0)
        {echo("<table class='G' id='modules' style=\"height:$h;\">");
        echo("<tr><td class='title'><div class='f'>");
        echo " $titre</div></td></tr>";
        }
    else
        {;
?>   
        <table class="G"  id="modules">
        <tr><td class="title">
        <div class="f" id = "dmodules">
        <?php echo $titre;?>
        </div></td></tr>
        <script>
        var h = heightChart() + 2;
        $("#modules").css("height", h);
        var h2 = (heightChart()-300)/4 + 20;
        $("#dmodules").css("height",h2);
        </script>   
<?php            
         }                  
    if($charts > 1)
        echo("
            <script>
            $(\"#modules\").css( \"background-color\", \"#f0f0f0\" );
            </script>        
        ");
?>              
         

	<tr>
	<td class='g'>
	<div class='fl'><span>Station</span></div>	
	<div class='fr'>
		<select id="el00" name='selectStation' >            
 		<?php
		for($i = 0;$i < $num;$i++)
			{$stat = $mydevices[$i]['station_name'];
			$arr = explode(" ",$stat);
			$stat = substr($arr[0],0,15);
			if($i == $_SESSION['stationId'])
			    echo("<option value='$i' selected='selected'>$stat</option>");
            else
			    echo("<option value='$i'>$stat</option>");            
			}
    	?>
            </select>
	</div></td></tr>
	
<script type="text/javascript">
var selectmenu=document.getElementById("el00")
selectmenu.onchange=function()
    {top.location.href='modules.php?stationNum='+this.options[this.selectedIndex].value;
    }
</script>
		
	<!--<tr><td class='g'><div class='f'></div></td></tr>-->
	
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

<?php
        $cu = ' '.tu();
        $txt = "<select name='selectMsesure'>";
        if($selectMesure == 'T')
            $txt .= "<option value='T' selected='selected'> T </option>
    		    <option value='H'  > H </option>
    		    <option value='C'  > CO2 </option>";
        else if($selectMesure == 'H')
            $txt .= "<option value='T'> T </option>
    		    <option value='H'  selected='selected'> H </option>
    		    <option value='C'  > CO2 </option>  "; 
        else
            $txt .="<option value='T'> T </option>
    		    <option value='H' > H </option>
    		    <option value='C'  selected='selected'  > CO2 </option>"; 
    	$txt .= "</select>";	    
                
?>
				
	</td></tr>
		
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
			$stat = substr($arr[0],0,15);
			if($view[$i])
				echo("<tr><td><input type='checkbox' name='selectedModules[]' value='$i' checked='checked'> $stat </td></tr>\n");
			else
				echo("<tr><td><input  type='checkbox' name='selectedModules[]' value='$i'> $stat </td></tr>\n");		
			}
		echo("</table>\n");	
?>			
        </td></tr>
        </table>
	</td></tr>

	<tr>
	<td class='g' style='height:20px;'>
	<div class='f' style='height:20px;'>	
	<input type='submit'  value='<?php echo tr("Go");?>' class='g'>	
	<input type='button' value='&#8592;' class='g' onclick="top.location.href='modules.php?hist=-1'">	
	<input type='button' value='x' class='g' onclick="top.location.href='modules.php?hist=0'">	
	<input type='button' value='&#8594;' class='g' onclick="top.location.href='modules.php?hist=1'">	
	<input type='button' value='o' class='g' onclick="top.location.href='modules.php?hist=-2'">	
	
	<!--<span.clear></span>	-->
	</div></td></tr>
	
	</table>
	</form>	

	
<!-- End DrawMenu Module -->
	
<?php } 
?>	

