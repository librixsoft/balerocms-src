<?php
// 2. Clase base Controller

class Controller {

    protected RequestHelper $request;

    public function __construct(RequestHelper $request) {
        $this->request = $request;
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
                    if ($instance->sr === '' && $requestedSr === '') {
                        $this->{$method->getName()}();
                        return;
                    }

                    if ($instance->sr === $requestedSr) {
                        $this->{$method->getName()}();
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
