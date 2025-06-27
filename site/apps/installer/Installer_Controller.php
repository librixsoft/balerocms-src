<?php
// 3. Clase Installer_Controller con métodos y rutas personalizadas

class Installer_Controller extends Controller {

    private installer_Model $model;
    private installer_View $view;

    private ConfigSettings $configSettings;

    public function __construct() {
        try {
            $this->request = new RequestHelper();
            parent::__construct($this->request);

            $this->view = new installer_View();
            $this->model = new installer_Model();

            $this->configSettings = new ConfigSettings();
            $this->configSettings->LoadSettings();

            $this->view->installButton();
        } catch (Exception $e) {
            $this->view = new installer_View();

            if (!is_writable($this->getCfgFile())) {
                $MsgBox = new MsgBox(_ERROR, _CHMOD_ERROR);
                $this->view->content .= $MsgBox->Show();
            }

            if (strpos($e->getMessage(), _UNKNOW_DATABASE)) {
                $this->view->unknow_database_error();
                $this->model->createDB();
                $this->view->check_db = "";
            } else {
                $this->view->unknow_database_connect();
                $this->view->check_db = "";
            }
        }

        $this->initBasePath();

        $this->init();
    }

    public function initBasePath() {
        if (empty($this->configSettings->getBasepath())) {
            $this->configSettings->setBasepath($this->configSettings->getFullBasepath());
        }
    }

    #[Post(sr: 'formDBInfo')]
    public function formDBInfo() {
        try {
            $this->configSettings->setDbhost($this->request->post('dbhost'));
            $this->configSettings->setDbuser($this->request->post('dbuser'));
            $this->configSettings->setDbpass($this->request->post('dbpass'));
            $this->configSettings->setDbname($this->request->post('dbname'));

            $this->view->check_db = $this->view->check_icon;

        } catch (Exception $e) {
            $this->view->check_db = "";
            $this->view->file_error($e->getMessage());
        }

        header("Location: index.php");
    }


    #[Post(sr: 'formSiteInfo')]
    public function formSiteInfo()
    {
        try {
            $this->configSettings->setTitle($this->request->post('title'));
            $this->configSettings->setUrl($this->request->post('url'));
            $this->configSettings->setDescription($this->request->post('description'));
            $this->configSettings->setKeywords($this->request->post('keywords'));
            $basepath = $this->request->post("basepath");
            if ($basepath !== null) {
                $this->configSettings->setBasepath($basepath);
            }
        } catch (Exception $e) {
            // Opcional: manejar error aquí
        }

        header("Location: index.php");
    }

    #[Post(sr: 'formadminInfo')]
    public function formadminInfo() {
        try {
            if (empty($this->request->post("username"))) {
                throw new Exception(_EMPTY_USERNAME);
            }
            if (empty($this->request->post("passwd"))) {
                throw new Exception(_EMPTY_PASSWORD);
            }
            if ($this->request->post("passwd") != $this->request->post("passwd2")) {
                throw new Exception(_PASSWORDS_DONT_MATCH);
            }
            if (!filter_var($this->request->post("email"), FILTER_VALIDATE_EMAIL)) {
                throw new Exception(_INDALID_EMAIL);
            }

            $this->configSettings->setLastname($this->request->post('lastname'));
            $this->configSettings->setNewsletter($this->request->post('newsletter'));
            $this->configSettings->setUser($this->request->post('username'));
            $this->configSettings->setEmail($this->request->post('email'));

            $obj = new Blowfish();
            $pwd = $obj->genpwd($this->request->post('passwd'));
            $this->configSettings->setPass($pwd);

            header("Location: index.php");
        } catch (Exception $e) {
            $this->view->check_admin = "";
            $this->view->form_field_error($e->getMessage());
            $this->main();
        }
    }


    #[Get(sr: '')]  // ruta raíz para GET (ejemplo index.php?app=installer sin path)
    public function main() {
        $this->view->is_mod_rewrite_enabled();
        $this->view->wizard();
        $this->view->renderView();
    }

    #[Post(sr: 'progressBar')]
    public function progressBar()
    {
        try {
            $this->view->progressBar();
            $this->model->install();
        } catch (Exception $e) {
            // Manejo opcional de error
        }
    }

}
