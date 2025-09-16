<?php

namespace Modules\Login\Controllers;

use Framework\Core\Controller;
use Modules\Login\Models\LoginModel;
use Modules\Login\Views\LoginViewModel;
use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Static\Redirect;

class LoginController extends Controller
{
    protected LoginModel $model;
    private LoginViewModel $viewModel;

    public function __construct(
        LoginModel $model,
        LoginViewModel $viewModel
    ) {
        $this->model = $model;
        $this->viewModel = $viewModel;
    }

    #[Get('/')]
    public function home()
    {
        return $this->render("admin/login.html", $this->viewModel->setLoginParams(), false);
    }

    #[Post('/')]
    public function login()
    {
        if ($this->loginManager->handleLogin()) {
            Redirect::to('/admin/settings');
        } else {
            return $this->render(
                "admin/login.html",
                $this->viewModel->setLoginParams([
                    'error_message' => 'Usuario o contraseña incorrectos.'
                ]), false
            );
        }
    }

    #[Get('/logout')]
    public function logout()
    {
        $this->loginManager->logout();
        Redirect::to('/login/');
    }
}
