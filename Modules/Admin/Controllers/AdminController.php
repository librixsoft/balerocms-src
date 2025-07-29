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
use Framework\Http\Post;
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

    #[Post('/login')]
    public function login()
    {
        // TODO: Admin login logic
        $loggedIn = true;
        if($loggedIn) {
            header("Location: " . $this->configSettings->getBasepath() . "/admin/settings");
        } else {
            // TODO: Render login view with error login message
        }
    }

    #[Get('/dashboard')]
    public function dashboard()
    {
        header("Location: " . $this->configSettings->getBasepath() . "/admin/settings");
    }

    // TODO: It needs validate secure login when accesing protected endpoints
    #[Get('/settings')]
    public function getSettings()
    {
        try {

            $params = [
                'virtual_pages' => $this->model->getVirtualPages(),
                'defaultTheme' => $this->configSettings->getTheme(),
                // TODO: Load templates from folder layouts or themes
                'themes' => [
                    ['value' => 'Default', 'label' => 'Default'],
                    ['value' => 'Dark',    'label' => 'Dark'],
                    ['value' => 'Light',   'label' => 'Light'],
                    ['value' => 'Modern',  'label' => 'Modern'],
                ],
            ];

            $params = array_merge(AdminViewModel::getDefaultParams($this->configSettings), $params);

            return $this->view->render("admin/dashboard.html", $params);

        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    #[Post('/settings')]
    public function postSettings()
    {
        try {
            $this->configSettings->setTitle($this->request->post("title"));
            $this->configSettings->setDescription($this->request->post("description"));
            $this->configSettings->setKeywords($this->request->post("keywords"));
            $this->configSettings->setTheme($this->request->post("theme"));

            header("Location: " . $this->configSettings->getBasepath() . "/admin/settings");
            return "";

        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

}
