<?php

namespace Modules\Admin\Views;

use Framework\Core\ViewModel;
use Framework\Core\ConfigSettings;
use Framework\Core\ThemesReader;

class AdminViewModel
{
    private ConfigSettings $config;
    private ThemesReader $themesReader;

    public function __construct(ConfigSettings $config, ThemesReader $themesReader)
    {
        $this->config = $config;
        $this->themesReader = $themesReader;
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

    public function getSettingsParams(array $extraParams = []): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'core_version'   => _CORE_VERSION,
            'defaultTheme'   => $this->config->getTheme(),
            'themes' => $this->themesReader->getThemes(),
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
            'txt_footer'      => $this->config->getFooter(),

            'btn_refresh' => 'Refresh',
        ]);

        if (!empty($extraParams)) {
            $viewModel->addAll($extraParams);
        }

        return $viewModel->all();
    }

    public function getPagesParams(array $extraParams = []): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'lbl_title'    => 'Title',
            'current_date' => date('Y-m-d H:i:s'),
            'new_page'     => 'New page',
            'btn_add'      => 'Create',
            'lbl_visible'  => 'Visible',
            'activeMenu'   => 'all_pages',
        ]);

        if (!empty($extraParams)) {
            $viewModel->addAll($extraParams);
        }

        return $viewModel->all();
    }

    public function getAllPagesParams(array $extraParams = []): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'activeMenu' => 'all_pages',
        ]);

        if (!empty($extraParams)) {
            $viewModel->addAll($extraParams);
        }

        return $viewModel->all();
    }

    public function getEditPageParams(array $extraParams = []): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'activeMenu' => 'all_pages',
        ]);

        if (!empty($extraParams)) {
            $viewModel->addAll($extraParams);
        }

        return $viewModel->all();
    }

    public function getAllBlocksParams(array $extraParams = []): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'lbl_blocks'    => 'Blocks',
            'lbl_new_block' => 'New Block',
            'activeMenu'    => 'all_blocks',
        ]);

        if (!empty($extraParams)) {
            $viewModel->addAll($extraParams);
        }

        return $viewModel->all();
    }

    public function getNewBlockParams(array $extraParams = []): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'lbl_new_block' => 'New Block',
            'activeMenu'    => 'blocks',
        ]);

        if (!empty($extraParams)) {
            $viewModel->addAll($extraParams);
        }

        return $viewModel->all();
    }

    public function getEditBlockParams(array $extraParams = []): array
    {
        $viewModel = $this->createViewModel();

        $viewModel->addAll([
            'lbl_edit_block' => 'Edit Block',
            'activeMenu'     => 'blocks',
        ]);

        if (!empty($extraParams)) {
            $viewModel->addAll($extraParams);
        }

        return $viewModel->all();
    }
}
