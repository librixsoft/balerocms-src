<?php

namespace Framework\Core;

use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Http\RequestHelper;

class Controller
{
    protected View $view;
    protected RequestHelper $request;
    protected ConfigSettings $configSettings;

    public function __construct(RequestHelper $request, View $view, ConfigSettings $configSettings)
    {
        $this->request = $request;
        $this->view = $view;
        $this->configSettings = $configSettings;

        // Carga configuración y realiza inicializaciones comunes
        $this->configSettings->LoadSettings();
        $this->initBasePath();
        $this->init();
    }

    /**
     * Inicializa basepath si no está seteado.
     */
    private function initBasePath(): void
    {
        if (empty($this->configSettings->getBasepath())) {
            $this->configSettings->setBasepath($this->configSettings->getFullBasepath());
        }
    }

    public function init(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $requestedSr = $this->request->get('sr') ?? '';

        $requestedSr = trim($requestedSr, '/');

        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                $attrName = $attribute->getName();
                $instance = $attribute->newInstance();

                if (
                    ($attrName === Get::class && $httpMethod === 'GET') ||
                    ($attrName === Post::class && $httpMethod === 'POST')
                ) {
                    $methodSr = trim($instance->sr, '/');

                    if ($methodSr === $requestedSr || ($methodSr === '' && $requestedSr === '')) {
                        $this->runMethod($method->getName());
                        return;
                    }
                }
            }
        }

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
