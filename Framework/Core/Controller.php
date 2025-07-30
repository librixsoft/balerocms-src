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

    /**
     * Called from Boot::instantiateClass()
     * @param RequestHelper $request
     * @param View $view
     */
    public function init(RequestHelper $request, View $view)
    {
        $this->request = $request;
        $this->view = $view;

        /**
         * ConfigSettings se obtiene desde su instancia singleton.
         * Esto garantiza que toda la aplicación comparta una única fuente
         * de configuración centralizada y previamente cargada desde XML.
         *
         * No se necesita volver a llamar a LoadSettings() ni inyectar manualmente
         * porque la instancia ya fue creada e inicializada en Boot.
         */
        $this->configSettings = ConfigSettings::getInstance();

        $this->run();
    }

    private function initBasePath(): void
    {
        if (empty($this->configSettings->getBasepath())) {
            $this->configSettings->setBasepath($this->configSettings->getFullBasepath());
        }
    }

    public function run(): void
    {
        $this->initBasePath();

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $requestedSr = trim($this->request->get('sr') ?? '', '/');

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

                    $regex = preg_replace('#\{([^}]+)\}#', '(?P<$1>[^/]+)', $routePattern);
                    $regex = '#^' . $regex . '$#';

                    if (preg_match($regex, $requestedSr, $matches)) {
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

    protected function render(string $template, array $params = []): string
    {
        $common = [
            'title' => $this->configSettings->getTitle(),
            'keywords' => $this->configSettings->getKeywords(),
            'description' => $this->configSettings->getDescription(),
            'basepath' => $this->configSettings->getBasepath(),
        ];

        return $this->view->render($template, array_merge($common, $params));
    }
}
