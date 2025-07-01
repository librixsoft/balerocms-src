<?php

namespace Modules\Installer\Controllers;

use Framework\Core\View;
use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;
use Framework\Http\RequestHelper;
use Modules\Installer\Mapper\InstallerMapper;
use Modules\Installer\Models\InstallerModel;
use Framework\Core\Controller;
use Exception;
use Modules\Installer\DTO\InstallerDTO;
use Modules\Installer\Views\InstallerViewModel;

use Framework\Http\Get;
use Framework\Http\Post;

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
            $params = InstallerViewModel::getDefaultParams($this->configSettings);
            $params['mod_rewrite_enabled'] = $this->checkModRewrite();
            $params['welcome'] = "Welcome to Balero CMS Setup Wizard.";
            $params['btn_install'] = "Instalar";

            $configFilePath = LOCAL_DIR . '/resources/config/balero.config.xml';
            $params['config_writeable'] = is_writable($configFilePath);

            return $this->view->render("resources/views/setup_wizard.html", $params);
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Post(sr: '/')]
    public function install()
    {
        $errors = [];

        try {
            $installerDTO = InstallerDTO::fromRequest($this->request);

            // Validaciones suaves sin excepciones
            if (empty($installerDTO->username)) {
                $errors['username'] = 'El nombre de usuario no puede estar vacío.';
            }
            /**
            if (empty($installerDTO->passwd)) {
                $errors['passwd'] = 'La contraseña no puede estar vacía.';
            } elseif ($installerDTO->passwd !== $installerDTO->passwd2) {
                $errors['passwd2'] = 'Las contraseñas no coinciden.';
            }

            if (!filter_var($installerDTO->email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'El email ingresado no es válido.';
            }}**/

            if (!empty($errors)) {
                // Vista con errores + datos para repoblar
                $params = InstallerViewModel::getSetupWizardParams($this->configSettings, [
                    'errors' => $errors,
                    'form_data' => (array) $installerDTO,
                ]);

                return $this->view->render("resources/views/setup_wizard.html", $params);
            }

            // Si todo está bien
            InstallerMapper::map($installerDTO, $this->configSettings);

            $params = InstallerViewModel::getSetupWizardParams($this->configSettings);

        } catch (Exception $e) {
            ErrorConsole::handleException($e);

            $params = InstallerViewModel::getSetupWizardParams($this->configSettings, [
                'error_message' => $e->getMessage(),
            ]);
        }

        return $this->view->render("resources/views/setup_wizard.html", $params);
    }


    #[Post(sr: 'progressBar')]
    public function progressBar()
    {
        try {
            $this->model->install();
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
        }

        $params = InstallerViewModel::getDefaultParams($this->configSettings);
        return $this->view->render("resources/views/progressBar.html", $params);
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
