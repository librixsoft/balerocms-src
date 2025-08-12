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
        $params = $this->getPagesParams(); // integrar getPagesParams
        $params['current_date'] = date('Y-m-d H:i:s');
        $params['activeMenu'] = 'pages';
        // Otros valores específicos para "new page" si quieres
        $params['new_page'] = 'New page';
        $params['btn_add'] = 'Create';
        $params['lbl_visible'] = 'Visible';
        $params['enabled'] = 'Enabled';
        $params['disabled'] = 'Disabled';
        $params['lbl_static_url'] = 'Static URL';
        $params['lbl_content'] = 'Content';
        return $params;
    }


    public function getPagesParams(): array
    {
        return [
            'lbl_edit_page' => 'Edit Page',
            'lbl_title' => 'Title',
            'lbl_static_url' => 'Static URL',
            'lbl_content' => 'Content',
            'lbl_action' => 'Action',
            'lbl_edit' => 'Edit',
            'lbl_delete' => 'Delete',
            'btn_save' => 'Save',
            'pages' => $this->model->getVirtualPages(),
            'activeMenu' => 'all_pages',
        ];
    }

    public function updatePage(array $data): bool
    {
        return $this->model->updatePage((int)$data['id'], $data);
    }

    public function getEditPageParams(int $id): array
    {
        $page = $this->model->getPageById($id);
        // Obtener también parámetros generales de páginas
        $pagesParams = $this->getPagesParams();

        // Combinar ambos arrays, teniendo en cuenta que 'page' es específico
        return array_merge($pagesParams, ['page' => $page]);
    }



}
