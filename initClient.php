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

function initClient()
	{global $client_id,$client_secret,$test_username,$test_password;
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
	if(isset($_GET["code"]) && !isset($_SESSION['client'])) // login on Netatmo
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
	
	if(isset($_SESSION['client']))
		$client = $_SESSION['client'];
	else  // si identifiant et mot de passe dans config.php
		{$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret, "username" => $test_username, "password" => $test_password));
		try {
			$tokens = $client->getAccessToken();       
			} catch(NAClientException $ex) {
				echo ("Identifiant ($test_username)\n
				ou mot de passe ($test_password) incorrect (id:$client-id secret:$client_secret");
			exit(-1);	
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
			$ex = stristr(stristr($ex,"Stack trace:",true),"message");
			echo("erreur:$ex");
			exit(-1);
			}	
			
		$devicelist = $helper->SimplifyDeviceList($devicelist);
		$_SESSION['devicelist'] = $devicelist;
		if($debug)echo("device liste / ");			
		}
		
	if(isset($_SESSION['mesures']))
			$mesures = $_SESSION['mesures'];
		else
   			{
   			$mesures = $helper->GetLastMeasures($client,$devicelist);
			$_SESSION['mesures'] = $mesures;
			if($debug)echo("mesures / ");	
			}
	}
?>