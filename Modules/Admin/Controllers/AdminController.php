<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Admin\Controllers;

use Framework\Core\Controller;
use Framework\IO\Uploader;
use Modules\Admin\Models\AdminModel;
use Modules\Admin\Views\AdminViewModel;
use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Static\Redirect;

class AdminController extends Controller
{
    protected AdminModel $model;
    private Uploader $uploader;
    private AdminViewModel $viewModel;

    public function __construct(
        AdminModel $model,
        Uploader $uploader,
        AdminViewModel $viewModel
    ) {
        $this->model = $model;
        $this->uploader = $uploader;
        $this->viewModel = $viewModel;
    }

    #[Get('/')]
    public function home()
    {
        return $this->render("admin/login.html", $this->viewModel->getLoginParams());
    }

    #[Post('/login')]
    public function login()
    {
        $loggedIn = true; // TODO: implementar login real

        if ($loggedIn) {
            Redirect::to('/admin/settings');
        } else {
            // TODO: manejar error o redireccionar con mensaje
        }
    }

    #[Get('/dashboard')]
    public function dashboard()
    {
        Redirect::to('/admin/settings');
    }

    #[Get('/settings')]
    public function getSettings()
    {
        return $this->render("admin/dashboard.html", $this->viewModel->getSettingsParams());
    }

    #[Post('/settings')]
    public function postSettings()
    {
        $data = [
            'title' => $this->request->post("title"),
            'description' => $this->request->post("description"),
            'keywords' => $this->request->post("keywords"),
            'theme' => $this->request->post("theme"),
        ];

        $this->viewModel->updateSettings($data);

        Redirect::to('/admin/settings');
        return "";
    }

    #[Get('/pages')]
    public function getPages()
    {
        return $this->render("admin/pages.html", $this->viewModel->getPagesParams());
    }

    #[Post('/uploader')]
    public function postUploader()
    {
        if (!isset($_FILES['file'])) {
            throw new \Exception("input file not exist");
        }

        echo $this->uploader->image($_FILES['file'], LOCAL_DIR);
        return "";
    }
}
