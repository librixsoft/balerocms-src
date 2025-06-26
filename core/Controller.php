<?php

class Controller {
    protected RequestHelper $request;

    public function __construct(RequestHelper $request) {
        $this->request = $request;
        // No llamar a init() aquí para no usar propiedades no inicializadas
    }

    public function init() {
        $sr = $this->request->get('sr');

        if ($sr !== null && $sr !== '') {
            $app = $this->request->get('app');

            if ($app === null || $app === '') {
                die(_GET_APP_DONT_EXIST);
            }

            $class_methods = get_class_methods($this);

            if (in_array($sr, $class_methods)) {
                $this->$sr();
            } else {
                die("El método '$sr' no existe en el controlador.");
            }
        } else {
            $this->main();
        }
    }

    public function main() {
        echo "Método main() del Controller";
    }
}
