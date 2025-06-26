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
                throw new Exception("No se pudo conectar a la base de datos.");
            }
        } catch (Exception $e) {
            throw new Exception("Error en Model: " . $e->getMessage());
        }
    }
}

