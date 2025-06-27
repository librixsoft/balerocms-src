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
                    if (($instance->sr === '' && $requestedSr === '') || $instance->sr === $requestedSr) {
                        $result = $this->{$method->getName()}(); // Ejecutar el método

                        // 🔁 render automático si retorna una vista
                        if (is_string($result)) {
                            $view = new View();
                            $view->renderLayout($result);
                            exit;
                        }

                        if (is_array($result) && isset($result['view'])) {
                            $view = new View();
                            $view->renderLayout($result['view'], $result['params'] ?? []);
                            exit;
                        }

                        return;
                    }
                }
            }
        }

        $this->main();
    }

    protected function view(string $layoutPath, array $params = []): array
    {
        return ['view' => $layoutPath, 'params' => $params];
    }


    public function main() {
        echo "Método main() del Controller";
    }
}
