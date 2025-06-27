<?php

class Installer_Model extends Model
{
    private ConfigSettings $configSettings;

    public function __construct()
    {
        try {
            parent::dbConnect();
            $this->configSettings = new ConfigSettings();
            $this->configSettings->LoadSettings(); // If needed
        } catch (Throwable $e) {
            ErrorConsole::handleException(new Exception("Error during Installer_Model construction: " . $e->getMessage(), 0, $e));
        }
    }

    public function install()
    {
        try {
            $query = file_get_contents(APPS_DIR . "installer/sql/tables.sql");
            $query = str_replace("{dbname}", $this->configSettings->getDbname(), $query);
            $this->db->create($query);
            $this->configSettings->setInstalled("yes");
        } catch (Throwable $e) {
            $this->configSettings->setInstalled("no");
            ErrorConsole::handleException(new Exception("Installation failed: " . $e->getMessage(), 0, $e));
        }
    }

}
