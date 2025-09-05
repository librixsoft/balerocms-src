<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Admin\Controllers;

use Framework\Core\Controller;
use Framework\Http\Auth;
use Framework\IO\Uploader;
use Modules\Admin\Models\AdminModel;
use Modules\Admin\Views\AdminViewModel;
use Framework\Http\Get;
use Framework\Http\Post;
use Framework\Static\Redirect;

#[Auth(required: true)]
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
        Redirect::to('/admin/settings');
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

        $this->model->updateSettings($data);

        Redirect::to('/admin/settings');
        return "";
    }

    #[Get('/new-page')]
    public function getPages()
    {
        return $this->render("admin/new_page.html", $this->viewModel->getPagesParams());
    }

    #[Get('/pages')]
    public function getAllPages()
    {
        return $this->render("admin/pages.html", $this->viewModel->getAllPagesParams());
    }

    #[Post('/pages/new')]
    public function postNewPage()
    {
        $data = [
            'virtual_title'   => $this->request->post('virtual_title'),
            'static_url'      => $this->request->post('static_url'),
            'virtual_content' => $this->request->post('virtual_content'),
            'visible'         => (int) $this->request->post('visible'),  // corregido, valor 1 o 0
            'date'            => $this->request->post('date'),
        ];

        $this->model->createPage($data);

        Redirect::to('/admin/pages');
    }


    #[Post('/uploader')]
    public function postUploader()
    {
        if (!isset($_FILES['file'])) {
            throw new \Exception("input file not exist");
        }

        return $this->uploader->image($_FILES['file']);
    }

    #[Get('/pages/edit/{id}')]
    public function editPage(int $id)
    {
        return $this->render("admin/edit_page.html", $this->viewModel->getEditPageParams($id));
    }

    #[Post('/pages/edit/{id}')]
    public function postEditPage(int $id)
    {
        $data = [
            'id' => $id,
            'virtual_title' => $this->request->post("virtual_title"),
            'static_url' => $this->request->post("static_url"),
            'virtual_content' => $this->request->post("virtual_content"),
        ];

        $this->viewModel->updatePage($data);

        Redirect::to('/admin/pages');
    }

}
