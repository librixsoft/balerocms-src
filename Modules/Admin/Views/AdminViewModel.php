<?php

namespace Modules\Admin\Views;

use Framework\Core\ViewModel;
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

    private function createViewModel(): ViewModel
    {
        $viewModel = new ViewModel();

        // Parámetros base disponibles en todas las vistas
        $viewModel->addAll([
            'username' => $this->config->getUsername(),
            'email'    => $this->config->getEmail(),
        ]);

        return $viewModel;
    }

    public function getSettingsParams(): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'core_version'   => _CORE_VERSION,
            'virtual_pages'  => $this->model->getVirtualPages(),
            'defaultTheme'   => $this->config->getTheme(),
            'pages_count'    => $this->model->getPagesCount(),
            'blocks_count'   => $this->model->getBlocksCount(),

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
            'lbl_footer' => 'Footer',

            'txt_title'       => $this->config->getTitle(),
            'txt_keywords'    => $this->config->getKeywords(),
            'txt_description' => $this->config->getDescription(),
            'txt_footer' => $this->config->getFooter(),

            'btn_refresh' => 'Refresh',
        ]);

        return $viewModel->all();
    }

    public function getPagesParams(): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'lbl_title'    => 'Title',
            'current_date' => date('Y-m-d H:i:s'),
            'new_page'     => 'New page',
            'btn_add'      => 'Create',
            'lbl_visible'  => 'Visible',
            'activeMenu'   => 'all_pages',
            'pages_count'  => $this->model->getPagesCount(),
        ]);

        return $viewModel->all();
    }

    public function getAllPagesParams(): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'activeMenu'   => 'all_pages',
            'pages'        => $this->model->getVirtualPages(),
            'pages_count'  => $this->model->getPagesCount(),
            'blocks_count' => $this->model->getBlocksCount(),
        ]);

        return $viewModel->all();
    }

    public function getEditPageParams(int $id): array
    {
        $viewModel = $this->createViewModel();

        $page = $this->model->getPageById($id);

        $viewModel->addAll([
            'activeMenu'  => 'all_pages',
            'pages_count' => $this->model->getPagesCount(),
            'page'        => $page,
        ]);

        return $viewModel->all();
    }

    public function updatePage(array $data): bool
    {
        return $this->model->updatePage((int)($data['id'] ?? 0), $data);
    }

    public function getAllBlocksParams(): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'blocks'        => $this->model->getBlocks(),
            'lbl_blocks'    => 'Blocks',
            'lbl_new_block' => 'New Block',
            'activeMenu'    => 'all_blocks',
            'pages_count'   => $this->model->getPagesCount(),
            'blocks_count'  => $this->model->getBlocksCount(),
        ]);

        return $viewModel->all();
    }

    public function getNewBlockParams(): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'lbl_new_block' => 'New Block',
            'activeMenu'    => 'blocks',
        ]);

        return $viewModel->all();
    }

    public function getEditBlockParams(int $id): array
    {
        $viewModel = $this->createViewModel();

        $block = $this->model->getBlockById($id);

        $viewModel->addAll([
            'block'          => $block,
            'lbl_edit_block' => 'Edit Block',
            'activeMenu'     => 'blocks',
        ]);

        return $viewModel->all();
    }
}
