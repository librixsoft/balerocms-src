<?php

class Model
{
    protected MySQL $db;
    protected ConfigSettings $configSettings;

    public function __construct(ConfigSettings $configSettings)
    {
        $this->configSettings = $configSettings;
    }

    public function dbConnect()
    {

        try {
            $this->db = new MySQL(
                $this->configSettings->getDbhost(),
                $this->configSettings->getDbuser(),
                $this->configSettings->getDbpass());

            if ($this->db->isStatus()) {
                $this->db->query("CREATE DATABASE IF NOT EXISTS " . $this->configSettings->getDbname() . ";");
                $this->db = new MySQL(
                    $this->configSettings->getDbhost(),
                    $this->configSettings->getDbuser(),
                    $this->configSettings->getDbpass(),
                    $this->configSettings->getDbname()
                );
            } else {
                throw new Exception("Failed to connect to the database.");
            }
        } catch (Throwable $e) {
            ErrorConsole::handleException(new Exception("Error in Model: " . $e->getMessage(), 0, $e));
        }
    }
}
