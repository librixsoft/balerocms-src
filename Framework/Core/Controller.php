<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Core;

use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Http\RequestHelper;
use Framework\Security\LoginManager;
use Framework\I18n\LangSelector;

class Controller
{

    /**
     * Constante que define el nombre del parámetro secundario index.php?module=module&target={target}
     */
    private const PARAM_TARGET = 'target';

    /****************************
     * Seran heredado al controller
     * hijo no se necesita redeclarar
     ***************************/
    protected View $view;
    protected RequestHelper $request;
    protected ConfigSettings $configSettings;
    protected LoginManager $loginManager;

    /**
     * Called from Boot::loadController()
     * @param RequestHelper $request
     * @param View $view
     */
    public function initControllerAndInject(
        RequestHelper $request,
        View $view,
        ConfigSettings $configSettings,
        LoginManager $loginManager
    )
    {
        $this->request = $request;
        $this->view = $view;
        $this->configSettings = $configSettings;
        $this->loginManager = $loginManager;

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
        $requestedTarget = trim($this->request->get(self::PARAM_TARGET) ?? '', '/');

        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Leer atributo Auth a nivel de clase
        $classAuthAttr = $reflection->getAttributes(\Framework\Http\Auth::class);
        $classAuth = !empty($classAuthAttr) ? $classAuthAttr[0]->newInstance() : null;

        foreach ($methods as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                $attrName = $attribute->getName();
                $instance = $attribute->newInstance();

                if (
                    ($attrName === Get::class && $httpMethod === 'GET') ||
                    ($attrName === Post::class && $httpMethod === 'POST')
                ) {
                    // $target = index.php?controller=...&target=$method
                    $routePattern = trim($instance->target, '/');
                    $regex = preg_replace('#\{([^}]+)\}#', '(?P<$1>[^/]+)', $routePattern);
                    $regex = '#^' . $regex . '$#';

                    if (preg_match($regex, $requestedTarget, $matches)) {
                        $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);

                        // Chequeo Auth a nivel de método
                        $methodAuthAttr = $method->getAttributes(\Framework\Http\Auth::class);
                        $auth = !empty($methodAuthAttr) ? $methodAuthAttr[0]->newInstance() : $classAuth;

                        if ($auth && $auth->required && !$this->loginManager->isLoggedIn()) {
                            die("Unauthorized - login required");
                        }

                        $this->runMethod($method->getName(), $params);
                        return;
                    }
                }
            }
        }

        ErrorConsole::handleException(new \RuntimeException("Ruta no encontrada: '{$requestedTarget}'"));
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

        $langParams = LangSelector::getParams($this->request);

        return $this->view->render($template, array_merge($common, $langParams, $params));
    }


}
