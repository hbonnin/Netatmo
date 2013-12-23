<?php 
require_once 'Config.php';
require_once 'AppliCommonPublic.php';
require_once 'NAApiClient.php';
require_once 'initClient.php';
session_start();
date_default_timezone_set($timezone);
echo "<pre>";
$date = date('d/m/Y H:i s',time());
echo("Timezone = $timezone  date:$date<br>");
echo("Temperature_unit (Netatmo) = {$_SESSION['Temperature_unit']} <br>");
echo("Language (Netatmo) = {$_SESSION['lang']} <br>");
echo("Path = {$_SESSION['path']} <br>");
echo ("Agent = {$_SERVER['HTTP_USER_AGENT']} <br>"); 
echo ("Client = {$_SERVER['REMOTE_ADDR']} <br>"); 
$t = $_SESSION['timeToken'] + $_SESSION['expires_in'];
$dt = $t - time();
echo "Token valide &#8594; ".date('d/m/Y H:i s',$t)."(".$dt."s);\n";
echo("-------------------------------\n");
print_r($_SESSION['LogMsg']);
if(isset($_SESSION['ex']))
     print_r($_SESSION['ex']);

if($_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']) != 'fraysseix.fr/Netatmo')
    {foreach ($_SESSION  as $key => $value)
        {switch ($key)
            {case GraphiqueMesureInt:           
            case GraphiqueMesureExt:
            case MenuInterval:
            case selectMesures:
            case viewCompare: 
            case viewModules:
            case LogMsg:
                    break;
            default:echo "<br>$key: "; 
                    print_r($_SESSION[$key]);
                    break;  
            }
        }
    }

?>
