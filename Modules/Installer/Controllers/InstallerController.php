<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Installer\Controllers;

use Framework\Core\Validator;
use Modules\Installer\Mapper\InstallerMapper;
use Modules\Installer\Models\InstallerModel;
use Framework\Core\Controller;
use Modules\Installer\DTO\InstallerDTO;
use Modules\Installer\Views\InstallerViewModel;
use Framework\I18n\LangSelector;
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
        $params = array_merge(
            $this->installerViewModel->getInstallerParams(),
            LangSelector::getParams()
        );

        return $this->render("installer/setup_wizard.html", $params);
    }

    #[Post('/')]
    public function install()
    {
        $installerDTO = InstallerDTO::fromRequest($this->request);
        $input = (array) $installerDTO;

        $validator = Validator::make($input)
            ->required('username', 'El nombre de usuario no puede estar vacío.')
            ->required('passwd', 'La contraseña no puede estar vacía.')
            ->match('passwd', 'passwd2', 'Las contraseñas no coinciden.')
            ->email('email', 'El correo electrónico no es válido.');

        $params = [];

        if ($validator->fails()) {
            $params['errors'] = $validator->errors();
        } else {
            InstallerMapper::map($installerDTO, $this->configSettings);
        }

        $params = array_merge(
            $params,
            $this->installerViewModel->getInstallerParams(),
            LangSelector::getParams()
        );

        return $this->render("installer/setup_wizard.html", $params);
    }

    #[Post('progressBar')]
    public function progressBar()
    {
        $this->model->install();

        return $this->render("installer/progressBar.html", $this->installerViewModel->getInstallerParams());
    }
}
