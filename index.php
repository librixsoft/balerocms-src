<?php

define("_CORE_VERSION", "1.0");

$dir = dirname(__FILE__);
$dir = str_replace("\\", "/", $dir);
define("LOCAL_DIR", $dir);

require_once(LOCAL_DIR . "/Framework/Core/ErrorConsole.php");
require_once(LOCAL_DIR . "/Framework/Core/Boot.php");

use Framework\Core\Boot;
use Framework\Routing\Router;

new Boot();

$router = Boot::resolve(Router::class);
$router->init();
