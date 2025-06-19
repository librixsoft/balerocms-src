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
        // Posibles rutas donde puede estar la clase
        $paths = [
            LOCAL_DIR . "/core/{$class}.php",
            APPS_DIR . "{$class}/{$class}_Controller.php",                      // ej: site/apps/virtual_page/virtual_page_Controller.php
            MODS_DIR . "{$class}/mod_{$class}_Controller.php",                 // ej: site/apps/admin/mods/users/mod_users_Controller.php
        ];

        // Soporte para clases dentro de cualquier app (como hacía tu autoloader personalizado)
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

        // Buscar en rutas fijas
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once($path);
                return;
            }
        }

        // Opcional: lanzar error si no se encuentra
        // throw new Exception("No se pudo cargar la clase: {$class}");
    }
}
