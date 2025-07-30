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
use Framework\Security\LoginManager;
use Modules\Admin\AdminElements;
use Throwable;
use Exception;

use Framework\I18n\LangManager;

class Router
{

    /**
     * Load default app controller
     */
    private const DEFAULT_APP = 'Page';

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

    public function init(): void
    {
        // Cargar helpers
        require_once LOCAL_DIR . '/Framework/I18n/lang_helper.php';

        // Detectar idioma y validar
        $lang = $_GET['lang'] ?? $_SESSION['lang'] ?? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
        $supported = ['en', 'es']; // Agrega más si es necesario

        if (!in_array($lang, $supported)) {
            $lang = 'en';
        }

        $_SESSION['lang'] = $lang;

        // ✅ Nuevo esquema: cargamos múltiples archivos desde carpeta
        LangManager::load($lang, LOCAL_DIR . '/resources/lang');

        // Resolver application
        $app = $this->request->get('app');

        // Default app load, before swtich case
        if (!$app) {
            $this->initApp(self::DEFAULT_APP);
            exit;
        }

        // before swtich case
        match ($app) {
            'logout' => LoginManager::logout(), // TODO: Move to admin controller login endpoint
            default  => $this->initApp(ucfirst($app)),
        };
    }

    private function initApp(string $appName): void
    {
        $this->app = $appName;
        $controllerClass = "Modules\\{$appName}\\Controllers\\{$appName}Controller";

        if (!class_exists($controllerClass)) {
            ErrorConsole::handleException(new Exception("Controller class not found: $controllerClass"));
        }

        Boot::loadController($controllerClass);
    }

    // TODO: Move to admin controller login endpoint
    private function handleAdmin(): void
    {
        $loginManager = new LoginManager($this->security);

        if ($loginManager->handleLogin()) {
            $this->initAdminModule();
        } else {
            $loginManager->showLoginForm();
        }
    }

    // TODO: Move to admin controller login endpoint
    // TODO: Delete this method logic because  admin controlers modules will be integrated as admin controllers
    private function initAdminModule(): void
    {
        $mod = $this->request->get("mod");
        $adminElements = new AdminElements();
        $menuData = $adminElements->mods_menu();

        if (!$mod) {
            Boot::loadController('Modules\\Admin\\Controllers\\AdminController', [$menuData]);
            return;
        }

        $modName = ucfirst($mod);
        $controllerClass = "Modules\\Admin\\Controllers\\mod_{$modName}_Controller";

        if (!class_exists($controllerClass)) {
            ErrorConsole::handleException(new Exception("Module controller class not found: $controllerClass"));
        }

        Boot::loadController($controllerClass, [$menuData]);
    }

    private function checkInstallerRedirect(): void
    {
        try {
            if ($this->configSettings->getInstalled() === "no") {
                $currentApp = $this->request->get('app');
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
