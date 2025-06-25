<?php

class ControllerHandler {
    protected Security $objSecurity;

    public function __construct() {
        $this->objSecurity = new Security();
        $this->init();
    }

    protected function init() {
        if (isset($_GET['sr'])) {
            $sr = $_GET['sr'];

            if (!isset($_GET['app'])) {
                die(_GET_APP_DONT_EXIST);
            }

            $shield_var = $this->objSecurity->antiXSS($_GET['app']);
            $class_methods = get_class_methods($this);

            if (in_array($sr, $class_methods)) {
                $this->$sr();
            } else {
                // Método no existe
                die("El método '$sr' no existe en el controlador.");
            }
        } else {
            $this->main();
        }
    }

    // Método por defecto para cuando no hay sr
    public function main() {
        echo "Método main() del ControllerHandler";
    }
}
