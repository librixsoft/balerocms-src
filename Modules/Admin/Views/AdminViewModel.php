<?php

namespace Modules\Admin\Views;

use Framework\Core\ConfigSettings;

class AdminViewModel
{

    public static function getDefaultParams(ConfigSettings $config, $model): array
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

            'virtual_pages' => $model->getVirtualPages(),
            'defaultTheme' => $config->getTheme(),
            // TODO: Load templates from folder layouts or themes
            'themes' => [
                ['value' => 'Default', 'label' => 'Default'],
                ['value' => 'Dark',    'label' => 'Dark'],
                ['value' => 'Light',   'label' => 'Light'],
                ['value' => 'Modern',  'label' => 'Modern'],
            ],
            'activeMenu' => 'settings',

        ];
    }

    public static function getPagesParams(ConfigSettings $config, $model): array
    {
        return [
            // TODO: Add pages params
            'activeMenu' => 'pages',
        ];
    }

}
