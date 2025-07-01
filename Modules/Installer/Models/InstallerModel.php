<?php

namespace Modules\Installer\Models;

use Framework\Core\Model;
use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;
use Exception;
use Throwable;

class InstallerModel extends Model
{
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

    public function install(): void
    {
        try {
            $sqlFile = LOCAL_DIR . "/site/apps/installer/sql/tables.sql";

            if (!file_exists($sqlFile)) {
                throw new Exception("SQL installation file not found: $sqlFile");
            }

            $query = file_get_contents($sqlFile);
            if ($query === false) {
                throw new Exception("Failed to read SQL installation file: $sqlFile");
            }

            $query = str_replace("{dbname}", $this->configSettings->getDbname(), $query);
            $this->db->create($query);
            $this->setInstalled("yes");
        } catch (Throwable $e) {
            $this->setInstalled("no");
            ErrorConsole::handleException(
                new Exception("Installation failed: " . $e->getMessage(), 0, $e)
            );
        }
    }
}
