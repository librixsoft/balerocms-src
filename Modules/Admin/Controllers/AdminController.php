<?php

namespace Modules\Admin\Controllers;

use Framework\Core\Controller;
use Framework\Core\View;
use Framework\Http\RequestHelper;
use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;
use Modules\Admin\Models\AdminModel;
use Modules\Admin\Views\AdminViewModel;
use Framework\Http\Get;
use Exception;

class AdminController extends Controller
{
    protected AdminModel $model;

    public function __construct(
        RequestHelper $request,
        View $view,
        AdminModel $model,
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
                //'virtual_pages' => $this->model->getVirtualPages(),
            ];

            $params += AdminViewModel::getDefaultParams($this->configSettings);

            return $this->view->render("admin/login.html", $params);

        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }


}
