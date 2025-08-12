<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Admin\Models;

use Framework\Core\Model;
use Framework\Core\ErrorConsole;
use Exception;
use Throwable;

class AdminModel extends Model
{
    public function getVirtualPages(): array
    {
        try {
            $sql = "SELECT * FROM page WHERE active = 1 AND visible = 1 ORDER BY id ASC";
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

    public function getPageById(int $id): ?array
    {
        $id = (int)$id;
        $sql = "SELECT * FROM page WHERE id = {$id} LIMIT 1";

        $this->db->query($sql);
        $this->db->get();

        $rows = $this->db->getRows();

        return $rows[0] ?? null;
    }

    public function updatePage(int $id, array $data): bool
    {
        $sql = "UPDATE page SET 
        virtual_title = ?, 
        static_url = ?, 
        virtual_content = ? 
        WHERE id = ?";

        $params = [
            $data['virtual_title'],
            $data['static_url'],
            $data['virtual_content'],
            $id
        ];

        $this->db->query($sql, $params);

        // Puedes devolver true si no hubo excepción
        return true;
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
