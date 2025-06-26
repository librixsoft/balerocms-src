<?php

class Router
{

    private Language $lang;

    private Security $security;
    private RequestHelper $request;

    private ConfigSettings $configSettings;

    private string $app;


    public function __construct()
    {

        $this->configSettings = new ConfigSettings();
        $this->security = new Security();
        $this->request = new RequestHelper($this->security);
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
            new virtual_page_Controller();
            exit;
        }

        switch ($app) {

            case "admin":
                $this->login();
                break;

            case "logout";
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

        if (file_exists(APPS_DIR . $this->app . "/" . $this->app . "_Controller.php")) {
            $this->lang = new Language();
            $dynamic = $this->app . "_Controller";
            $this->lang->init();
            $this->lang->init_apps_lang($this->app);
            $this->lang->app = $this->app;
            new $dynamic();
            unset($this->lang);
        } else {
            $msg = new MsgBox("error", "dont exist");
            $theme = new ThemeLoader(LOCAL_DIR . "/themes/tundra/main.html");
            echo $theme->renderPage($array = array("content" => $msg->Show()));
        }

    }

    public function init_mod()
    {
        $mod = $this->request->get("mod");

        if ($mod === null) {
            $adminControllerFile = APPS_DIR . "admin/admin_Controller.php";

            if (!file_exists($adminControllerFile)) {
                die(_CONTROLLER_ADMIN_NOT_FOUND);
            }

            $this->lang = new Language();
            $this->lang->app = "admin";
            $this->lang->init();
            $this->lang->init_apps_lang("admin");

            $admin_elements = new AdminElements();
            $title_mod_menu = $admin_elements->mods_menu();

            new admin_Controller($title_mod_menu);
            return;
        }

        $modDir = MODS_DIR . $mod;

        if (!file_exists($modDir)) {
            die(_CONTROLLER_NOT_FOUND);
        }

        $dynamicController = "mod_" . $mod . "_Controller";

        if (!class_exists($dynamicController)) {
            die("No se pudo cargar la clase $dynamicController");
        }

        $admin_elements = new AdminElements();
        $title_mod_menu = $admin_elements->mods_menu();

        new $dynamicController($title_mod_menu);


    }



    public function installer()
    {

        try {

            $isInstalled = $this->configSettings->getInstalled();

            if ($isInstalled === "no") {

                if (!file_exists(APPS_DIR . "installer")) {
                    die("App installer NOT found.");
                }

                $this->lang = new Language();
                $this->lang->init();
                $this->lang->init_apps_lang("installer");
                new installer_Controller();
                die();

            }

        } catch (Exception $e) {

            if (!file_exists(APPS_DIR . "installer")) {
                die("App installer NOT found.");
            }

            die($e->getMessage());

        }

    }

}
