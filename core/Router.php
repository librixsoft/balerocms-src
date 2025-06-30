<?php

class Router
{
    private Language $lang;
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

    public function init()
    {
        $app = $this->request->get('app');

        if ($app === null) {
            $this->lang = new Language();
            $this->lang->init();
            $this->lang->init_apps_lang("virtual_page");
            $this->lang->app = "virtual_page";

            Boot::safeResolve(virtual_page_Controller::class);
            exit;
        }

        switch ($app) {
            case "admin":
                $this->login();
                break;

            case "logout":
                LoginManager::logout();
                break;

            default:
                $this->app = $app;
                $this->init_app();
                break;
        }
    }

    public function login()
    {
        $this->lang = new Language();
        $this->lang->init();
        $this->lang->init_apps_lang("admin");
        $this->lang->app = "admin";

        $loginManager = new LoginManager($this->security);
        $isAuthenticated = $loginManager->handleLogin();

        if ($isAuthenticated) {
            $this->init_mod();
        } else {
            $loginManager->showLoginForm();
        }
    }

    public function init_app()
    {
        $controllerFile = APPS_DIR . $this->app . "/" . $this->app . "_Controller.php";

        if (!file_exists($controllerFile)) {
            ErrorConsole::handleException(new Exception("Controller file not found: $controllerFile"));
        }

        $this->lang = new Language();
        $this->lang->init();
        $this->lang->init_apps_lang($this->app);
        $this->lang->app = $this->app;

        $dynamic = $this->app . "_Controller";

        Boot::safeResolve($dynamic);
        unset($this->lang);
    }

    public function init_mod()
    {
        $mod = $this->request->get("mod");

        if ($mod === null) {
            $adminControllerFile = APPS_DIR . "admin/admin_Controller.php";

            if (!file_exists($adminControllerFile)) {
                ErrorConsole::handleException(new Exception("Admin controller file not found: $adminControllerFile"));
            }

            $this->lang = new Language();
            $this->lang->app = "admin";
            $this->lang->init();
            $this->lang->init_apps_lang("admin");

            $admin_elements = new AdminElements();
            $title_mod_menu = $admin_elements->mods_menu();

            Boot::safeResolve(admin_Controller::class, [$title_mod_menu]);
            return;
        }

        $modDir = MODS_DIR . $mod;

        if (!file_exists($modDir)) {
            ErrorConsole::handleException(new Exception("Module directory not found: $modDir"));
        }

        $dynamicController = "mod_" . $mod . "_Controller";

        if (!class_exists($dynamicController)) {
            ErrorConsole::handleException(new Exception("Module controller class not found: $dynamicController"));
        }

        $admin_elements = new AdminElements();
        $title_mod_menu = $admin_elements->mods_menu();

        Boot::safeResolve($dynamicController, [$title_mod_menu]);
    }

    public function installer()
    {
        try {
            $isInstalled = $this->configSettings->getInstalled();

            if ($isInstalled === "no") {
                if (!file_exists(APPS_DIR . "installer")) {
                    ErrorConsole::handleException(new Exception("Installer application not found in: " . APPS_DIR . "installer"));
                }

                $this->lang = new Language();
                $this->lang->init();
                $this->lang->init_apps_lang("installer");

                Boot::safeResolve(installer_Controller::class);
                exit;
            }

        } catch (Throwable $e) {
            if (!file_exists(APPS_DIR . "installer")) {
                ErrorConsole::handleException(new Exception("Installer application not found in: " . APPS_DIR . "installer"));
            }

            ErrorConsole::handleException($e);
        }
    }
}
