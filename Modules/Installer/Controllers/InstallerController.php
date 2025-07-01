<?php

namespace Modules\Installer\Controllers;

use Framework\Core\Validator;
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
            $params = InstallerViewModel::getSetupWizardParams($this->configSettings);
            return $this->view->render("resources/views/setup_wizard.html", $params);
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Post(sr: '/')]
    public function install()
    {
        $params = [];

        try {
            $installerDTO = InstallerDTO::fromRequest($this->request);
            $input = (array) $installerDTO;

            $validator = Validator::make($input)
                ->required('username', 'El nombre de usuario no puede estar vacío.')
                ->required('passwd', 'La contraseña no puede estar vacía.')
                ->match('passwd', 'passwd2', 'Las contraseñas no coinciden.')
                ->email('email', 'El correo electrónico no es válido.');

            if ($validator->fails()) {
                $params = [
                    'errors' => $validator->errors()
                ];
            } else {
                InstallerMapper::map($installerDTO, $this->configSettings);
            }
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            $params['error_message'] = $e->getMessage();
        }

        $params = InstallerViewModel::getSetupWizardParams($this->configSettings, $params);

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
