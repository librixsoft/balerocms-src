<?php

namespace Framework\Core;

use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Http\RequestHelper;
use ReflectionClass;
use ReflectionMethod;
use Framework\Core\ErrorConsole;

class Controller
{
    protected View $view;
    protected RequestHelper $request;

    public function __construct(RequestHelper $request, View $view)
    {
        $this->request = $request;
        $this->view = $view;
    }

    public function init(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $requestedSr = $this->request->get('sr') ?? '';
        $requestedSr = ltrim($requestedSr, '/'); // dejamos '/' internos para permitir rutas como home/setup

        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                $attrName = $attribute->getName();
                $instance = $attribute->newInstance();

                // Solo Get o Post
                if (
                    ($attrName === Get::class && $httpMethod === 'GET') ||
                    ($attrName === Post::class && $httpMethod === 'POST')
                ) {
                    $methodSr = ltrim($instance->sr, '/');

                    if ($methodSr === $requestedSr || ($methodSr === '' && $requestedSr === '')) {
                        $this->runMethod($method->getName());
                        return;
                    }
                }
            }
        }

        // Si nada coincide, error
        ErrorConsole::handleException(new \RuntimeException("Ruta no encontrada: '{$requestedSr}'"));
    }

    private function runMethod(string $methodName): void
    {
        $result = $this->{$methodName}();

        if (is_string($result)) {
            echo $result;
            exit;
        }

        if (is_array($result) && isset($result['view'])) {
            echo $this->view->render($result['view'], $result['params'] ?? []);
            exit;
        }
    }
}
