<?php

namespace Modules\Admin\Controllers;

use Framework\Core\Controller;
use Framework\Core\ErrorConsole;
use Framework\IO\Uploader;
use Modules\Admin\Models\AdminModel;
use Modules\Admin\Views\AdminViewModel;
use Framework\Http\Get;
use Framework\Http\Post;
use Exception;
use Framework\Core\Redirect;

class AdminController extends Controller
{
    protected AdminModel $model;
    private Uploader $uploader;
    private AdminViewModel $viewModel;
    private Redirect $redirect;

    public function __construct(
        AdminModel $model, // TODO: Keep because do database connection after
        Uploader $uploader,
        AdminViewModel $viewModel,
        Redirect $redirect  // autowiring
    ) {
        $this->model = $model;
        $this->uploader = $uploader;
        $this->viewModel = $viewModel;
        $this->redirect = $redirect;
    }

    #[Get('/')]
    public function home()
    {
        try {
            return $this->render("admin/login.html", $this->viewModel->getLoginParams());
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Post('/login')]
    public function login()
    {
        $loggedIn = true; // TODO: implementar login real

        if ($loggedIn) {
            //Redirect::to('/admin/settings');
            $this->redirect->to('/admin/settings');
        } else {
            // TODO: manejar error
        }
    }

    #[Get('/dashboard')]
    public function dashboard()
    {
        //Redirect::to('/admin/settings');
        $this->redirect->to('/admin/settings');
    }

    #[Get('/settings')]
    public function getSettings()
    {
        try {
            return $this->render("admin/dashboard.html", $this->viewModel->getSettingsParams());
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Post('/settings')]
    public function postSettings()
    {
        try {
            $data = [
                'title' => $this->request->post("title"),
                'description' => $this->request->post("description"),
                'keywords' => $this->request->post("keywords"),
                'theme' => $this->request->post("theme"),
            ];

            $this->viewModel->updateSettings($data);

            Redirect::to('/admin/settings');
            return "";

        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Get('/pages')]
    public function getPages()
    {
        try {
            return $this->render("admin/pages.html", $this->viewModel->getPagesParams());
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Post('/uploader')]
    public function postUploader()
    {
        try {
            if (!isset($_FILES['file'])) {
                throw new Exception("input file not exist");
            }

            echo $this->uploader->image($_FILES['file'], LOCAL_DIR);
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }
}
