<?php

$jour = array("Dim","Lun","Mar","Mer","Jeu","Ven","Sam"); 

function refreshToken()
    {global $client_id,$client_secret,$timezone;
    if(!isset($_SESSION['refresh_token']))
        {logMsg('NO refreshtoken');
        logout();
        }
    date_default_timezone_set($timezone);
    $token_url = "https://api.netatmo.net/oauth2/token";
    $postdata = http_build_query(array(
         							'grant_type' => "refresh_token",
            						'refresh_token' => $_SESSION['refresh_token'],
            						'client_id' => $client_id,
            						'client_secret' => $client_secret
        							));
    $opts = array('http' => array(
        							'method'  => 'POST',
        							'header'  => 'Content-type: application/x-www-form-urlencoded;charset=UTF-8',
        							'content' => $postdata
    								));
    $context  = stream_context_create($opts);
    @$response = file_get_contents($token_url, false, $context);
    if(!$response)return -1;
    $params = null;
    $params = json_decode($response, true);
	$access_token = $params['access_token'];
	$refresh_token = $params['refresh_token'];
	$expires_in = $params['expires_in'];
	$_SESSION['refresh_token'] = $refresh_token;		
	$_SESSION['timeToken'] = time();
	$_SESSION['expires_in'] = $expires_in;
	$client = new NAApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
	$client->setVariable("client_id", $client_id);
	$client->setVariable("client_secret", $client_secret);
	//if(isset($_SESSION['code']))$client->setVariable("code", $_SESSION['code']);
	logMsg("Refresh token success:$refresh_token ");
	saveTokenCookie($refresh_token);
	$_SESSION['client'] = $client;
	return 1;
	}
function checkToken()
    {if(isset($_SESSION['timeToken']))
		{$time_left = $_SESSION['timeToken'] + $_SESSION['expires_in'] - time();
		//logMsg("timeLeft:$time_left");
		if($time_left < 5*60) 
			refreshToken();
		return $time_left;
		}
	else return -1;
    }	
function init($numStations)
    {if(!isset($_SESSION['init']))
        {$_SESSION['init'] = 1;
        $_SESSION['timeLoad'] = time();
        $_SESSION['stationId'] = 0;
        $_SESSION['stationIdP'] = -1;
        $_SESSION['selectedInter'] = '1day';
        $_SESSION['selectedInterP'] = 'x';
        $_SESSION['date_begP'] = 0;
        $_SESSION['date_endP'] = 0;  
        $_SESSION['dateend'] = date("d/m/Y",mktime(date("H"), date("i"), 0, date('m') , date('d'),date('y')));
        $_SESSION['datebeg'] = date("d/m/Y",mktime(date("H"), date("i"), 0, date('m') , date('d'),date('y')));
        $_SESSION['path'] = dirname($_SERVER['PHP_SELF']);
        $client = $_SESSION['client'];     
        $MenuInterval = array ( "G" => 5,
                            "C"  => 1,
                            "M"  => 4,
                            "H"  => 1,                             
                            "opt" => array (
                                        0 => array ('1week','1 semaine',7*24*60*60,26*7*24*60*60),
                                        1 => array ('1day','1 journée',24*60*60,30*24*60*60),
                                        2 => array ('3hours','3 heures',3*60*60,15*24*60*60),
                                        3 => array ('1hour','1 heure',60*60,4*24*60*60),
                                        4 => array ('30min','30 minutes',30*60,2*24*60*60),
                                        5 => array ('max','5 minutes',5*60,24*60*60)
                                        )
                                );
        $_SESSION['MenuInterval'] = $MenuInterval;  
        $_SESSION['date_end'] = time();
        $_SESSION['date_beg'] = time() - $MenuInterval['opt'][1][3];
        
        $selectMesures[0] = 1;
        for($i = 1 ;$i < 5; $i++)
            $selectMesures[$i] = 0;
        $_SESSION['selectMesures'] = $selectMesures;    
        for($i = 0 ;$i < $numStations; $i++)
            $viewCompare[$i] = 1;
        $viewCompare['numview'] = $numStations;
        $_SESSION['viewCompare'] = $viewCompare; 
        createViewmodules();
        $_SESSION['selectMesureCompare'] = 'T';
        $_SESSION['selectMesureHist'] = 'T';
        $_SESSION['selectMesureModule'] = 'T';
        $_SESSION['hist'] = 12;
        }      
    }
function saveTokenCookie($refresh_token)
    {global $save_token;
    if($save_token  == 0)return;  
    logMsg("saveTokenCookie:$refresh_token");
    echo "<script>";
    echo("var refresh_token = \"$refresh_token\";\n");
    echo("$.jCookies({name:'netatmotoken',value:{Refresh_token:refresh_token},days:30});");
    echo "</script>";
    }
function initClient()
	{global $client_id,$client_secret;
	if(isset($_SESSION['expires_in']))  
	    checkToken(); // seule action effectuer chaque fois
    // authentification through Netatmo
	if(isset($_GET["code"]) && !isset($_SESSION['client'])) 
		{if(isset($_GET["error"]))
		    {if($_GET["error"] == "access_denied")
		        logMsg("You refused the application's access");
		    else
		        logMsg("Unknown error");
		    logout();
		    }
		if(isset($_SESSION['state']) && ($_SESSION['state'] != $_GET['state']))
		    {logMsg("The state does not match");
		    logout();
		    }
		$code = $_GET["code"];
		$my_url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] ;
        $token_url = "https://api.netatmo.net/oauth2/token";
        $postdata = http_build_query(array(
                                        'grant_type' => "authorization_code",
                                        'client_id' => $client_id,
                                        'client_secret' => $client_secret,
                                        'code' => $code,
                                        'redirect_uri' => $my_url,  
                                        'scope' => "read_station read_thermostat write_thermostat"
                                        ));
        $opts = array('http' => array(
                                    'method'  => 'POST',
                                    'header'  => 'Content-type: application/x-www-form-urlencoded;charset=UTF-8',
                                    'content' => $postdata
                                    ));
        $context  = stream_context_create($opts);
        $response = file_get_contents($token_url, false, $context);
        $params = null;
        $params = json_decode($response, true);
        $access_token = $params['access_token'];
        $refresh_token = $params['refresh_token'];
        $expires_in = $params['expires_in'];
        $expire_in = $params['expire_in'];
        $_SESSION['refresh_token'] = $refresh_token;		
        $_SESSION['timeToken'] = time();
        $_SESSION['expires_in'] = $expires_in;// celui que j'utilise
        $_SESSION['expire_in'] = $expire_in;
        try
            {
            $client = new NAApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
            }catch(NAClientException $ex) 
        		{$_SESSION['ex'] = $ex;
        		logMsg('NAClientException:client from token');
        		alert("NAClientException:client from token");
			    logout();				
			    }       
        $_SESSION['client'] = $client;
        saveTokenCookie($refresh_token);
		}
	if(isset($_SESSION['client']))
		{$client = $_SESSION['client'];
		$tokens = $client->getAccessToken();       
        }
	if(1)
		{$helper = new NAApiHelper($client);	
		try {
			 $devicelist = $helper->simplifyDeviceList();
			}
		catch(NAClientException $ex) {
			$_SESSION['ex'] = $ex;
		    logMsg("NAClientException:devicelist");
		    alert("NAClientException:devicelist");
		    logout();	
			}	
	    $numStations = count($devicelist["devices"]);	
	    if(!isset($_SESSION['mydevices']))
    		{$mydevices = createDevicelist($devicelist);
	    	$_SESSION['mydevices'] = $mydevices;
	    	init($numStations);	
	    	}
	    getDashBoard($devicelist);
		}	 
	$user = $client->api("getuser", "POST");
    $Temperature_unit = 1 - $user['administrative']['unit'];
    $_SESSION['Temperature_unit'] = $Temperature_unit;	
    $_SESSION['lang'] = $user['administrative']['lang'];
    $timezone = $_SESSION['mydevices'][0]['timezone']; 
    $_SESSION['timezone'] = $timezone;
    date_default_timezone_set($timezone);
	}
function createViewmodules()
    {$mydevices = $_SESSION['mydevices']; 
    $numStations = $mydevices ["num"];
    for($i = 0;$i < $numStations;$i++)
        {$numModules = $mydevices[$i]["modules"]["num"];
        $pluvio = $mydevices[$i]["pluviometre"];

        for($j = 0; $j <= $numModules;$j++)
            $viewModules[$i][$j] = ($j == $pluvio + 1) ? 0:1;
        $viewModules[$i][1] = 0;
        $numview = 0;
        for($j = 0; $j <= $numModules;$j++)
            if($viewModules[$i][$j])++$numview;
        $viewModules[$i]["numView"] = $numview;
        }
    $_SESSION['viewModules'] = $viewModules;
    }
function createDevicelist($devicelist)
    {
    $numStations = count($devicelist["devices"]);
    $myDevices['num'] = $numStations;
    $myDevices['address'] = 0;
    for($stationId = 0; $stationId <  $numStations;$stationId++)
        {$myDevices[$stationId]['station_name'] = $devicelist["devices"][$stationId]["station_name"];
        $myDevices[$stationId]['_id'] = $devicelist["devices"][$stationId]["_id"];
        $myDevices[$stationId]['module_name'] = $devicelist["devices"][$stationId]["module_name"];
        $myDevices[$stationId]['timezone'] = $devicelist["devices"][$stationId]["place"]["timezone"];
        $numModules = count($devicelist["devices"][$stationId]["modules"]);
        $myDevices[$stationId]['modules']['num'] = $numModules;
        $iswap = 0;
        $pluvio = -2;
        for($module = 0; $module < $numModules;$module++)
            {$myDevices[$stationId]['modules'][$module]['_id'] = $devicelist["devices"][$stationId]["modules"][$module]["_id"];
            $myDevices[$stationId]['modules'][$module]['module_name'] = $devicelist["devices"][$stationId]["modules"][$module]["module_name"];
            $myDevices[$stationId]['modules'][$module]['type'] = $devicelist["devices"][$stationId]["modules"][$module]["type"];
            if($myDevices[$stationId]['modules'][$module]['type'] == 'NAModule1')$iswap = $module;
            if($myDevices[$stationId]['modules'][$module]['type'] == 'NAModule3')$pluvio = $module;
            }
        if($pluvio == 0)$pluvio = $iswap;
        $myDevices[$stationId]['swap'] = $iswap;
        $myDevices[$stationId]['pluviometre'] = $pluvio;
        if($iswap != 0)
            {$tmp = $myDevices[$stationId]['modules'][0]['_id'];
            $myDevices[$stationId]['modules'][0]['_id'] = $myDevices[$stationId]['modules'][$iswap]['_id'];
            $myDevices[$stationId]['modules'][$iswap]['_id'] = $tmp;
            
            $tmp = $myDevices[$stationId]['modules'][0]['module_name'];
            $myDevices[$stationId]['modules'][0]['module_name'] = $myDevices[$stationId]['modules'][$iswap]['module_name'];
            $myDevices[$stationId]['modules'][$iswap]['module_name'] = $tmp;
            
            $tmp = $myDevices[$stationId]['modules'][0]['type'];
            $myDevices[$stationId]['modules'][0]['type'] = $myDevices[$stationId]['modules'][$iswap]['type'];
            $myDevices[$stationId]['modules'][$iswap]['type'] = $tmp;
            }
        }
    return $myDevices;
    }
function getDashBoard($devicelist)
    {$numStations = count($devicelist["devices"]);  
    for($stationId = 0; $stationId <  $numStations;$stationId++)
        {$dashboard[$stationId][-1] = $devicelist["devices"][$stationId]["dashboard_data"];
        $numModules = count($devicelist["devices"][$stationId]["modules"]);
        $iswap = $_SESSION['mydevices'][$stationId]['swap'];
        $dashboard[$stationId][0] = $devicelist["devices"][$stationId]["modules"][$iswap]["dashboard_data"];     
        for($module = 1; $module < $numModules;$module++)
            {if($module == $iswap)
                $dashboard[$stationId][$module] = $devicelist["devices"][$stationId]["modules"][0]["dashboard_data"];
            else
                $dashboard[$stationId][$module] = $devicelist["devices"][$stationId]["modules"][$module]["dashboard_data"]; 
            }
        }
    $_SESSION['dashboard'] =  $dashboard; 
    }

function getLastMeasures($devicelist)
    {
	$client =  $_SESSION['client'];  
	$helper = new NAApiHelper();
	return $helper->GetLastMeasures($client,$devicelist);
    }
function getDevicelist() 
    {
	$client =  $_SESSION['client'];
	$helper = new NAApiHelper($client);
	try {
		//$devicelist = $client->api("devicelist", "POST");
		$devicelist = $helper->simplifyDeviceList();
		}
	catch(NAClientException $ex) {
		$_SESSION['ex'] = $ex;
		logMsg("NAClientException:devicelist");
		logout();	
		}	
	//$devicelist = $helper->SimplifyDeviceList($devicelist);
    return $devicelist;
    }
function emptyLog()
    {$_SESSION['LogMsg'] = '';
    }
function logMsg($txt)
    {$date = date("D H:i s",time());
    $_SESSION['LogMsg'] .= $date.': '.$txt.'<br>';
    } 
function logout()
    {$path = dirname($_SERVER['PHP_SELF']).'/logout.php';
    ?>
    <script>
    <?php echo("path = \"$path\";\n");?>
    top.location.href=path;
    </script>
    <?php
    //echo("<script> top.location.href=../logout.php</script>");
    }
function alert($txt)
    {$txt = "'".$txt."'";
    echo("<script>alert($txt);\n</script>");
    }
function degree2($temperature)
// conver celsius to fahrenheit if necessary
    {$Temperature_unit = $_SESSION['Temperature_unit'];
    if($Temperature_unit)
        $t = $temperature;
    else
        {$t =  intval($temperature*18+320.5);
        $t /= 10;
        }
    return $t;
    }
function tr($txt)
    {global $trans;
    if(!isset($trans[$txt]))return $txt;
    switch($_SESSION['lang'])
        {case 'fr-FR': return $txt;
                        break;
        case 'en-US':  return $trans[$txt][0];
                        break;
        default:        return $txt;
                        break;
        }    
    }  
function getTimeOffset($localZone)
    {
    $dateTimeZoneLocal = new DateTimeZone("$localZone");
    $dateTimeZoneGmt = new DateTimeZone("UTC");//UTC
    $dateTimeLocal = new DateTime('now', $dateTimeZoneLocal);
    $dateTimeGmt = new DateTime('now', $dateTimeZoneGmt);
    $offset = ($dateTimeZoneLocal->getOffset($dateTimeGmt))/3600;
    return $offset; 
    }    
?>
