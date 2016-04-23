<?php
require_once 'Config.php';
require_once 'translate.php';

function alert($txt)
    {$txt = "'".$txt."'";
    echo("<script>alert($txt);\n</script>");
    }
function logMsg($txt)
    {$date = gmdate("D H:i s",time());
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
    }    
function degree2($temperature)
// convert celsius to fahrenheit if necessary
    {$Temperature_unit = $_SESSION['Temperature_unit'];
    if($Temperature_unit == 0)
        $t = $temperature;
    else
        {$t =  intval($temperature*18+320.5);
        $t /= 10;
        }
    return $t;
    }
function tu()
    {return $t = ($_SESSION['Temperature_unit'] ==  0)? '°':'F';
    }
$speedUnit = array(' kph',' mph',' ms',' bf',' noeud'); 
function wu()
    {global $speedUnit;
    return $speedUnit[$_SESSION['Wind_unit']];
    }
function speed2($speed)
//windunit: 0 -> kph, 1 -> mph, 2 -> ms, 3 -> beaufort, 4 -> knot
    {switch($_SESSION['Wind_unit'])
        {case 0 :return $speed;
        case 1 :return $speed*(1000/3600);
        case 2 :return $speed*.621371;
        case 3 :
            if($speed <= 1)return 0;
            if($speed <= 5)return 1;
            if($speed <= 11)return 2;
            if($speed <= 19)return 3;
            if($speed <= 28)return 4;
            if($speed <= 38)return 5;
            if($speed <= 49)return 6;
            if($speed <= 61)return 7;
            if($speed <= 74)return 8;
            if($speed <= 88)return 9;
            if($speed <= 102)return 10;
            if($speed <= 117)return 11;
            return 12;
        case 4 :return $speed*.539957;
        default: return -1;        
        }
    }
 function angleDir($angle)
    {
    
    if($angle > 22 && $angle <= 67)return '&swarr;';
    else if($angle > 67 && $angle <= 112)return '&larr;';
    else if($angle > 112 && $angle <= 157)return '&nwarr;';
    else if($angle > 157 && $angle <= 202)return '&uarr;';
    else if($angle > 202 && $angle <= 247)return '&nearr;';
    else if($angle > 247 && $angle <= 292)return '&rarr;';
    else if($angle > 292 && $angle <= 337)return '&searr;';
    else return '&darr;';  
/*    
    if($angle > 22 && $angle <= 67)return '&nearr;';
    else if($angle > 67 && $angle <= 112)return '&rarr;';
    else if($angle > 112 && $angle <= 157)return '&searrow;';
    else if($angle > 157 && $angle <= 202)return '&darr;';
    else if($angle > 202 && $angle <= 247)return '&swarr;';
    else if($angle > 247 && $angle <= 292)return '&larr;';
    else if($angle > 292 && $angle <= 337)return '&nwarr;';
    else return '&uarr;';  
*/    
    }
    
$pressureUnit = array(' mbar',' inHg',' mmHg');   
function pru()
    {global $pressureUnit;
    return $pressureUnit[$_SESSION['Pressure_unit']];
    }
function pressure2($p)   
    {switch($_SESSION['Pressure_unit'])
        {case 0: return $p;
        case 1: return $p*.7501;
        case 2: return $p*.02953;
        default: return -1; 
        }
    }
function tr($txt)
    {global $trans;
    if(!isset($trans[$txt][0]))return $txt;
     switch($_SESSION['lang'])
        {case 'fr-FR': return $txt;
                        break;
        case 'en-US':   
        case 'en-GB':   return $trans[$txt][0];
                        break;
        default:        return $txt;
                        break;
        }    
    }  
function saveTokenCookie($refresh_token)
    {global $save_token;
    if($save_token  == 0)return;  
    //logMsg("saveTokenCookie:$refresh_token");
    echo "<script>";
    echo("var refresh_token = \"$refresh_token\";\n");
    echo("$.jCookies({name:'nntoken',value:{Refresh_token:refresh_token},days:3});");
    echo "</script>";
    }
function init($numStations)
    {if(!isset($_SESSION['init']))
        {$_SESSION['init'] = 1;
        date_default_timezone_set($_SESSION['timezone']);
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
        $_SESSION['selectMesureCompare'] = 'T';
        $_SESSION['selectMesureHist'] = 'T';
        $_SESSION['selectMesureModule'] = 'T';
        $_SESSION['hist'] = 12; // compareHist = 12 mois
        }      
    }  
function createViewmodules($numStations) 
    {if(isset($_SESSION['viewModules']))return;
    $mydevices = $_SESSION['mydevices']; 
    for($i = 0;$i < $numStations;$i++)
        {$numModules = $mydevices[$i]["modules"]["num"];
        $numModulesInt = $mydevices[$i]["modules"]["numInt"];
        $viewModules[$i][0] = 1;
        $viewModules[$i][1] = 0;
        for($j = 2; $j < 2+$numModulesInt;$j++)
            $viewModules[$i][$j] = 1;
        for($j = $numModulesInt + 2; $j <= $numModules;$j++)
            $viewModules[$i][$j] = 0;
        $numview = $numModulesInt + 1;
        $viewModules[$i]["numView"] = $numview;
        }
    $_SESSION['viewModules'] = $viewModules;
    }
    
function createMyDevices($devicelist)
    {
    $numStations = count($devicelist["devices"]);
    $myDevices['num'] = $numStations;
    $myDevices['address'] = 0;
    $myDevices['user'] = $devicelist["user"];
    for($stationId = 0; $stationId <  $numStations;$stationId++)
        {
        $myDevices[$stationId]['place'] = $devicelist["devices"][$stationId]["place"];
        $myDevices[$stationId]['station_name'] = $devicelist["devices"][$stationId]["station_name"];
        $myDevices[$stationId]['_id'] = $devicelist["devices"][$stationId]["_id"];
        $myDevices[$stationId]['module_name'] = $devicelist["devices"][$stationId]["module_name"];
        $myDevices[$stationId]['timezone'] = $devicelist["devices"][$stationId]["place"]["timezone"];
        $myDevices[$stationId]['firmware'] = $devicelist["devices"][$stationId]["firmware"];
        $myDevices[$stationId]['last_upgrade'] = $devicelist["devices"][$stationId]["last_upgrade"]; 
        $myDevices[$stationId]['wifi_status'] = $devicelist["devices"][$stationId]["wifi_status"];        
        $myDevices[$stationId]['dashboard_data'] = $devicelist["devices"][$stationId]["dashboard_data"];
        $numModules = count($devicelist["devices"][$stationId]["modules"]);
        $myDevices[$stationId]['modules']['num'] = $numModules;
        $myDevices[$stationId]['modules']['numMod4'] = 0;
        $indexModule = 0;  
        for($module = 0; $module < $numModules;$module++)
            {
            $typeModule = $devicelist["devices"][$stationId]["modules"][$module]["type"];
            switch($typeModule)
                {
                case "NAModule1": // exterieur
                    $index = 0;
                    break;
                case "NAModule2": // anemometre
                    $index = 11;
                    break;
                case "NAModule3": // pluviometre
                    $index = 10;
                    break;
                case "NAModule4": // interieur
                    $index = ++$indexModule;
                    break;
                default:
                    break;
                }
            $myDevices[$stationId]['modules'][$index] = $devicelist["devices"][$stationId]["modules"][$module];
            $myDevices[$stationId]['modules']['numInt'] = $indexModule;
            }
        }  
    $_SESSION['mydevices'] = $myDevices;    
    $_SESSION['timezone'] = $myDevices[0]['place']['timezone'];
    $_SESSION['lang'] = $myDevices['user']['administrative']['lang']; 
    $_SESSION['Temperature_unit'] = $myDevices['user']['administrative']['unit']; 
    $_SESSION['Wind_unit'] = $myDevices['user']['administrative']['windunit']; 
    $_SESSION['Pressure_unit'] = $myDevices['user']['administrative']['pressureunit']; 
    return $numStations;
    }   
function loadData($client)
    {try{
        $data = $client->getData();
        }
    catch(Netatmo\Exceptions\NAClientException $ex)
        {
       logMsg("An error occured while retrieving data: ". $ex->getMessage());
       logout();
        }
    if(!isset($data['devices']) || !is_array($data['devices']) || count($data['devices']) < 1)
        {
        logMsg("User has no devices");
        logout();
        }    
    return $data;
    }
function initClient()
    {checkToken();
    if(isset($_SESSION['tinitClient']) &&  time() - $_SESSION['tinitClient'] < 5*60 )return;
    $_SESSION['tinitClient'] = time();
    logMsg("initClient");
    $data = loadData($_SESSION['client']);  
    //$_SESSION['data'] = $data;  
    $numStations = createMyDevices($data);  
    createViewmodules($numStations);  
    init($numStations); 
    }
function login($client,$cook)
    { 
    if(!$cook)
        {try{
            $tokens = $client->getAccessToken();
            }
        catch(Netatmo\Exceptions\NAClientException $ex)
            {
            echo "An error occured while trying to retrieve your tokens \n";
            echo "Reason: ".$ex->getMessage()."\n";
            die();
            }   
        $_SESSION['refresh_token'] = $tokens['refresh_token'];
        $_SESSION['expires_in'] = $tokens['expires_in'];
        $_SESSION['timeToken'] = time();
        saveTokenCookie($_SESSION['refresh_token']); 
        }
    $_SESSION['client'] = $client;  
    initClient();
    echo "<script>";
    echo "top.location.href = 'iconesExt.php';";
    echo "</script>"; 
    }
function refreshToken($firstTime = 1)
    {global $client_id,$client_secret;
    if(!isset($_SESSION['refresh_token']))
        {logMsg('in refreshToken: NO refreshtoken');
        logout();
        }
    $refresh_token = $_SESSION['refresh_token'];
    logMsg("Previous refresh token:$refresh_token  ");
    $token_url = "https://api.netatmo.net/oauth2/token";
    $postdata = http_build_query(array(
         							'grant_type' => "refresh_token",
            						'refresh_token' => $_SESSION['refresh_token'],
            						'client_id' => $client_id,
            						'client_secret' => $client_secret
        							));
    $opts = array('http' => array(
        							'method'  => 'POST',
        							'header'  => 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'."\r\n"
        							                .'Cache-Control: no-store'."\r\n"
        							                .'Pragma: no-cache'."\r\n",
        							'content' => $postdata
    								));
    $context  = stream_context_create($opts);
    @$response = file_get_contents($token_url, false, $context);
    if(!$response)
        {logMsg("Could not refresh token");
        if(!$firstTime)logout();
        return -1;
        }
    $params = null;
    $params = json_decode($response, true);
	$access_token = $params['access_token'];
	$refresh_token = $params['refresh_token'];
	$expires_in = $params['expires_in'];
	//$expires_in = 400;
	logMsg("Refresh token success:$refresh_token");
	logMsg("Expires:$expires_in");
	saveTokenCookie($refresh_token);
	$_SESSION['refresh_token'] = $refresh_token;		
	$_SESSION['timeToken'] = time();


	$_SESSION['expires_in'] = $expires_in;
	$client = new Netatmo\Clients\NAWSApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
	$client->setVariable("client_id", $client_id);
	$client->setVariable("client_secret", $client_secret);
	$client->setVariable("scope", "read_station");
	$_SESSION['client'] = $client;	
	return 1;
	}
 function checkToken()
    {if(isset($_SESSION['timeToken']))
		{$time_left = $_SESSION['timeToken'] + $_SESSION['expires_in'] - time();
		//logMsg("timeLeft:$time_left");
		if($time_left < 5*60) 
			refreshToken(0);
		return $time_left;
		}
	else logout();
    }	
?>
