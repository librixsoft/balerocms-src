<?php

namespace Modules\Admin\Views;

use Framework\Core\ConfigSettings;

class AdminViewModel
{

    public static function getDefaultParams(ConfigSettings $config): array
    {
        return [

            'core_version' => _CORE_VERSION,
            'lbl_theme' => "Theme",

            'lbl_settings' => __('admin.settings'),
            'lbl_title' => 'Title',
            'lbl_keywords' => 'Keywords',
            'lbl_description' => 'Description',

            'txt_title' => $config->getTitle(),
            'txt_keywords' => $config->getKeywords(),
            'txt_description' => $config->getDescription(),

            'btn_refresh' => 'Refresh',
        ];
    }

}
