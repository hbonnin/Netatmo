<?php 
define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__.'/src/Netatmo/autoload.php');
session_start();
require_once 'initClient.php';
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<meta charset='utf-8'>
</head>
<body>
<?php 
echo "<pre>";
$cu = tu(); $vu = wu(); $pu = pru();
$timezone = $_SESSION['timezone'];
date_default_timezone_set($timezone);
$date = date('d/m/Y H:i s',time());
echo("Timezone = $timezone  date:$date<br>");
echo("Units: Temperature ($cu) Wind ($vu)  Pressure ($pu)<br>");
echo("Language = {$_SESSION['lang']} <br>");
echo("Path = {$_SESSION['path']} <br>");
echo ("Agent = {$_SERVER['HTTP_USER_AGENT']} <br>"); 
echo ("Client = {$_SERVER['REMOTE_ADDR']} <br>"); 
$t = $_SESSION['timeToken'] + $_SESSION['expires_in'];
$dt = $t - time();
echo "Token valide &#8594; ".date('d/m/Y H:i.s',$t)." (".$dt."s left);\n";
echo("-------------------------------\n");
if(isset($_SESSION['ex']))
     print_r($_SESSION['ex']);
    {foreach ($_SESSION  as $key => $value)
        {switch ($key)
            {
            case LogMsg:
            //case viewModules:
            //case selectMesureHist:
            //case hist:
            //case client:
            //case lang:
            //case data:
            case mydevices:
                echo "<br>$key: "; 
                print_r($_SESSION[$key]);
                break;
            default:
                    break;  
            }
        }
    }
?>
</body>
</html>
