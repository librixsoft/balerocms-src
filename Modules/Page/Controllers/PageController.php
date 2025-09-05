<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Page\Controllers;

use Framework\Core\Controller;
use Modules\Page\Models\PageModel;
use Modules\Page\Views\PageViewModel;
use Framework\Http\Get;
use Exception;

class PageController extends Controller
{
    protected PageViewModel $viewModel;
    protected PageModel $model;

    public function __construct(
        PageModel $model,
        PageViewModel $viewModel
    ) {
        $this->model = $model;
        $this->viewModel = $viewModel;
    }

    #[Get('/')]
    public function home()
    {
        return $this->render("layouts/main.html", $this->viewModel->setPageParams());
    }

    #[Get('/{staticUrl}')]
    public function page(string $staticUrl)
    {
        $page = $this->model->getVirtualPageBySlug($staticUrl);

        if (empty($page)) {
            // Página no encontrada → pasar mensaje al ViewModel
            return $this->render("layouts/page_detail.html", $this->viewModel->setPageParams([
                'error_message' => "La página solicitada no existe.",
            ]));
        }

        return $this->render("layouts/page_detail.html", $this->viewModel->setPageParams([
            'page' => $page,
        ]));
    }

}
