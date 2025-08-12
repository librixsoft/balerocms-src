<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Admin\Views;

use Framework\Core\ConfigSettings;
use Modules\Admin\Models\AdminModel;

class AdminViewModel
{
    private AdminModel $model;
    private ConfigSettings $config;

    public function __construct(AdminModel $model, ConfigSettings $config)
    {
        $this->model = $model;
        $this->config = $config;
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

            'core_version' => _CORE_VERSION,

            'virtual_pages' => $this->model->getVirtualPages(),
            'defaultTheme' => $this->config->getTheme(),

            // TODO: Implements theme system and reads
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

    public function getNewPageParams(): array
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

    public function getPagesParams(): array
    {
        return [
            'lbl_title' => 'Title',
            'lbl_action' => 'Action',
            'lbl_edit' => 'Edit',
            'lbl_delete' => 'Delete',
            'pages' => $this->model->getVirtualPages(),
            'activeMenu' => 'all_pages',
        ];
    }

}
