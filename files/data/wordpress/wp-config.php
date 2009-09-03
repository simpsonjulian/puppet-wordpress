<?php
/** WordPress's Debianised default master config file 
Please do NOT edit and read about how the configuration works in the README.Debian
**/

require_once('/etc/wordpress/config-'.strtolower($_SERVER['HTTP_HOST']).'.php');

define('ABSPATH', '/data/wordpress/');

require_once(ABSPATH.'wp-settings.php');
?>

