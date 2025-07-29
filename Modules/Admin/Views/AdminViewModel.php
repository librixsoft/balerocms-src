<?php

namespace Modules\Admin\Views;

use Framework\Core\ConfigSettings;
use Modules\Admin\Models\AdminModel;

class AdminViewModel
{
    public static function getLoginParams(): array
    {
        return [
            'core_version' => _CORE_VERSION,
            'lbl_theme' => 'Theme',
        ];
    }

    public static function getSettingsParams(ConfigSettings $config, AdminModel $model): array
    {
        return [
            'virtual_pages' => $model->getVirtualPages(),
            'defaultTheme' => $config->getTheme(),

            'themes' => [
                ['value' => 'Default', 'label' => 'Default'],
                ['value' => 'Dark',    'label' => 'Dark'],
                ['value' => 'Light',   'label' => 'Light'],
                ['value' => 'Modern',  'label' => 'Modern'],
            ],

            'activeMenu' => 'settings',

            // Etiquetas y campos específicos
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

    public static function getPagesParams(): array
    {
        return [
            'lbl_title' => 'Title',
            'lbl_active' => 'Active',
            'enabled' => 'Enabled',
            'disabled' => 'disabled',
            'lbl_message' => 'Content',
            'btn_add' => 'Create',
            'new_page' => 'New page',
            'activeMenu' => 'pages',
        ];
    }
}
