<?php

namespace Framework\Core;

use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Http\Route;
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

        $reflection = new \ReflectionClass($this);

        // Obtener ruta base (Route de clase), sin trim para no perder barras internas
        $classRouteAttr = $reflection->getAttributes(\Framework\Http\Route::class);
        $baseRoute = '';

        foreach ($classRouteAttr as $attr) {
            $instance = $attr->newInstance();
            error_log("Clase tiene atributo Route con sr: '{$instance->sr}'");
        }

        if (!empty($classRouteAttr)) {
            $baseRoute = $classRouteAttr[0]->newInstance()->sr;
        }

        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                $attrName = $attribute->getName();
                $instance = $attribute->newInstance();

                // Solo Get o Post
                if (
                    ($attrName === \Framework\Http\Get::class && $httpMethod === 'GET') ||
                    ($attrName === \Framework\Http\Post::class && $httpMethod === 'POST')
                ) {
                    // Concatenar ruta base y método, evitando dobles barras
                    $methodSr = $instance->sr;

                    if ($baseRoute !== '') {
                        $fullRoute = rtrim($baseRoute, '/') . '/' . ltrim($methodSr, '/');
                    } else {
                        $fullRoute = $methodSr;
                    }

                    error_log($fullRoute );

                    // Comparar exactamente con la ruta solicitada
                    if ($fullRoute === $requestedSr || ($fullRoute === '' && ($requestedSr === '' || $requestedSr === '/'))) {
                        $this->runMethod($method->getName());
                        return;
                    }
                }
            }
        }

        // Si nada coincide, error
        \Framework\Core\ErrorConsole::handleException(new \RuntimeException("Ruta no encontrada: '{$requestedSr}'"));
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
