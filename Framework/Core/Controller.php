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
                    $routePattern = trim($instance->sr, '/');

                    // Soporta rutas dinámicas: /pagina/{slug}
                    $regex = preg_replace('#\{([^}]+)\}#', '(?P<$1>[^/]+)', $routePattern);
                    $regex = '#^' . $regex . '$#';

                    if (preg_match($regex, $requestedSr, $matches)) {
                        // Filtrar solo claves con nombre (no índices numéricos)
                        $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);

                        $this->runMethod($method->getName(), $params);
                        return;
                    }
                }
            }
        }

        ErrorConsole::handleException(new \RuntimeException("Ruta no encontrada: '{$requestedSr}'"));
    }

    private function runMethod(string $methodName, array $params = []): void
    {
        $result = $this->{$methodName}(...$params);

        if (is_string($result)) {
            echo $result;
            exit;
        }

        if (is_array($result) && isset($result['view'])) {
            echo $this->render($result['view'], $result['params'] ?? []);
            exit;
        }
    }

    /**
     * Renderiza una vista e inyecta parámetros comunes automáticamente.
     */
    protected function render(string $template, array $params = []): string
    {
        // Define global model view parameters to pass al view/controllers
        $common = [
            'title' => $this->configSettings->getTitle(),
            'keywords' => $this->configSettings->getKeywords(),
            'description' => $this->configSettings->getDescription(),
            'basepath' => $this->configSettings->getBasepath(),
        ];

        // El controlador puede sobreescribir cualquier valor común
        $params = array_merge($common, $params);

        return $this->view->render($template, $params);
    }
}
