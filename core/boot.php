<?php

class boot {

    protected $class;

    public function __construct() {
        spl_autoload_register([$this, "autoload"]);
    }

    public function autoload($class) {
        $this->class = $class;

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

    }
}
