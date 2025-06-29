<?php

class Installer_Model extends Model
{
    public function __construct()
    {
        try {
            parent::dbConnect();
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error during Installer_Model construction: " . $e->getMessage(), 0, $e)
            );
            return;  // No exit, solo retornamos para evitar página en blanco
        }
    }

    public function install(): void
    {
        try {
            $sqlFile = APPS_DIR . "installer/sql/tables.sql";
            if (!file_exists($sqlFile)) {
                ErrorConsole::handleException(
                    new Exception("SQL installation file not found: $sqlFile")
                );
                return;  // Salimos para evitar continuar con error
            }

            $query = file_get_contents($sqlFile);
            $query = str_replace("{dbname}", $this->getDbname(), $query);
            $this->db->create($query);

            $this->setInstalled("yes");
        } catch (Throwable $e) {
            $this->setInstalled("no");
            ErrorConsole::handleException(
                new Exception("Installation failed: " . $e->getMessage(), 0, $e)
            );
            return;
        }
    }
}
