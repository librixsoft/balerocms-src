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
use Framework\Core\Redirect;

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
        Redirect::init($configSettings);
        parent::__construct($request, $view, $configSettings);
    }

    #[Get('/')]
    public function home()
    {
        try {
            return $this->render("admin/login.html", AdminViewModel::getLoginParams());
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

        if ($loggedIn) {
            Redirect::to('/admin/settings');
        } else {
            // TODO: Render login view with error login message
            // Podrías usar: return $this->render("admin/login.html", [...])
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
        try {
            return $this->render("admin/dashboard.html", AdminViewModel::getSettingsParams($this->configSettings, $this->model));
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
            return $this->render("admin/dashboard.html", AdminViewModel::getPagesParams());
        } catch (Exception $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }
}
