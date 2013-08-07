<?php 
require_once 'AppliCommonPublic.php';
require_once 'NAApiClient.php';
session_start();
date_default_timezone_set("Europe/Paris");
$date = date("d:m:Y H:i",time());
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<link rel='icon' href='favicon.ico' />
</head>
<body >

<h2> You are logged off </h2>
<?php echo $date;?>
<?php 
require_once 'NAApiClient.php';
echo "<pre>";
if(!$_SESSION['Ipad'])
    {if($_SERVER['SERVER_NAME'] != 'fraysseix.fr')
        print_r($_SESSION);
    else if(isset($_SESSION['ex']))
        print_r($_SESSION['ex']);
    }
echo "</pre>";
echo("</body></html>");
$_SESSION=array();
session_destroy();
?>
