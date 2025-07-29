<?php

namespace Modules\Page\Controllers;

use Framework\Core\Controller;
use Framework\Core\View;
use Framework\Http\RequestHelper;
use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;
use Modules\Page\Models\PageModel;
use Modules\Page\Views\PageViewModel;
use Framework\Http\Get;
use Exception;

class PageController extends Controller
{
    protected PageModel $model;

    public function __construct(
        RequestHelper $request,
        View $view,
        PageModel $model,
        ConfigSettings $configSettings
    ) {
        $this->model = $model;
        parent::__construct($request, $view, $configSettings);
    }

    #[Get('/')]
    public function home()
    {
        return $this->render("layouts/main.html", PageViewModel::getHomeParams($this->model));
    }

    #[Get('/{staticUrl}')]
    public function page(string $staticUrl)
    {
        try {
            $page = $this->model->getVirtualPageBySlug($staticUrl);

            if (empty($page)) {
                throw new Exception("Página no encontrada");
            }

            return $this->render("layouts/detail.html", PageViewModel::getDetailParams($this->model, $page));

        } catch (Exception $e) {
            ErrorConsole::handleException($e);

            return $this->render("layouts/detail.html", PageViewModel::getNotFoundParams());
        }
    }
}
