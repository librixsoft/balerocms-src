<?php

/**
 * boot.php
 * Autoloader central de Balero CMS
 * (c) lastprophet
 */

class boot
{
    public function __construct()
    {
        spl_autoload_register([$this, "autoload"]);
    }

    public function autoload($class)
    {

        $paths = [
            LOCAL_DIR . "/core/{$class}.php",
            APPS_DIR . "{$class}/{$class}_Controller.php",
            MODS_DIR . "{$class}/mod_{$class}_Controller.php",
        ];

        $apps = scandir(APPS_DIR);
        foreach ($apps as $appName) {
            if ($appName === "." || $appName === "..") {
                continue;
            }

            $appClassFile = APPS_DIR . "{$appName}/{$class}.php";
            if (file_exists($appClassFile)) {
                require_once($appClassFile);
                return;
            }
        }

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once($path);
                return;
            }
        }
        
    }
}
