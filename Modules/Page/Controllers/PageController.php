<?php

namespace Modules\Page\Controllers;

use Framework\Core\Controller;
use Framework\Core\View;
use Framework\Http\RequestHelper;
use Framework\Core\ErrorConsole;
use Modules\Page\Models\PageModel;
use Modules\Page\Views\PageViewModel;
use Framework\Http\Get;
use Exception;

class PageController extends Controller
{

    protected PageViewModel $viewModel;
    protected PageModel $model;

    public function __construct(
        RequestHelper $request,
        View $view,
        PageModel $model,
        PageViewModel $viewModel
    ) {
        $this->model = $model;
        $this->viewModel = $viewModel;
        parent::__construct($request, $view);
    }

    #[Get('/')]
    public function home()
    {
        return $this->render("layouts/main.html", $this->viewModel->getHomeParams());
    }

    #[Get('/{staticUrl}')]
    public function page(string $staticUrl)
    {
        try {
            $page = $this->model->getVirtualPageBySlug($staticUrl);

            if (empty($page)) {
                throw new Exception("Página no encontrada");
            }

            return $this->render("layouts/detail.html", $this->viewModel->getDetailParams($page));

        } catch (Exception $e) {
            ErrorConsole::handleException($e);

            return $this->render("layouts/detail.html", $this->viewModel->getNotFoundParams());
        }
    }
}
