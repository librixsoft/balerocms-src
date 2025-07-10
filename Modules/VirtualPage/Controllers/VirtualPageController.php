<?php

namespace Modules\VirtualPage\Controllers;

use Framework\Core\Controller;
use Framework\Core\View;
use Framework\Http\RequestHelper;
use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;
use Modules\VirtualPage\Models\VirtualPageModel;
use Modules\VirtualPage\Views\VirtualPageViewModel;
use Framework\Http\Get;
use Exception;

class VirtualPageController extends Controller
{
    protected VirtualPageModel $model;

    public function __construct(
        RequestHelper $request,
        View $view,
        VirtualPageModel $model,
        ConfigSettings $configSettings
    ) {
        $this->model = $model;
        parent::__construct($request, $view, $configSettings);
    }

    #[Get('/')]
    public function home()
    {
        try {
            $virtualPages = $this->model->getVirtualPages();

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

    #[Get('/welcome')]
    public function show()
    {
        try {
            $page = $this->model->getVirtualPageBySlug('welcome');

            if (empty($page)) {
                throw new Exception("Página no encontrada");
            }

            $params = [
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
