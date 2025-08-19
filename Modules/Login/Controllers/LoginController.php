<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

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
        return $this->render("admin/login.html", $this->viewModel->getLoginParams());
    }

    #[Post('/')]
    public function login()
    {
        if ($this->loginManager->handleLogin()) {
            Redirect::to('/admin/settings');
        } else {
            return $this->render(
                "admin/login.html",
                $this->viewModel->getLoginParams($this->loginManager)
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
