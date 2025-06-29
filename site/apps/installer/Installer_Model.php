<?php

class Installer_Model extends Model
{
    private ConfigSettings $configSettings;

    public function __construct(ConfigSettings $configSettings = null)
    {
        try {
            parent::dbConnect();

            $this->configSettings = $configSettings ?? new ConfigSettings();
            $this->configSettings->LoadSettings(); // Cargar configuración
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error during Installer_Model construction: " . $e->getMessage(), 0, $e)
            );
            exit;
        }
    }

    public function install(): void
    {
        try {
            $sqlFile = APPS_DIR . "installer/sql/tables.sql";
            if (!file_exists($sqlFile)) {
                throw new Exception("SQL installation file not found: $sqlFile");
            }

            $query = file_get_contents($sqlFile);
            $query = str_replace("{dbname}", $this->configSettings->getDbname(), $query);
            $this->db->create($query);

            $this->configSettings->setInstalled("yes");
        } catch (Throwable $e) {
            $this->configSettings->setInstalled("no");

            ErrorConsole::handleException(
                new Exception("Installation failed: " . $e->getMessage(), 0, $e)
            );
            exit;
        }
    }
}
