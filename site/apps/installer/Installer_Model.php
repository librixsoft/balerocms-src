<?php

/**
 *
 * model.php
 * (c) Feb 26, 2013 lastprophet
 * @author Anibal Gomez (lastprophet)
 * Balero CMS Open Source
 * Proyecto %100 mexicano bajo la licencia GNU.
 * PHP P.O.O. (M.V.C.)
 * Contacto: anibalgomez@icloud.com
 *
 **/

class installer_Model extends ConfigSettings
{

    private MySQL $db;

    public function __construct()
    {

        parent::__construct();

        try {

            $this->db = new MySQL($this->getDbhost(), $this->getDbuser(), $this->getDbpass());

            if ($this->db->isStatus() == TRUE) {

                $this->db->query("CREATE DATABASE IF NOT EXISTS " . $this->getDbname() . ";");

                $this->db = new mySQL($this->getDbhost(), $this->getDbuser(), $this->getDbpass(), $this->getDbname());

            } else {

                throw new Exception();

            }

        } catch (Exception $e) {

            throw new Exception($e->getMessage());

        }

    }

    public function install()
    {
        $query = file_get_contents(APPS_DIR . "installer/sql/tables.sql");
        $query = str_replace("{dbname}", $this->getDbname(), $query);
        $this->db->create($query);

        $xml = new XMLHandler(LOCAL_DIR . "/site/etc/balero.config.xml");
        $xml->editChild("/config/system/installed", "yes");
    }

    public function createDB()
    {
        die("error");
    }


}
