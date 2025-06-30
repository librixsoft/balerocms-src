<?php

namespace Modules\Installer\Controllers;

use Framework\View;
use Framework\ConfigSettings;
use Framework\ErrorConsole;
use Http\RequestHelper;
use Modules\Installer\Models\InstallerModel;
use Framework\Controller;
use Framework\Blowfish;
use Exception;

use Http\Get;
use Http\Post;

class InstallerController extends Controller
{
    protected View $view;
    protected RequestHelper $request;
    protected InstallerModel $model;
    protected ConfigSettings $configSettings;

    public function __construct(
        RequestHelper $request,
        View $view,
        InstallerModel $model,
        ConfigSettings $configSettings
    ) {
        $this->request = $request;
        $this->view = $view;
        $this->model = $model;
        $this->configSettings = $configSettings;

        $this->configSettings->LoadSettings();

        parent::__construct($this->request);
        $this->initBasePath();
        $this->init();
    }

    private function initBasePath(): void
    {
        if (empty($this->configSettings->getBasepath())) {
            $this->configSettings->setBasepath($this->configSettings->getFullBasepath());
        }
    }

    #[Get(sr: '/')]
    public function main()
    {
        try {
            $params = $this->getDefaultParams();
            $params['mod_rewrite_enabled'] = $this->checkModRewrite();
            $params['welcome'] = "Welcome to Balero CMS Setup Wizard.";
            $params['btn_install'] = "Instalar";

            $configFilePath = LOCAL_DIR . '/site/etc/balero.config.xml';
            $params['config_writeable'] = is_writable($configFilePath);

            return $this->view->render("views/setup_wizard.html", $params);
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Post(sr: '/')]
    public function install()
    {
        try {
            $this->configSettings->setDbhost($this->request->post('dbhost'));
            $this->configSettings->setDbuser($this->request->post('dbuser'));
            $this->configSettings->setDbpass($this->request->post('dbpass'));
            $this->configSettings->setDbname($this->request->post('dbname'));

            $this->configSettings->setTitle($this->request->post('title'));
            $this->configSettings->setUrl($this->request->post('url'));
            $this->configSettings->setDescription($this->request->post('description'));
            $this->configSettings->setKeywords($this->request->post('keywords'));

            $basepath = $this->request->post("basepath");
            if ($basepath !== null) {
                $this->configSettings->setBasepath($basepath);
            }

            if (empty($this->request->post("username"))) {
                throw new Exception(_EMPTY_USERNAME);
            }

            if (empty($this->request->post("passwd"))) {
                throw new Exception(_EMPTY_PASSWORD);
            }

            if ($this->request->post("passwd") !== $this->request->post("passwd2")) {
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

            $params = $this->getSetupWizardParams();
        } catch (Exception $e) {
            $params = $this->getSetupWizardParams([
                'error_message' => $e->getMessage()
            ]);
        }

        return $this->view->render("views/setup_wizard.html", $params);
    }

    #[Post(sr: 'progressBar')]
    public function progressBar()
    {
        try {
            $this->model->install();
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
        }

        $params = $this->getDefaultParams();
        return $this->view->render("views/progressBar.html", $params);
    }

    protected function getDefaultParams(): array
    {
        return [
            'title' => $this->configSettings->getTitle(),
            'page' => defined('_PAGE') ? _PAGE : '',
            'keywords' => $this->configSettings->getKeywords(),
            'description' => $this->configSettings->getDescription(),
            'basepath' => $this->configSettings->getBasepath(),

// etiquetas
            'lbl_dbconfig' => 'Database Configuration',
            'lbl_dbhost' => 'Database Host',
            'lbl_dbusername' => 'Database User',
            'lbl_dbpass' => 'Database Password',
            'lbl_dbname' => 'Database Name',
            'lbl_dbname_note' => 'If database does not exist, it will be created',
            'lbl_siteinfo' => 'Site Information',
            'lbl_basepath' => 'Base Path',
            'lbl_basepath_note' => 'Note about base path',
            'lbl_title' => 'Site Title',
            'lbl_url' => 'Site URL',
            'lbl_keywords' => 'Keywords',
            'lbl_description' => 'Description',
            'lbl_adminconfig' => 'Administrator Configuration',
            'lbl_administrator' => 'Administrator',
            'lbl_pass' => 'Password',
            'lbl_retype' => 'Retype Password',
            'lbl_firstname' => 'First Name',
            'lbl_lastname' => 'Last Name',
            'lbl_email' => 'Email Address',


            // valores
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

            'btn_save' => "Guardar",
        ];
    }

    private function getSetupWizardParams(array $extraParams = []): array
    {
        $params = $this->getDefaultParams();
        $params['mod_rewrite_enabled'] = $this->checkModRewrite();
        $params['welcome'] = "Welcome to Balero CMS Setup Wizard.";
        $params['btn_install'] = _INSTALL_BUTTON;
        $configFilePath = LOCAL_DIR . '/site/etc/balero.config.xml';
        $params['config_writeable'] = is_writable($configFilePath);

        return array_merge($params, $extraParams);
    }

    private function checkModRewrite(): bool
    {
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            return in_array('mod_rewrite', $modules);
        }

        return false;
    }
}
