<?php

class Model extends ConfigSettings
{
    protected MySQL $db;

    public function dbConnect()
    {
        parent::__construct();

        try {
            $this->db = new MySQL($this->getDbhost(), $this->getDbuser(), $this->getDbpass());

            if ($this->db->isStatus()) {
                $this->db->query("CREATE DATABASE IF NOT EXISTS " . $this->getDbname() . ";");
                $this->db = new MySQL(
                    $this->getDbhost(),
                    $this->getDbuser(),
                    $this->getDbpass(),
                    $this->getDbname()
                );
            } else {
                throw new Exception("Failed to connect to the database.");
            }
        } catch (Throwable $e) {
            ErrorConsole::handleException(new Exception("Error in Model: " . $e->getMessage(), 0, $e));
        }
    }
}
