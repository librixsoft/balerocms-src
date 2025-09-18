<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Core;

use Framework\Config\Context;
use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Http\RequestHelper;
use Framework\Security\LoginManager;
use Framework\I18n\LangSelector;

class Controller
{
    private const PARAM_TARGET = 'target';

    /****************************
     * Serán heredadas al controller hijo
     ***************************/
    // TODO: Implements autowiring
    protected View $view;
    protected RequestHelper $request;
    protected ConfigSettings $configSettings;
    protected LoginManager $loginManager;


    /**
     * Construye la plantilla base de los controllers de Balero CMS
     */
    public function initControllerAndInject(): void
    {
        // TODO: Implements autowiring
        $this->request = Context::get(RequestHelper::class);
        $this->view = Context::get(View::class);
        $this->configSettings = Context::get(ConfigSettings::class);
        $this->loginManager = Context::get(LoginManager::class);

        $this->run();
    }

    private function initBasePath(): void
    {
        $basepath = trim($this->configSettings->basepath ?? '');
        if ($basepath === '') {
            $basepath = $this->configSettings->getFullBasepath();
        }
        $this->configSettings->basepath = $basepath;
    }

    public function run(): void
    {
        $this->initBasePath();

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $requestedTarget = trim($this->request->get(self::PARAM_TARGET) ?? '', '/');

        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $classAuthAttr = $reflection->getAttributes(\Framework\Http\Auth::class);
        $classAuth = !empty($classAuthAttr) ? $classAuthAttr[0]->newInstance() : null;

        foreach ($methods as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $attrName = $attribute->getName();
                $instance = $attribute->newInstance();

                if (
                    ($attrName === Get::class && $httpMethod === 'GET') ||
                    ($attrName === Post::class && $httpMethod === 'POST')
                ) {
                    $routePattern = trim($instance->target, '/');
                    $regex = preg_replace('#\{([^}]+)\}#', '(?P<$1>[^/]+)', $routePattern);
                    $regex = '#^' . $regex . '$#';

                    if (preg_match($regex, $requestedTarget, $matches)) {
                        $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);

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

        ErrorConsole::handleException(
            new \RuntimeException("Ruta no encontrada: '{$requestedTarget}'")
        );
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

    protected function render(string $template, array $params = [], bool $useTheme = true): string
    {
        $langParams = LangSelector::getLanguageParams($this->request);
        return $this->view->render($template, array_merge($langParams, $params), $useTheme);
    }

}
