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
        							'content' => $postdata
    								));
    $context  = stream_context_create($opts);
    $response = file_get_contents($token_url, false, $context);
    $params = null;
    $params = json_decode($response, true);
	$access_token = $params['access_token'];
	$refresh_token = $params['refresh_token'];
	$expires = $params['expires_in'];
	$_SESSION['refresh_token'] = $refresh_token;		
	$_SESSION['time'] = time();
	$_SESSION['expires'] = $expires;
	$client = new NAApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
	$_SESSION['client'] = $client;		
	}
function init($numStations)
    {if(!isset($_SESSION['init']))
        {$_SESSION['emsg'] = 'Messages d\'erreur:<br>';
        $_SESSION['init'] = true;
        $_SESSION['stationId'] = 0;
        $_SESSION['selectedInter'] = '1day';
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
        for($i = 0 ;$i < $numStations; $i++)
            $viewCompare[$i] = 1;
        $viewCompare['numview'] = $numStations;
        $_SESSION['viewCompare'] = $viewCompare; 
        $_SESSION['selectMesureCompare'] = 'T';
        $_SESSION['selectMesureModule'] = 'T';
        }

    }
function initClient()
	{global $client_id,$client_secret,$test_username,$test_password;
	date_default_timezone_set("Europe/Paris");
	
	$debug = 0;
	if($debug)echo("initclient / ");	
	if(isset($_SESSION['time']))
		{$time_left = $_SESSION['time'] + $_SESSION['expires'] - time();
		if($time_left < 0) 
			{if($debug)echo("Refresh token / ");
			refreshToken();
			}
		}
	if(isset($_GET["error"]))
		{if($_GET["error"] == "access_denied")
			{echo "You refused the application's access\n";exit(-1);}
		}
	if(isset($_GET["code"]) && !isset($_SESSION['client'])) // login on Netatmo (do not work on free)
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
										'content' => $postdata
										));
			$context  = stream_context_create($opts);
			$response = file_get_contents($token_url, false, $context);
			$params = null;
			$params = json_decode($response, true);
			$access_token = $params['access_token'];
			$refresh_token = $params['refresh_token'];
			$expires = $params['expires_in'];
			$_SESSION['refresh_token'] = $refresh_token;		
			$_SESSION['time'] = time();
			$_SESSION['expires'] = $expires;
			$client = new NAApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
			$_SESSION['client'] = $client;	
			if($debug)echo("client from token / ");				
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
					$_SESSION['emsg'] = "User:$test_username
					<br>ou mot de passe:$test_password
					<br> ou id:$client_id
					<br> ou secret:$client_secret incorrect<br>*****<br>".$ex->getMessage();
				else 
				    $_SESSION['emsg'] .= $ex->getMessage();

			    echo("<script> top.location.href='logout.php'</script>");				
			    }    
		$_SESSION['client'] = $client;	
		if($debug)echo("client from password / ");		
		}  	
	
	$helper = new NAApiHelper();
	
	if(isset($_SESSION['devicelist']))
		$devicelist = $_SESSION['devicelist'];
	else
		{try {
			$devicelist = $client->api("devicelist", "POST");
			}
		catch(NAClientException $ex) {
			//$ex = stristr(stristr($ex,"Stack trace:",true),"message");
			$_SESSION['emsg'] .= "erreur:$ex->getMessage();";
				echo " {$_SESSION['emsg']}";
			echo("<script> top.location.href='logout.php'</script>");	
			}	
			
		$devicelist = $helper->SimplifyDeviceList($devicelist);
		$_SESSION['devicelist'] = $devicelist;
		if($debug)echo("device liste / ");			
		}
		
	if(isset($_SESSION['mesures']))
			$last_mesures = $_SESSION['mesures'];
		else
   			{
   			$last_mesures = $helper->GetLastMeasures($client,$devicelist);
			$_SESSION['mesures'] = $last_mesures;
			if($debug)echo("mesures / ");	
			}
	$numStations = count($devicelist["devices"]);		
    init($numStations);			
	}
?>
