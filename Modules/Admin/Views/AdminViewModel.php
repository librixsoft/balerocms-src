<?php

namespace Modules\Admin\Views;

use Framework\Core\ConfigSettings;
use Modules\Admin\Models\AdminModel;
use Framework\Config\Context;

class AdminViewModel
{
    private AdminModel $model;
    private ConfigSettings $config;

    public function __construct(AdminModel $model)
    {
        $this->model = $model;

        // Usar por alias
        $this->config = Context::get('config');

        // O directamente con FQCN
        //$this->config = Context::get(\Framework\Core\ConfigSettings::class);

    }

    public function updateSettings(array $data): void
    {
        $this->config->setTitle($data['title'] ?? '');
        $this->config->setDescription($data['description'] ?? '');
        $this->config->setKeywords($data['keywords'] ?? '');
        $this->config->setTheme($data['theme'] ?? '');
    }

    public function getLoginParams(): array
    {
        return [
            'core_version' => _CORE_VERSION,
            'lbl_theme' => 'Theme',
        ];
    }

    public function getSettingsParams(): array
    {
        return [
            'virtual_pages' => $this->model->getVirtualPages(),
            'defaultTheme' => $this->config->getTheme(),

            'themes' => [
                ['value' => 'Default', 'label' => 'Default'],
                ['value' => 'Dark',    'label' => 'Dark'],
                ['value' => 'Light',   'label' => 'Light'],
                ['value' => 'Modern',  'label' => 'Modern'],
            ],

            'activeMenu' => 'settings',

            'lbl_theme' => "Theme",
            'lbl_settings' => __('admin.settings'),
            'lbl_title' => 'Title',
            'lbl_keywords' => 'Keywords',
            'lbl_description' => 'Description',

            'txt_title' => $this->config->getTitle(),
            'txt_keywords' => $this->config->getKeywords(),
            'txt_description' => $this->config->getDescription(),

            'btn_refresh' => 'Refresh',
        ];
    }

    public function getPagesParams(): array
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
