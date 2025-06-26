<?php

class Installer_Model extends Model
{
    private ConfigSettings $configSettings;

    public function __construct()
    {
        try {
            parent::dbConnect();
            $this->configSettings = new ConfigSettings();
            $this->configSettings->LoadSettings(); // si es necesario
        } catch (Exception $e) {
        }
    }

    public function install()
    {
        try {
            $query = file_get_contents(APPS_DIR . "installer/sql/tables.sql");
            $query = str_replace("{dbname}", $this->getDbname(), $query);
            $this->db->create($query);
            $this->configSettings->setInstalled("yes");
        } catch(Exception $e) {
            $this->configSettings->setInstalled("no");
        }
    }
}
