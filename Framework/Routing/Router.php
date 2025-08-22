<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Routing;

use Framework\Core\Boot;
use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;
use Framework\Http\RequestHelper;
use Framework\Security\Security;
use Framework\Static\Constant;
use Modules\Admin\AdminElements;
use Framework\I18n\LangSelector;
use Throwable;
use Exception;

class Router
{

    /**
     * Load default app controller
     */
    private const DEFAULT_APP = 'Page';

    /**
     * Constante que define el nombre del parámetro index.php?module={module}
     */
    private const PARAM_MODULE = 'module';

    private Security $security;
    private RequestHelper $request;
    private ConfigSettings $configSettings;
    private string $app;

    public function __construct(
        ConfigSettings $configSettings,
        Security $security,
        RequestHelper $request
    ) {
        $this->configSettings = $configSettings;
        $this->security = $security;
        $this->request = $request;

        $this->checkInstallerRedirect();
    }

    public function initBalero(): self
    {
        // Cargar helpers
        require_once Constant::LANG_HELPER;

        // Obtener parámetros de idioma y cargar archivos
        LangSelector::getLanguageParams($this->request);

        // Resolver application
        $app = $this->request->get(self::PARAM_MODULE);

        if (!$app) {
            $this->initModule(self::DEFAULT_APP);
            exit;
        }

        // Default load
        match ($app) {
            default => $this->initModule(ucfirst($app)),
        };

        return $this;
    }

    private function initModule(string $appName): void
    {
        $this->app = $appName;
        $controllerClass = "Modules\\{$appName}\\Controllers\\{$appName}Controller";

        if (!class_exists($controllerClass)) {
            ErrorConsole::handleException(new Exception("Controller class not found: $controllerClass"));
        }

        Boot::loadController($controllerClass);
    }

    private function checkInstallerRedirect(): void
    {
        try {
            if ($this->configSettings->getInstalled() === "no") {
                $currentApp = $this->request->get(self::PARAM_MODULE);
                if ($currentApp !== 'installer') {
                    $base = rtrim($this->configSettings->getBasePath(), '/');
                    header('Location: ' . $base . '/installer/');
                    exit;
                }
            }
        } catch (Throwable $e) {
            ErrorConsole::handleException($e);
        }
    }
}
