<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Page\Views;

use Framework\Core\ConfigSettings;
use Framework\Core\ViewModel;
use Modules\Page\Models\PageModel;

class PageViewModel
{

    private PageModel $model;
    private ConfigSettings $config;
    private ViewModel $viewModel;

    public function __construct(PageModel $model, ConfigSettings $config)
    {
        $this->config = $config;
        $this->model = $model;
        $this->viewModel = new ViewModel();
    }

    /**
     * Pobla todos los parámetros de Page y devuelve el array listo para render.
     *
     * @param array $extraParams Parámetros adicionales (por ejemplo errores o datos específicos).
     */
    public function setPageParams(array $extraParams = []): array
    {
        $this->viewModel->addAll([
            // Colecciones
            'virtual_pages' => $this->model->getVirtualPages(),

            // Etiquetas comunes
            'lbl_virtual_pages' => 'Virtual Pages',
            'lbl_home' => 'Home',
            'lbl_no_pages' => 'No virtual pages available.',

            // Botones
            'btn_refresh' => 'Refresh',
        ]);

        // Mezclar parámetros adicionales (por ejemplo, página actual o errores)
        if (!empty($extraParams)) {
            $this->viewModel->addAll($extraParams);
        }

        return $this->viewModel->all();
    }

}
