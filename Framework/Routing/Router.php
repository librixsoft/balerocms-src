<?php

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

class Router
{
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
        $app = $this->request->get('app');

        if (!$app) {
            Boot::safeResolve('Modules\\VirtualPage\\Controllers\\VirtualPageController');
            exit;
        }

        match ($app) {
            'admin'  => $this->handleAdmin(),
            'logout' => LoginManager::logout(),
            default  => $this->initApp(ucfirst($app)),
        };
    }

    private function handleAdmin(): void
    {
        $loginManager = new LoginManager($this->security);

        if ($loginManager->handleLogin()) {
            $this->initAdminModule();
        } else {
            $loginManager->showLoginForm();
        }
    }

    private function initApp(string $appName): void
    {
        $this->app = $appName;
        $controllerClass = "Modules\\{$appName}\\Controllers\\{$appName}Controller";

        if (!class_exists($controllerClass)) {
            ErrorConsole::handleException(new Exception("Controller class not found: $controllerClass"));
        }

        Boot::safeResolve($controllerClass);
    }

    private function initAdminModule(): void
    {
        $mod = $this->request->get("mod");
        $adminElements = new AdminElements();
        $menuData = $adminElements->mods_menu();

        if (!$mod) {
            Boot::safeResolve('Modules\\Admin\\Controllers\\AdminController', [$menuData]);
            return;
        }

        $modName = ucfirst($mod);
        $controllerClass = "Modules\\Admin\\Controllers\\mod_{$modName}_Controller";

        if (!class_exists($controllerClass)) {
            ErrorConsole::handleException(new Exception("Module controller class not found: $controllerClass"));
        }

        Boot::safeResolve($controllerClass, [$menuData]);
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
