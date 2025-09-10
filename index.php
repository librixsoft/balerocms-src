<?php

use Framework\Core\Boot;
use Framework\Routing\Router;

const _CORE_VERSION = "1.0";

require_once __DIR__ . '/bootstrap.php';

(new Boot)->instantiateClass(Router::class)->initBalero();
