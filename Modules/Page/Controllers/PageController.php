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
        try {

            $params = [
                'virtual_pages' => $this->model->getVirtualPages(),
                'title' => 'Páginas virtuales'
            ];

            return $this->view->render("layouts/main.html", $params);

        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Get('/{staticUrl}')]
    public function page(string $staticUrl)
    {
        try {
            $page = $this->model->getVirtualPageBySlug($staticUrl);

            if (empty($page)) {
                throw new Exception("Página no encontrada");
            }

            $params = [
                'virtual_pages' => $this->model->getVirtualPages(),
                'page' => $page
            ];

            return $this->view->render("layouts/detail.html", $params);

        } catch (Exception $e) {
            ErrorConsole::handleException($e);

            $params = [
                'error_message' => "La página solicitada no existe."
            ];

            return $this->view->render("layouts/detail.html", $params);
        }
    }


}
