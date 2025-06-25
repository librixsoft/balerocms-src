<?php

class installer_Controller extends ControllerHandler {

    public installer_Model $objModel;
    public installer_View $objView;

    private Security $security;
    protected RequestHelper $request;
    private ConfigSettings $configSettings;

    public function __construct() {
        try {

            $this->security = new Security();
            $this->request = new RequestHelper($this->security);

            parent::__construct($this->request);

            $this->objView = new installer_View();
            $this->objModel = new installer_Model();

            $this->configSettings = new ConfigSettings();
            $this->configSettings->LoadSettings();

            $this->objView->installButton();

        } catch (Exception $e) {
            $this->objView = new installer_View();

            if(!is_writable($this->getCfgFile())) {
                $MsgBox = new MsgBox(_ERROR, _CHMOD_ERROR);
                $this->objView->content .= $MsgBox->Show();
            }

            if(strpos($e->getMessage(), _UNKNOW_DATABASE)) {
                $this->objView->unknow_database_error();
                $this->objModel->createDB();
                $this->objView->check_db = "";
            } else {
                $this->objView->unknow_database_connect();
                $this->objView->check_db = "";
            }
        }

        $this->initBasePath();

        // Finalmente, llama init() para procesar el parámetro sr y mostrar vista
        $this->init();
    }

    public function initBasePath() {
        if (empty($this->configSettings->getBasepath())) {
            $this->configSettings->setBasepath($this->configSettings->getFullBasepath());
        }
    }

    public function formDBInfo() {
        if ($this->request->hasPost("submit")) {
            try {
                $this->configSettings->setDbhost($this->request->post('dbhost'));
                $this->configSettings->setDbuser($this->request->post('dbuser'));
                $this->configSettings->setDbpass($this->request->post('dbpass'));
                $this->configSettings->setDbname($this->request->post('dbname'));

                $this->objView->check_db = $this->objView->check_icon;

            } catch (Exception $e) {
                $this->objView->check_db = "";
                $this->objView->file_error($e->getMessage());
            }
        }

        header("Location: index.php");
    }

    public function formSiteInfo() {
        try {
            if ($this->request->hasPost("submit")) {
                $this->configSettings->setTitle($this->request->post('title'));
                $this->configSettings->setUrl($this->request->post('url'));
                $this->configSettings->setDescription($this->request->post('description'));
                $this->configSettings->setKeywords($this->request->post('keywords'));
                $basepath = $this->request->post("basepath");
                if ($basepath !== null) {
                    $this->configSettings->setBasepath($basepath);
                }
            }
        } catch (Exception $e) {
            // Opcional: manejar error aquí
        }

        header("Location: index.php");
    }

    public function formadminInfo() {
        if ($this->request->hasPost("submit")) {
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
                $this->objView->check_admin = "";
                $this->objView->form_field_error($e->getMessage());
                $this->main();
            }
        } else {
            header("Location: index.php");
        }
    }

    public function main() {
        $this->objView->is_mod_rewrite_enabled();
        $this->objView->wizard();
        $this->objView->Render();
    }

    public function progressBar() {
        if($this->request->hasPost("submit") && (!preg_match("/_blank/", $this->objView->getPass()))) {
            try {
                $this->objView->progressBar();
                $this->objModel->install();
            } catch (Exception $e) {
                // Opcional: manejo de error
            }
        } else {
            header("Location: index.php?app=installer");
        }
    }

    public function validate($field) {
        if(empty($field)) {
            throw new Exception(_EMPTY_FIELD . " " . $field);
        }
        return true;
    }
}
