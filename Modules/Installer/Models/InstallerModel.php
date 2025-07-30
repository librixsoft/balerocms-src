<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Modules\Installer\Models;

use Framework\Core\Model;
use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;
use Exception;
use Framework\Database\MySQL;
use Throwable;

class InstallerModel extends Model
{

    public function __construct(ConfigSettings $configSettings, MySQL $db)
    {
        try {
            parent::__construct($configSettings, $db);
            $this->dbConnect(); // conecta a la base de datos al instanciar
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error during InstallerModel construction: " . $e->getMessage(), 0, $e)
            );
        }
    }

    public function install(): void
    {
        try {
            $sqlFile = LOCAL_DIR . "/Modules/Installer/sql/tables.sql";

            if (!file_exists($sqlFile)) {
                throw new Exception("SQL installation file not found: $sqlFile");
            }

            $query = file_get_contents($sqlFile);
            if ($query === false) {
                throw new Exception("Failed to read SQL installation file: $sqlFile");
            }

            $query = str_replace("{dbname}", $this->configSettings->getDbname(), $query);
            $this->db->create($query);
            $this->configSettings->setInstalled("yes");
        } catch (Throwable $e) {
            $this->configSettings->setInstalled("no");
            ErrorConsole::handleException(
                new Exception("Installation failed: " . $e->getMessage(), 0, $e)
            );
        }
    }
}
