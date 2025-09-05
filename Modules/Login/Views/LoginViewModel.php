<?php

/**
 * Balero CMS
 * @author Anibal Gomez
 * @license GNU General Public License
 */

namespace Modules\Login\Views;

use Framework\Core\ViewModel;
use Framework\Core\ConfigSettings;
use Modules\Login\Models\LoginModel;

class LoginViewModel
{
    private LoginModel $model;
    private ConfigSettings $config;
    private ViewModel $viewModel;

    public function __construct(LoginModel $model, ConfigSettings $config)
    {
        $this->model = $model;
        $this->config = $config;
        $this->viewModel = new ViewModel(); // instanciación interna
    }

    /**
     * Pobla todos los parámetros de login y devuelve el array listo para render.
     */
    public function setLoginParams(array $extraParams = []): array
    {
        $this->viewModel->addAll([
            // Etiquetas
            'lbl_login'     => 'Login',
            'lbl_username'  => 'Username',
            'lbl_password'  => 'Password',

            // Botones
            'btn_login'     => 'Sign In',
        ]);

        // Mezclar parámetros adicionales (por ejemplo errores o mensajes de sesión)
        if (!empty($extraParams)) {
            $this->viewModel->addAll($extraParams);
        }

        return $this->viewModel->all();
    }
}
