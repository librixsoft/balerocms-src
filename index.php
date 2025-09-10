<?php

use Framework\Core\Boot;
use Framework\Routing\Router;

const _CORE_VERSION = "1.0";

$dir = dirname(__FILE__);
$dir = str_replace("\\", "/", $dir);
define("LOCAL_DIR", $dir);

require_once(LOCAL_DIR . "/Framework/Core/ErrorConsole.php");
require_once(LOCAL_DIR . "/Framework/Core/Boot.php");
require_once(LOCAL_DIR . "/Framework/Static/Constant.php");

$autoload = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoload)) {
    require $autoload;
}

(new Boot)::instantiateClass(Router::class)->initBalero();

