<?php

define("_CORE_VERSION", "0.9");

$dir = dirname(__FILE__); // Windows Servers
$dir = str_replace("\\", "/", $dir);

define("LOCAL_DIR", $dir);
define("APPS_DIR", LOCAL_DIR . "/site/apps/");
define("MODS_DIR", LOCAL_DIR . "/site/apps/admin/mods/");

require_once(LOCAL_DIR . "/core/ErrorConsole.php");
ErrorConsole::register();

require_once(LOCAL_DIR . "/core/Attributes.php");
require_once(LOCAL_DIR . "/core/Boot.php");
new Boot();

$objHeaders = new CMSHeaders();
$objRouter = new Router();
$objHeaders->cmsHeaders();
$objRouter->init();