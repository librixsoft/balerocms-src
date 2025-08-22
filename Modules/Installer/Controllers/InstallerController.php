<?php

namespace Modules\Installer\Controllers;

use Framework\Core\Validator;
use Framework\Core\Controller;
use Framework\Static\Redirect;
use Framework\Static\Flash;
use Modules\Installer\Mapper\InstallerMapper;
use Modules\Installer\Models\InstallerModel;
use Modules\Installer\DTO\InstallerDTO;
use Modules\Installer\Views\InstallerViewModel;
use Framework\Http\Get;
use Framework\Http\Post;

class InstallerController extends Controller
{
    protected InstallerModel $model;
    protected InstallerViewModel $installerViewModel;

    public function __construct(
        InstallerModel $model,
        InstallerViewModel $installerViewModel
    ) {
        $this->model = $model;
        $this->installerViewModel = $installerViewModel;
    }

    #[Get('/')]
    public function home()
    {
        // Recolectar errores y datos cacheados
        $params = [];

        if (Flash::has('errors')) {
            $params['errors'] = Flash::get('errors');
        }

        if (Flash::has('cacheFormData')) {
            $params['cacheFormData'] = Flash::get('cacheFormData');
        }

        // Obtener todos los parámetros del instalador listos para render
        $params = $this->installerViewModel->setInstallerParams($params);

        return $this->render("installer/setup_wizard.html", $params);
    }

    #[Post('/install')]
    public function postInstall()
    {
        $installerDTO = InstallerDTO::fromRequest($this->request);
        $input = (array) $installerDTO;

        $validator = Validator::make($input)
            ->required('username', 'El nombre de usuario no puede estar vacío.')
            ->required('passwd', 'La contraseña no puede estar vacía.')
            ->match('passwd', 'passwd2', 'Las contraseñas no coinciden.')
            ->email('email', 'El correo electrónico no es válido.');

        if ($validator->fails()) {
            Flash::set('errors', $validator->errors());
            Flash::set('cacheFormData', $input);
        } else {
            InstallerMapper::map($installerDTO, $this->configSettings);
        }

        // PRG
        Redirect::to("/installer/");
    }

    #[Get('progressBar')]
    public function getProgressBar()
    {
        $params = $this->installerViewModel->setInstallerParams();
        return $this->render("installer/progressBar.html", $params);
    }

    #[Post('progressBar')]
    public function postProgressBar()
    {
        $this->model->install();
        Redirect::to("/installer/progressBar");
    }
}
