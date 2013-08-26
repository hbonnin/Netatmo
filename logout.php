<?php 
require_once 'AppliCommonPublic.php';
require_once 'NAApiClient.php';
require_once 'initClient.php';
session_start();
date_default_timezone_set("Europe/Paris");
$date = date("d:m:Y H:i",time());
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<script src='js/close.js'></script>
<script src='js/size.js'></script>
<link rel='icon' href='favicon.ico' />
</head>
<body >
<script>
if(isMobile())
    {closewebapp();
    exit();
    }
</script>
<h2> You are logged off </h2>
<?php echo $date;?>
<?php 
echo "<pre>";
if($_SERVER['SERVER_NAME'] != 'fraysseix.fr')
    {print_r($_SESSION['LogMsg']);
    echo("path={$_SESSION['path']} <br>");    
    print_r($_SESSION['client']);echo "<br>";
    print_r($_SESSION['mydevices']);echo "<br>";
    }
if(isset($_SESSION['ex']))
    print_r($_SESSION['ex']);
echo "</pre>";
echo("</body></html>");
$_SESSION=array();
session_destroy();
exit();
?>
