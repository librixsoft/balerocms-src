<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Admin\Models;

use Framework\Core\Model;
use Framework\Core\ErrorConsole;
use Framework\Static\Utils;
use Exception;
use Throwable;

class AdminModel extends Model
{
    public function getVirtualPages(): array
    {
        try {
            $sql = "SELECT * FROM page WHERE visible = 1 ORDER BY id ASC";
            $this->db->query($sql);
            $this->db->get();

            $rows = $this->db->getRows() ?? [];

            // Generar URL estática para cada página virtual
            foreach ($rows as &$row) {
                $slug = Utils::slugify($row['static_url']);
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

    public function createPage(array $data): bool
    {
        $sql = "INSERT INTO page (virtual_title, static_url, virtual_content, visible, created_at) VALUES (?, ?, ?, ?, ?)";

        $params = [
            $data['virtual_title'],
            $data['static_url'],
            $data['virtual_content'],
            $data['visible'],
            $data['date'],
        ];


        $this->db->query($sql, $params);

        return true;
    }

    public function getPagesCount(): int
    {
        $pages = $this->getVirtualPages();
        return count($pages);
    }

}
