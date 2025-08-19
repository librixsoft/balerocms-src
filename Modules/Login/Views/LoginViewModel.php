<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Login\Views;

use Framework\Core\ConfigSettings;
use Modules\Login\Models\LoginModel;

class LoginViewModel
{
    private LoginModel $model;
    private ConfigSettings $config;

    public function __construct(LoginModel $model, ConfigSettings $config)
    {
        $this->model = $model;
        $this->config = $config;
    }


    public function getLoginParams(): array
    {
        return [
            'lbl_edit_page' => 'Edit Page',
            'lbl_title' => 'Title',
        ];
    }

}
