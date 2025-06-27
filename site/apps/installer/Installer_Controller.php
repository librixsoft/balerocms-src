<?php

class Installer_Controller extends Controller {

    private installer_Model $model;
    private ConfigSettings $configSettings;

    private string $check_db = '';
    private string $check_site = '';
    private string $check_admin = '';
    private string $check_icon = '<i class="icon-check"></i>';

    public function __construct() {
        $this->request = new RequestHelper();
        parent::__construct($this->request);

        $this->view = new View();
        $this->model = new installer_Model();

        $this->configSettings = new ConfigSettings();
        $this->configSettings->LoadSettings();

        $this->installButton();

        $this->initBasePath();
        $this->init();
    }

    protected function installButton(): void {
        $this->check_site = $this->check_icon;
        $this->check_admin = $this->check_icon;
    }

    public function initBasePath() {
        if (empty($this->configSettings->getBasepath())) {
            $this->configSettings->setBasepath($this->configSettings->getFullBasepath());
        }
    }

    #[Get(sr: '')]
    public function main() {
        $params = $this->getDefaultParams();
        $params['mod_rewrite_enabled'] = $this->checkModRewrite();
        $params['welcome'] = "Welcome to Balero CMS Setup Wizard.";
        $params['btn_install'] = _INSTALL_BUTTON;
        $configFilePath = LOCAL_DIR . '/site/etc/balero.config.xml';
        $params['config_writeable'] = is_writable($configFilePath);
        return $this->view->render("/views/setup_wizard.html", $params);
    }

    #[Post(sr: 'formDBInfo')]
    public function formDBInfo() {
        try {
            $this->configSettings->setDbhost($this->request->post('dbhost'));
            $this->configSettings->setDbuser($this->request->post('dbuser'));
            $this->configSettings->setDbpass($this->request->post('dbpass'));
            $this->configSettings->setDbname($this->request->post('dbname'));

            $this->check_db = $this->check_icon;
        } catch (Exception $e) {
            $this->check_db = "";
            // opcional: puedes agregar mensaje de error
        }

        $params = $this->getDefaultParams();
        return $this->view->render("/views/setup_wizard.html", $params);
    }

    #[Post(sr: 'formSiteInfo')]
    public function formSiteInfo() {
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
            // opcional: manejar error
        }

        $params = $this->getDefaultParams();
        return $this->view->render("/views/setup_wizard.html", $params);
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
            $this->configSettings->setUser($this->request->post('username'));
            $this->configSettings->setEmail($this->request->post('email'));

            $obj = new Blowfish();
            $pwd = $obj->genpwd($this->request->post('passwd'));
            $this->configSettings->setPass($pwd);

        } catch (Exception $e) {
            $this->check_admin = "";
            $params = $this->getDefaultParams();
            $params['error_message'] = $e->getMessage();
            return $this->view->render("/views/setup_wizard.html", $params);
        }

        // Renderizar siempre para mostrar cambios o errores
        $params = $this->getDefaultParams();
        $this->view->render("/views/setup_wizard.html", $params);
    }

    #[Post(sr: 'progressBar')]
    public function progressBar() {
        try {
            $this->model->install();
        } catch (Exception $e) {
            // Manejo opcional de error
        }
    }

    protected function getDefaultParams(): array {
        return [
            'title' => $this->configSettings->getTitle(),
            'page' => defined('_PAGE') ? _PAGE : '',
            'keywords' => $this->configSettings->getKeywords(),
            'description' => $this->configSettings->getDescription(),
            'basepath' => $this->configSettings->getBasepath(),

            'check_db' => $this->check_db,
            'check_site' => $this->check_site,
            'check_admin' => $this->check_admin,

            // etiquetas y textos
            'lbl_dbconfig' => _DB_CONFIG,
            'lbl_dbhost' => _DB_HOST,
            'lbl_dbusername' => _DB_USER,
            'lbl_dbpass' => _DB_PASS,
            'lbl_dbname' => _DB_NAME,
            'lbl_dbname_note' => _DB_IF_NOT_EXIST,
            'lbl_siteinfo' => _SITE_INFO,
            'lbl_basepath' => _BASEPATH,
            'lbl_basepath_note' => _NOTE_BASEPATH,
            'lbl_title' => _TITLE,
            'lbl_url' => _URL,
            'lbl_keywords' => _TAGS,
            'lbl_description' => _DESCRIPTION,
            'lbl_adminconfig' => _ADMIN_CONFIGURATION,
            'lbl_administrator' => _ADMIN,
            'lbl_pass' => _PASS,
            'lbl_retype' => _RETYPE_PASS,
            'lbl_firstname' => _NAME,
            'lbl_lastname' => _LAST_NAME,
            'lbl_email' => _EMAIL,

            // datos para inputs
            'txt_dbhost' => $this->configSettings->getDbhost(),
            'txt_dbuser' => $this->configSettings->getDbuser(),
            'txt_dbpass' => $this->configSettings->getDbpass(),
            'txt_dbname' => $this->configSettings->getDbname(),
            'txt_basepath' => $this->configSettings->getBasepath(),
            'txt_title' => $this->configSettings->getTitle(),
            'txt_url' => $this->configSettings->getUrl(),
            'txt_keywords' => $this->configSettings->getKeywords(),
            'txt_description' => $this->configSettings->getDescription(),
            'txt_administrator' => $this->configSettings->getUser(),
            'txt_pass' => '',
            'txt_retype' => '',
            'txt_firstname' => $this->configSettings->getFirstname(),
            'txt_lastname' => $this->configSettings->getLastname(),
            'txt_email' => $this->configSettings->getEmail(),

            'btn_save' => _INSTALLER_SAVE,
        ];
    }

    private function checkModRewrite(): bool {
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            return in_array('mod_rewrite', $modules);
        }
        // En caso de que no se pueda verificar, asumir false o true según convenga
        return false;
    }



}
