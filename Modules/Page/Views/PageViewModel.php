<?php

namespace Modules\Page\Views;

use Framework\Core\ConfigSettings;
use Modules\Page\Models\PageModel;

class PageViewModel
{

    private PageModel $model;
    private ConfigSettings $config;

    public function __construct(PageModel $model)
    {
        $this->config = ConfigSettings::getInstance();
        $this->model = $model;
    }

    /**
     * Diccionario específico para la vista principal.
     */
    public function getHomeParams(): array
    {
        return [
            'virtual_pages' => $this->model->getVirtualPages(),

            // Etiquetas
            'lbl_virtual_pages' => 'Virtual Pages',
            'lbl_home' => 'Home',
            'lbl_no_pages' => 'No virtual pages available.',

            // Botones o textos varios
            'btn_refresh' => 'Refresh',
        ];
    }

    /**
     * Diccionario para página individual.
     */
    public function getDetailParams(array $page): array
    {
        return [
            'virtual_pages' => $this->model->getVirtualPages(),
            'page' => $page,

            // Etiquetas
            'lbl_virtual_pages' => 'Virtual Pages',
            'lbl_home' => 'Home',
        ];
    }

    /**
     * Diccionario para error de página no encontrada.
     */
    public function getNotFoundParams(): array
    {
        return [
            'error_message' => "La página solicitada no existe.",
        ];
    }
}
