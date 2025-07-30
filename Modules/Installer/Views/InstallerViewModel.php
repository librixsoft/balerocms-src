<?php

namespace Modules\Installer\Views;

use Framework\Core\ConfigSettings;

class InstallerViewModel
{
    private ConfigSettings $config;

    public function __construct(ConfigSettings $config)
    {
        $this->config = $config;
    }

    public function getInstallerParams(): array
    {
        $params = [
            // Etiquetas
            'lbl_dbconfig' => 'Database Configuration',
            'lbl_dbhost' => 'Database Host',
            'lbl_dbusername' => 'Database User',
            'lbl_dbpass' => 'Database Password',
            'lbl_dbname' => 'Database Name',
            'lbl_dbname_note' => 'If database does not exist, it will be created',
            'lbl_siteinfo' => 'Site Information',
            'lbl_basepath' => 'Base Path',
            'lbl_basepath_note' => 'Note about base path',
            'lbl_title' => 'Site Title',
            'lbl_url' => 'Site URL',
            'lbl_keywords' => 'Keywords',
            'lbl_description' => 'Description',
            'lbl_adminconfig' => 'Administrator Configuration',
            'lbl_administrator' => 'Administrator',
            'lbl_pass' => 'Password',
            'lbl_retype' => 'Retype Password',
            'lbl_firstname' => 'First Name',
            'lbl_lastname' => 'Last Name',
            'lbl_email' => 'Email Address',

            // Valores configurables
            'txt_dbhost' => $this->config->getDbhost(),
            'txt_dbuser' => $this->config->getDbuser(),
            'txt_dbpass' => $this->config->getDbpass(),
            'txt_dbname' => $this->config->getDbname(),
            'txt_basepath' => $this->config->getBasepath(),
            'txt_url' => $this->config->getUrl(),
            'username' => $this->config->getUsername(),
            'txt_pass' => '',
            'txt_retype' => '',
            'txt_firstname' => $this->config->getFirstname(),
            'txt_lastname' => $this->config->getLastname(),
            'txt_email' => $this->config->getEmail(),

            // Botones
            'btn_save' => "Guardar",

            // Setup Wizard
            'mod_rewrite_enabled' => function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()),
            'welcome' => "Welcome to Balero CMS Setup Wizard.",
            'btn_install' => "Instalar",
            'config_writeable' => is_writable(LOCAL_DIR . '/resources/config/balero.config.xml'),
        ];

        return $params;
    }
}
