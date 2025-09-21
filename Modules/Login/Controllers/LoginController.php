<?php

namespace Modules\Login\Controllers;

use Framework\Core\Controller;
use Modules\Login\Models\LoginModel;
use Modules\Login\Views\LoginViewModel;
use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Static\Redirect;
use Framework\Static\Flash;

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
        $params = [];
        if (Flash::has('login_error')) {
            $params['login_error'] = Flash::get('login_error');
        }
        return $this->render("admin/login.html", $params, false);
    }

    #[Post('/')]
    public function login()
    {
        if ($this->loginManager->handleLogin()) {
            Redirect::to('/admin/settings');
        } else {
            $error = $this->loginManager->getMessage();
            Flash::set('login_error', $error);
            Redirect::to('/login/');
        }
    }

    #[Get('/logout')]
    public function logout()
    {
        $this->loginManager->logout();
        Redirect::to('/login/');
    }

    //TODO: Need to be unified in one controller endpoint in flash messages beside installer flash messages and others
    #[Post('/delete_flash_message')]
    public function deleteFlashMessage()
    {
        $key = $this->request->post('key', '');
        if ($key) {
            Flash::delete($key);
            return $this->json(['status' => 'ok', 'message' => "Flash '$key' eliminado"]);
        }
        return $this->json(['status' => 'error', 'message' => 'No se proporcionó clave']);
    }

}
