<?php

namespace Modules\Admin\Views;

use Framework\Core\ViewModel;
use Framework\Core\ConfigSettings;
use Modules\Admin\Models\AdminModel;

class AdminViewModel
{
    private AdminModel $model;
    private ConfigSettings $config;
    private ViewModel $viewModel;

    public function __construct(AdminModel $model, ConfigSettings $config)
    {
        $this->model = $model;
        $this->config = $config;
        $this->viewModel = new ViewModel();
    }

    public function getSettingsParams(): array
    {
        $this->viewModel->addAll([
            'core_version'   => _CORE_VERSION,
            'virtual_pages'  => $this->model->getVirtualPages(),
            'defaultTheme'   => $this->config->getTheme(),
            'pages_count'    => $this->model->getPagesCount(),
            'blocks_count'    => $this->model->getBlocksCount(),

            'themes' => [
                ['value' => 'Default', 'label' => 'Default'],
                ['value' => 'Dark',    'label' => 'Dark'],
                ['value' => 'Light',   'label' => 'Light'],
                ['value' => 'Modern',  'label' => 'Modern'],
            ],

            'activeMenu'    => 'settings',

            'lbl_theme'     => "Theme",
            'lbl_settings'  => __('admin.settings'),
            'lbl_title'     => 'Title',
            'lbl_keywords'  => 'Keywords',
            'lbl_description' => 'Description',

            'txt_title'       => $this->config->getTitle(),
            'txt_keywords'    => $this->config->getKeywords(),
            'txt_description' => $this->config->getDescription(),

            'btn_refresh' => 'Refresh',
        ]);

        return $this->viewModel->all();
    }

    public function getPagesParams(): array
    {
        $this->viewModel->addAll([
            'lbl_title'    => 'Title',
            'current_date' => date('Y-m-d H:i:s'),
            'new_page'     => 'New page',
            'btn_add'      => 'Create',
            'lbl_visible'  => 'Visible',
            'activeMenu'   => 'all_pages',
            'pages_count'    => $this->model->getPagesCount(),
        ]);

        return $this->viewModel->all();
    }

    public function getAllPagesParams(): array
    {
        $this->viewModel->addAll([
            'activeMenu' => 'all_pages',
            'pages'      => $this->model->getVirtualPages(),
            'pages_count'    => $this->model->getPagesCount(),
            'blocks_count'    => $this->model->getBlocksCount()
        ]);

        return $this->viewModel->all();
    }

    public function getEditPageParams(int $id): array
    {
        $page = $this->model->getPageById($id);

        $this->viewModel->addAll([
            'activeMenu'   => 'all_pages',
            'pages_count'    => $this->model->getPagesCount(),
            'page'       => $page,
        ]);

        return $this->viewModel->all();
    }

    public function updatePage(array $data): bool
    {
        return $this->model->updatePage((int)($data['id'] ?? 0), $data);
    }

    public function getAllBlocksParams(): array
    {
        $this->viewModel->addAll([
            'blocks' => $this->model->getBlocks(),
            'lbl_blocks' => 'Blocks',
            'lbl_new_block' => 'New Block',
            'activeMenu' => 'all_blocks',
            'pages_count'    => $this->model->getPagesCount(),
            'blocks_count'    => $this->model->getBlocksCount()
        ]);
        return $this->viewModel->all();
    }

    public function getNewBlockParams(): array
    {
        $this->viewModel->addAll([
            'lbl_new_block' => 'New Block',
            'activeMenu' => 'blocks'
        ]);
        return $this->viewModel->all();
    }

    public function getEditBlockParams(int $id): array
    {
        $block = $this->model->getBlockById($id);
        $this->viewModel->addAll([
            'block' => $block,
            'lbl_edit_block' => 'Edit Block',
            'activeMenu' => 'blocks'
        ]);
        return $this->viewModel->all();
    }

}
