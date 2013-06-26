<?php 
require_once 'Config.php';
    session_start();
    if(isset($_GET["logout"]))
		{$_SESSION=array();
    	session_destroy();
    	exit(-1);
    	}
     
    $my_url = "http://" . $_SERVER['SERVER_NAME'] . "/Netatmo/iconesExt.php";
    $_SESSION['state'] = md5(uniqid(rand(), TRUE));
    $dialog_url="https://api.netatmo.net/oauth2/authorize?client_id=" 
    	. $client_id . "&redirect_uri=" . urlencode($my_url) . "&state="
    	. $_SESSION['state'];
    echo("<script> top.location.href='" . $dialog_url . "'</script>");
?>
        
