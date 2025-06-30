<?php

namespace Framework;

use Http\Get;
use Http\Post;

use ReflectionClass;
use ReflectionMethod;

use Http\RequestHelper;

class Controller {

    protected View $view;
    protected RequestHelper $request;

    public function __construct(RequestHelper $request) {
        $this->request = $request;
        $this->view = new View();
    }

    public function init() {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $requestedSr = $this->request->get('sr') ?? '';

        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                $attrName = $attribute->getName();
                $instance = $attribute->newInstance();

                if (
                    ($attrName === Get::class && $httpMethod === 'GET') ||
                    ($attrName === Post::class && $httpMethod === 'POST')
                ) {
                    if (
                        ($instance->sr === '' && $requestedSr === '') ||
                        ($instance->sr === '/' && ($requestedSr === '' || $requestedSr === '/')) ||
                        $instance->sr === $requestedSr
                    ) {
                        $result = $this->{$method->getName()}(); // Ejecutar el método

                        if (is_string($result)) {
                            echo $result;
                            exit;
                        }

                        if (is_array($result) && isset($result['view'])) {
                            $view = new View();
                            echo $view->render($result['view'], $result['params'] ?? []);
                            exit;
                        }

                        return;
                    }
                }
            }
        }

        $this->main();
    }

    public function main() {
        echo "Método main() del Controller";
    }
}
