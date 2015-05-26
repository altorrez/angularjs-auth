<?php 
 
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__);
define('ROUTE_DIR', ROOT.DS.'albus'.DS.'Routes'.DS.'*.php');

require ROOT.DS.'albus'.DS.'Core'.DS.'autoloader.php';

$request = new albus\Core\Request();
$response = new albus\Core\Response();
// Uncomment this to enable database connections
$db = new albus\Core\Database();
$router = new albus\Core\Router();

// All user defined routes should be defined in albus/Routes/<filename>.php
foreach (glob(ROUTE_DIR) as $userRoute) {
    require $userRoute;
}
