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

            $rows = $this->db->getRows() ?? [];

            // Generar URL estática para cada página virtual
            foreach ($rows as &$row) {

                $slug = $this->slugify($row['static_url']);
                $row['url'] = "{$slug}";
            }

            return $rows;
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error al obtener páginas virtuales: " . $e->getMessage(), 0, $e)
            );
            return [];
        }
    }

    public function getVirtualPageBySlug(string $slug): array
    {
        try {
            $sql = "SELECT * FROM virtual_page WHERE static_url = ? AND active = 1 AND visible = 1 LIMIT 1";
            $params = [$slug];
            $this->db->query($sql, $params);
            $this->db->get();

            return $this->db->getRow() ?? [];

        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error al obtener página virtual por slug: " . $e->getMessage(), 0, $e)
            );
            return [];
        }
    }



    /**
     * Genera un slug amigable para URLs basado en el título (opcional)
     */
    private function slugify(string $text): string
    {
        // Pasos básicos para crear un slug
        $text = preg_replace('~[^\pL\d]+~u', '-', $text); // Reemplaza espacios y no letras por guion
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text); // Transliterar caracteres
        $text = preg_replace('~[^-\w]+~', '', $text); // Eliminar caracteres no deseados
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return $text ?: 'page';
    }

}
