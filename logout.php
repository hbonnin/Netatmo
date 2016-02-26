<?php
define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__.'/src/Netatmo/autoload.php');
session_start();
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
<h2> You are logged out </h2
>
<?php
echo "<pre><br>";

foreach ($_SESSION  as $key => $value)
    {switch ($key)
        {
        case LogMsg:
        //case mydevices:
             echo "<br>$key: "; 
            print_r($_SESSION[$key]);
            break;
        default:
                break;  
        }
    }
echo "</pre>";
echo("</body></html>");
$_SESSION=array();
session_destroy();
exit();    
?>
