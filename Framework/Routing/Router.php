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

        $this->installer();
    }

    public function init(): void
    {
        $app = $this->request->get('app');

        if ($app === null) {
            Boot::safeResolve('Modules\\VirtualPage\\Controllers\\VirtualPageController');
            exit;
        }

        switch ($app) {
            case 'admin':
                $this->login();
                break;

            case 'logout':
                LoginManager::logout();
                break;

            default:
                $this->app = ucfirst($app);
                $this->init_app();
                break;
        }
    }

    private function login(): void
    {

        $loginManager = new LoginManager($this->security);
        $isAuthenticated = $loginManager->handleLogin();

        if ($isAuthenticated) {
            $this->init_mod();
        } else {
            $loginManager->showLoginForm();
        }
    }

    private function init_app(): void
    {
        $controllerClass = "Modules\\{$this->app}\\Controllers\\{$this->app}Controller";

        if (!class_exists($controllerClass)) {
            ErrorConsole::handleException(new Exception("Controller class not found: $controllerClass"));
        }


        Boot::safeResolve($controllerClass);

    }

    private function init_mod(): void
    {
        $mod = $this->request->get("mod");

        if ($mod === null) {

            $admin_elements = new AdminElements();
            $title_mod_menu = $admin_elements->mods_menu();

            Boot::safeResolve('Modules\\Admin\\Controllers\\AdminController', [$title_mod_menu]);
            return;
        }

        $modName = ucfirst($mod);
        $controllerClass = "Modules\\Admin\\Controllers\\mod_{$modName}_Controller";

        if (!class_exists($controllerClass)) {
            ErrorConsole::handleException(new Exception("Module controller class not found: $controllerClass"));
        }

        $admin_elements = new AdminElements();
        $title_mod_menu = $admin_elements->mods_menu();

        Boot::safeResolve($controllerClass, [$title_mod_menu]);
    }

    private function installer(): void
    {
        try {
            if ($this->configSettings->getInstalled() === "no") {
                Boot::safeResolve('Modules\\Installer\\Controllers\\InstallerController');
                exit;
            }

        } catch (Throwable $e) {
            ErrorConsole::handleException($e);
        }
    }

}
