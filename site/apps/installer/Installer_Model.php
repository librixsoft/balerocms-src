<?php

class installer_Model extends Model
{
    private ConfigSettings $configSettings;

    public function __construct()
    {
        try {
            parent::dbConnect();
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
