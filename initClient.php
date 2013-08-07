<?php
function refreshToken()
    {global $client_id,$client_secret;
    date_default_timezone_set("Europe/Paris");    
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
        							'timeout' => 30,
        							'content' => $postdata
    								));
    $context  = stream_context_create($opts);
    @$response = file_get_contents($token_url, false, $context);
    $params = null;
    $params = json_decode($response, true);
/*    
    $client = $_SESSION['client'];
    $params = array('grant_type' => 'refresh_token'
            		,'client_id' => $client_id
            		,'client_secret' => $client_secret
            		,'refresh_token' => $_SESSION['refresh_token']
            		);           		
    @$params = $client->makeRequest($token_url, $method = 'GET', $params);
*/
    if(!$params || empty($params['access_token']) || empty($params['access_token']))
        {logMsg("refresh token: NO success");
        //echo("NO\n");
        return;}
	$access_token = $params['access_token'];
	$refresh_token = $params['refresh_token'];
	$expires_in = $params['expires_in'];
	$_SESSION['refresh_token'] = $refresh_token;		
	$_SESSION['timeToken'] = time();
	$_SESSION['expires_in'] = $expires_in;
	$client = $_SESSION['client'];
	//$client->setTokensFromStore($params);

	$client = new NAApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
	$client->setVariable("client_id", $client_id);
	$client->setVariable("client_secret", $client_secret);
	if(isset($_SESSION['code']))$client->setVariable("code", $_SESSION['code']);

    try {
        $tokens = $client->getAccessToken();       
        } catch(NAClientException $ex) 
            {$_SESSION['ex'] = $ex;
            echo("<script> top.location.href='logout.php'</script>");				
            }            
	logMsg("refresh token success:$refresh_token");	
	//echo("YES\n");
	$_SESSION['client'] = $client;		
	}
	
function checkToken()
    {if(isset($_SESSION['timeToken']))
		{$time_left = $_SESSION['timeToken'] + $_SESSION['expires_in'] - time();
		//{$time_left = $_SESSION['timeToken'] + 31*60 - time();
		if($time_left < 30*60) 
			refreshToken();
		//logMsg("checkToken $time_left");	
		}
    }	
function getTimeLeft()
    {return $_SESSION['timeToken'] + $_SESSION['expires_in'] - time(); 
    //return $_SESSION['timeToken'] + 31*60 - time();
    }
function init($numStations)
    {if(!isset($_SESSION['init']))
        {$_SESSION['init'] = 1;
        $_SESSION['expires_in']  = 10800;
        $_SESSION['timeLoad'] = time();
        $_SESSION['stationId'] = 0;
        $_SESSION['stationIdP'] = -1;
        $_SESSION['selectedInter'] = '1day';
        $_SESSION['selectedInterP'] = 'x';
        $_SESSION['date_begP'] = 0;
        $_SESSION['date_endP'] = 0;        
        $_SESSION['dateend'] = date("d/m/Y",mktime(date("H"), date("i"), 0, date('m') , date('d'),date('y')));
        $_SESSION['datebeg'] = date("d/m/Y",mktime(date("H"), date("i"), 0, date('m') , date('d'),date('y')));
        $MenuInterval = array ( "G" => 4,
                            "C"  => 1,
                            "M"  => 3, 
                            "opt" => array (
                                        0 => array ('1week','1 semaine',7*24*60*60),
                                        1 => array ('1day','1 journée',24*60*60),
                                        2 => array ('3hours','3 heures',3*60*60),
                                        3 => array ('30min','30 minutes',30*60),
                                        4 => array ('max','5 minutes',5*60)
                                        )
                                );
        $_SESSION['MenuInterval'] = $MenuInterval;  
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
        $_SESSION['selectMesureModule'] = 'T';
        $_SESSION['Ipad'] =  strpos($_SERVER['HTTP_USER_AGENT'],'iPad') ? 1 : 0;  
        }       
    }

function initClient()
	{global $client_id,$client_secret,$test_username,$test_password;
	date_default_timezone_set("Europe/Paris");

	if(!isset($_SESSION['LogMsg']))
	    {$date = date("d/m H:i:s",time());
	    $_SESSION['LogMsg'] = "Log start :$date<br>";
	    }
	    
	checkToken();

	if(isset($_GET["error"]))
		{if($_GET["error"] == "access_denied")
			{echo "You refused the application's access\n";exit(-1);}
		}
	if(isset($_GET["code"]) && !isset($_SESSION['client'])) 
		{$code = $_GET["code"];
		$my_url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] ;
 		if($_SESSION['state'] && ($_SESSION['state'] == $_GET['state'])) 
			{$token_url = "https://api.netatmo.net/oauth2/token";
			$postdata = http_build_query(array(
											'grant_type' => "authorization_code",
											'client_id' => $client_id,
											'client_secret' => $client_secret,
											'code' => $code,
											'redirect_uri' => $my_url               
											));
			$opts = array('http' => array(
										'method'  => 'POST',
										'header'  => 'Content-type: application/x-www-form-urlencoded;charset=UTF-8',
        						    	'timeout' => 10,
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
			$_SESSION['code'] = $code;		
			$_SESSION['expires_in'] = $expires_in;// celui que j'utilise
			//$_SESSION['expire_in'] = $expire_in;
			$client = new NAApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
			$_SESSION['client'] = $client;	
			$_SESSION['timeToken'] = time();
			logMsg('client from token');			
			}
		else
			{echo("The state does not match.");exit(-1);}
		}	

    if(!isset($_SESSION['client']) &&  empty($test_username) || empty($test_password))
        {if(isset($_SESSION['username']) && isset($_SESSION['password'] ))
            {$test_username = $_SESSION['username'];
            $test_password = $_SESSION['password']; 
            }
        }     
	if(isset($_SESSION['client']))
		$client = $_SESSION['client'];
	else  // si identifiant et mot de passe dans config.php
		{$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret, "username" => $test_username, "password" => $test_password));
		try {
			$tokens = $client->getAccessToken();       
			} catch(NAClientException $ex) 
			    {if(!isset($_SESSION['state']))
					logMsg("User:$test_username
					<br>ou mot de passe:$test_password
					<br> ou id:$client_id
					<br> ou secret:$client_secret incorrect");
				else 
				    logMsg('client from password');
				$_SESSION['ex'] = $ex;
			    echo("<script> top.location.href='logout.php'</script>");				
			    }  
	    $_SESSION['timeToken'] = time();	
	    $_SESSION['refresh_token'] = $tokens['refresh_token'];
	    $_SESSION['expires_in'] = $tokens['expires_in'];
		$_SESSION['client'] = $client;	
		$token = $_SESSION['refresh_token'] ;
		logMsg("client from password token:$token");
	    }
	    
	$helper = new NAApiHelper();
	
	if(isset($_SESSION['devicelist']))
		$devicelist = $_SESSION['devicelist'];
	else
		{
		try {
			$devicelist = $client->api("devicelist", "POST");
			}
		catch(NAClientException $ex) {
			$_SESSION['ex'] = $ex;
        	logMsg('devicelist');
			echo("<script> top.location.href='logout.php'</script>");	
			}	

		$devicelist = $helper->SimplifyDeviceList($devicelist);
		$mydevices = createDevicelist($devicelist);
		$_SESSION['mydevices'] = $mydevices;
		}
			
	$numStations = count($devicelist["devices"]);		
    init($numStations);	
    getScreenSize();
	}
function createViewmodules()
    {$mydevices = $_SESSION['mydevices']; 
    $numStations = $mydevices ["num"];
    for($i = 0;$i < $numStations;$i++)
        {$numModules = $mydevices[$i]["modules"]["num"];
        $viewModules[$i]["numView"] = $numModules + 1;
        for($j = 0; $j <= $numModules;$j++)
            $viewModules[$i][$j] = 1;
        }
    $_SESSION['viewModules'] = $viewModules;
    }
function createDevicelist($devicelist)
    {$numStations = count($devicelist["devices"]);
    $myDevices['num'] = $numStations;
    $myDevices['address'] = 0;
    for($stationId = 0; $stationId <  $numStations;$stationId++)
        {$myDevices[$stationId]['station_name'] = $devicelist["devices"][$stationId]["station_name"];
        $myDevices[$stationId]['_id'] = $devicelist["devices"][$stationId]["_id"];
        $myDevices[$stationId]['module_name'] = $devicelist["devices"][$stationId]["module_name"];
        $numModules = count($devicelist["devices"][$stationId]["modules"]);
        $myDevices[$stationId]['modules']['num'] = $numModules;
        for($module = 0; $module < $numModules;$module++)
            {$myDevices[$stationId]['modules'][$module]['_id'] = $devicelist["devices"][$stationId]["modules"][$module]["_id"];
            $myDevices[$stationId]['modules'][$module]['module_name'] = $devicelist["devices"][$stationId]["modules"][$module]["module_name"];
            $myDevices[$stationId]['modules'][$module]['type'] = $devicelist["devices"][$stationId]["modules"][$module]["type"];
            }
        }
    return $myDevices;
    }
function getLastMeasures($devicelist)
    {$helper = new NAApiHelper();
	$client =  $_SESSION['client'];
	return $helper->GetLastMeasures($client,$devicelist);
    }
function getDevicelist() // si je le supprime de SESSION
    {$helper = new NAApiHelper();
	$client =  $_SESSION['client'];
	try {
		$devicelist = $client->api("devicelist", "POST");
		}
	catch(NAClientException $ex) {
		$_SESSION['ex'] = $ex;
		echo("<script> top.location.href='logout.php'</script>");	
		}	
    return $helper->SimplifyDeviceList($devicelist);
    }
function getScreenSize()
    {// width and height of the navigator window
    if(isset($_GET['width']))
        $_SESSION['width'] = $_GET['width'];
    if(isset($_GET['height']))
        $_SESSION['height'] = $_GET['height'];
    }
function logMsg($txt)
    {$date = date("D H:i s",time());
    $_SESSION['LogMsg'] .= $date.': '.$txt.'<br>';
    } 
?>
