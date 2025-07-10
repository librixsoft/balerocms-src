<?php

namespace Modules\VirtualPage\Models;

use Framework\Core\Model;
use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;
use Exception;
use Throwable;

class VirtualPageModel extends Model
{

    public array $rows = [];
    public string $lang = "main";

    public function __construct(ConfigSettings $configSettings)
    {
        try {
            parent::__construct($configSettings);
            $this->dbConnect(); // conecta a la base de datos al instanciar
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error during InstallerModel construction: " . $e->getMessage(), 0, $e)
            );
        }
    }

    public function getVirtualPages(): array
    {
        try {
            $sql = "SELECT * FROM virtual_page WHERE active = 1 AND visible = 1 ORDER BY id ASC";
            $this->db->query($sql);
            $this->db->get();
            return $this->db->getRows() ?? [];
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error al obtener páginas virtuales: " . $e->getMessage(), 0, $e)
            );
            return [];
        }
    }


}
