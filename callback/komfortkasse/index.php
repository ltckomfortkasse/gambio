<?php 
/**
 * Komfortkasse
 * routing
 *
 * @version 1.3.0.4-xtc4/xct3
 */

ini_set('default_charset', 'utf-8');

//  error_reporting(E_ALL);
ini_set('display_errors', '0');



if (!array_key_exists('komfortkasse_no_include', $_GET) && !array_key_exists('komfortkasse_no_include', $_POST)) {
    require_once ('../../includes/application_top_callback.php');
}
include_once 'Komfortkasse.php';


$action = Komfortkasse_Config::getRequestParameter('action');

$kk = new Komfortkasse();
$kk->$action();
//call_user_method($action, );

?>