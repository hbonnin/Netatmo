<?php 
require_once 'Config.php';

    if(session_id())
		{$_SESSION=array();
    	session_destroy();
    	}
    session_start();
    
    $my_url = "http://" . $_SERVER['SERVER_NAME'] . "/Netatmo/menu.php";
//    if(isset($_GET["code"]))$code = $_GET["code"];
//    if(empty($code)) 
    	{$_SESSION['state'] = md5(uniqid(rand(), TRUE));
    	$dialog_url="https://api.netatmo.net/oauth2/authorize?client_id=" 
    	. $client_id . "&redirect_uri=" . urlencode($my_url) . "&state="
    	. $_SESSION['state'];
    	echo("<script> top.location.href='" . $dialog_url . "'</script>");
    	}

//    if($_SESSION['state'] && ($_SESSION['state']===$_GET['state'])) 
    if($_SESSION['state'] && ($_SESSION['state'] == $_GET['state'])) 
    	{$token_url = "https://api.netatmo.net/oauth2/token";
	    $postdata = http_build_query(array('grant_type' => "authorization_code",
            							'client_id' => $client_id,
            							'client_secret' => $client_secret,
            							'code' => $code,
            							'redirect_uri' => $my_url               
        								));
    	$opts = array('http' => array('method'  => 'POST',
        							'header'  => 'Content-type: application/x-www-form-urlencoded',
        							'content' => $postdata
    								));
    	$context  = stream_context_create($opts);
    	$response = file_get_contents($token_url, false, $context);
    	}
?>
        
