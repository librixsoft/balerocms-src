<?php

/**
 * boot.php
 * Autoloader central de Balero CMS
 * (c) lastprophet
 */

class Boot
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

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once($path);
                return;
            }
        }

        $apps = scandir(APPS_DIR);
        foreach ($apps as $appName) {
            if ($appName === "." || $appName === "..") continue;

            $appClassFile = APPS_DIR . "{$appName}/{$class}.php";
            if (file_exists($appClassFile)) {
                require_once($appClassFile);
                return;
            }

            $appControllerFile = APPS_DIR . "{$appName}/{$class}_Controller.php";
            if (file_exists($appControllerFile)) {
                require_once($appControllerFile);
                return;
            }
        }

        if (preg_match('/^mod_(.+)_Controller$/', $class, $matches)) {
            $mod = $matches[1]; // ahora sí será "virtual_page"
            $modFile = MODS_DIR . "{$mod}/{$class}.php";
            if (file_exists($modFile)) {
                require_once($modFile);
                return;
            }
        }
    }
}
