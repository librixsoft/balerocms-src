<?php

define("_CORE_VERSION", "1.0");

$dir = dirname(__FILE__);
$dir = str_replace("\\", "/", $dir);
define("LOCAL_DIR", $dir);

require_once(LOCAL_DIR . "/core/Framework/ErrorConsole.php");
require_once(LOCAL_DIR . "/core/Framework/Boot.php");

use Framework\Boot;
use Router\Router;
use Http\CMSHeaders;

new Boot();

$headers = new CMSHeaders();
$headers->cmsHeaders();

$router = Boot::resolve(Router::class);
$router->init();
