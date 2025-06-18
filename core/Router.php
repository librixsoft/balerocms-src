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

require_once(LOCAL_DIR . "/core/RequestHelper.php");
require_once(LOCAL_DIR . "/core/Security.php");
require_once(LOCAL_DIR . "/core/boot.php");

class Router
{

    public $message;
    public $lang;

    private Security $security;
    private RequestHelper $request;

    /**
     * Public get variable controller
     */

    private string $app;


    public function __construct()
    {

        $this->security = new Security();
        $this->request = new RequestHelper($this->security);
        $this->installer();

    }

    public function init()
    {

        new boot();

        $app = $this->request->get('app');

        if ($app === null) {
            $ldr = new autoloader("virtual_page"); // cargar clases para la app
            $this->lang = new Language();
            $this->lang->init();
            $this->lang->init_apps_lang("virtual_page");
            $this->lang->app = "virtual_page";
            $app = new virtual_page_Controller();
            exit;
        }

        switch ($app) {

            case "admin":
                $this->admin_router(); // login inside this method
                break;

            case "logout";
                $this->logout();
                break;

            default:
                $this->app = $app;
                $this->init_app();
                break;

        }


    }

    /**
     * Router has the login page inside BlowFish class
     */

    public function admin_router()
    {

        $this->lang = new Language();
        $this->lang->init();
        $this->lang->init_apps_lang("admin");
        $this->lang->app = "admin";

        //echo "cokie: " . base64_decode($_COOKIE['admin_god_balero']);

        /**
         * 15-02-2015 XSS on the cookie 'counter'.
         * Reported By Gjoko Krstic <gjoko@zeroscience.mk>
         * Fixed by Anibal Gomez <anibalgomez@icloud.com>
         */
        if (isset($_COOKIE['counter'])) {
            $counter = $this->security->toInt($_COOKIE['counter']);
            if ($counter >= 5) {
                die(_LOGIN_ATTEMPS);
            }
        }

        if (!isset($_COOKIE['admin_god_balero'])) {
            if (isset($_POST['login'])) {
                $cfg = new configSettings();
                $login = new Blowfish();
                $verify = $login->verify_hash($_POST['pwd'], $cfg->pass);

                if ($_POST['usr'] == $cfg->user && $verify == TRUE) {
                    $value = base64_encode($cfg->user . ":" . $cfg->pass);
                    setcookie("admin_god_balero", $value, time() + 3600 * 24);
                    //header("Location: index.php?app=admin");
                    header("Location: ./admin");
                } else {
                    if (!isset($_COOKIE['counter'])) {
                        setcookie("counter", 1, time() + 120);
                    }
                    if (isset($_COOKIE['counter'])) {
                        $counter = $this->objSecurity->toInt($_COOKIE['counter']);
                        setcookie("counter", $counter + 1, time() + 120);
                        echo $counter;
                    }
                    $this->message = _LOGIN_ERROR;
                }
            }
        }

        if (isset($_COOKIE['admin_god_balero'])) {

            //echo("logeado");
            $cfg = new configSettings();
            $login = new Blowfish();

            $cookie = base64_decode($_COOKIE['admin_god_balero']);

            //echo $cookie;

            $pieces = explode(":", $cookie);
            $cookie_usr = $pieces[0];
            $cookie_pwd = $pieces[1];


            if ($cfg->user == $cookie_usr && $cfg->pass == $cookie_pwd) {
                //die("buscando...");
                $ldr = new autoloader("admin"); // cargar clases para la app
                $this->init_mod();
            } else {
                // remomve cookie
                setcookie("admin_god_balero", "", time() - 3600);
                die("Hash Error");
            }

        } else {

            $cfg = new configSettings();
            $login = new Blowfish();
            $login->message = $this->message;
            $login->basepath = $cfg->basepath;

            try {

                echo $login->login_form(APPS_DIR . "admin/panel/login.html");

            } catch (Exception $e) {

                die($e->getMessage());

            }

        }

        unset($this->lang);

    }

    /**
     * init() app system
     * /site/apps/
     */

    public function init_app()
    {

        if (file_exists(APPS_DIR . $this->app . "/" . $this->app . "_Controller.php")) {
            $ldr = new autoloader($this->app); // cargar clases para la app
            $this->lang = new Language();
            $dynamic = $this->app . "_Controller";
            $this->lang->init();
            $this->lang->init_apps_lang($this->app);
            $this->lang->app = $this->app;
            $app = new $dynamic();
            unset($this->lang);
        } else {
            $msg = new MsgBox("error", "dont exist");
            $theme = new ThemeLoader(LOCAL_DIR . "/themes/tundra/main.html");
            echo $theme->renderPage($array = array("content" => $msg->Show()));
        }

        /**
         * Kill app
         */

        unset($this->app);
        die();

    }

    public function init_mod()
    {

        /**
         * Buscar en esta carpeta los modulos modloader("carpeta");
         */

        if (isset($_GET['mod'])) {

            $blind_url = $this->objSecurity->antiXSS($_GET['mod']);

            switch ($blind_url) {

                case $blind_url:
                    if (file_exists(MODS_DIR . $blind_url)) {
                        //$this->lang = new Language();
                        //$this->lang->init();
                        //$this->lang->app = $blind_url;
                        //$this->lang->init_mods_lang($blind_url);
                        //include_once(LOCAL_DIR . "/site/apps/admin/mods/" . $blind_url . "/lang/en.php");
                        $dynamic = "mod_" . $blind_url . "_Controller";
                        $mod_loader = new Modloader($blind_url);
                        $admin_elements = new AdminElements();
                        $title_mod_menu = $admin_elements->mods_menu();
                        // cargar controlador de pagina de inicio (admin).
                        $settings_controller = new $dynamic($title_mod_menu);
                    } else {
                        die(_CONTROLLER_NOT_FOUND);
                    }
                    unset($this->lang);
                    break;

            }

        } else {

            /**
             * Init admin app controller
             */

            if (file_exists(APPS_DIR . "admin/admin_Controller.php")) {

                /**
                 * Load lang and wait
                 */

                $this->lang = new Language();
                $this->lang->app = "admin";
                $this->lang->init();
                $this->lang->init_apps_lang("admin");

                /**
                 * Load panel and admin controller
                 */

                $admin_elements = new AdminElements();
                $title_mod_menu = $admin_elements->mods_menu();
                // cargar controlador de pagina de inicio (admin).
                $settings_controller = new admin_Controller($title_mod_menu);

                unset($this->lang);

            } else {
                die(_CONTROLLER_ADMIN_NOT_FOUND);
            }
        }

    }

    public function installer()
    {

        $init = new boot(); // cargar nucleo

        //die("installer");

        try {

            $xml = new XMLHandler(LOCAL_DIR . "/site/etc/balero.config.xml");
            $installed = $xml->Child("system", "installed");
            $isInstalled = strpos($installed, "yes");

            if ($isInstalled === false) {

                //die("no instalado");

                if (!file_exists(APPS_DIR . "installer")) {
                    die("App installer NOT found.");
                }

                $this->lang = new Language();
                $this->lang->init();
                $this->lang->init_apps_lang("installer");

                $ldr = new autoloader("installer"); // cargar clases para la app
                $app = new installer_Controller();
                die();

            }

        } catch (Exception $e) {

            if (!file_exists(APPS_DIR . "installer")) {
                die("App installer NOT found.");
            }

            die($e->getMessage());

        }


    } // installer

    public function logout()
    {
        if (isset($_COOKIE['admin_god_balero'])) {

            try {

                /**
                 * Delete cookie admin
                 */

                setcookie("admin_god_balero", "", time() - 3600);
                //header("Location: index.php?app=admin");
                header("Location: ./admin");

            } catch (Exception $e) {

                /**
                 * forzar
                 */

                setcookie("admin_god_balero", "", time() - 1);

            }

        }
    } // end logout

}
