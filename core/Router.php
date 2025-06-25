<?php

/**
 *
 * Router.php
 * (c) Feb 26, 2013 lastprophet
 * @author Anibal Gomez (lastprophet)
 * Balero CMS Open Source
 * Proyecto %100 mexicano bajo la licencia GNU.
 * PHP P.O.O. (M.V.C.)
 * Contacto: anibalgomez@icloud.com
 *
 **/

class Router
{

    public $lang;

    private Security $security;
    private RequestHelper $request;

    private string $app;


    public function __construct()
    {

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

        new boot();

        try {

            $xml = new XMLHandler(LOCAL_DIR . "/site/etc/balero.config.xml");
            $installed = $xml->Child("system", "installed");
            $isInstalled = strpos($installed, "yes");

            if ($isInstalled === false) {

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
