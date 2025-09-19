<?php

namespace Modules\Installer\Views;

use Framework\Core\ViewModel;
use Framework\Core\ConfigSettings;
use Framework\Static\Constant;

class InstallerViewModel
{
    private ConfigSettings $config;
    private ViewModel $viewModel;

    public function __construct(ConfigSettings $config)
    {
        $this->config = $config;
        $this->viewModel = new ViewModel(); // instanciación interna
    }

    /**
     * Pobla todos los parámetros de instalación y devuelve el array listo para render
     */
    public function setInstallerParams(array $extraParams = []): array
    {
        $this->viewModel->addAll([
            // Etiquetas
            'lbl_dbconfig' => 'Database Configuration',
            'lbl_dbhost' => __('installer.dbhost'),
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
            'txt_dbhost' => $this->config->dbhost,
            'txt_dbuser' => $this->config->dbuser,
            'txt_dbpass' => $this->config->dbpass,
            'txt_dbname' => $this->config->dbname,
            'txt_basepath' => $this->config->basepath,
            'txt_url' => $this->config->url,
            'username' => $this->config->username,
            'txt_pass' => '',
            'txt_retype' => '',
            'txt_firstname' => $this->config->firstname,
            'txt_lastname' => $this->config->lastname,
            'txt_email' => $this->config->email,

            // Botones
            'btn_save' => "Guardar",

            // Setup Wizard
            'mod_rewrite_enabled' => function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()),
            'welcome' => "Welcome to Balero CMS Setup Wizard.",
            'btn_install' => "Instalar",
            'config_writeable' => is_writable(Constant::CONFIG_PATH),

        ]);

        // Obtenemos flags que llegan del Controller o calculamos localmente
        // NOTA: el flag db_ok lo pasamos desde el Controller, si no está asumimos false
        $dbOk = $extraParams['db_ok'] ?? false;

        // Estado del sistema
        $fieldsValid     = $this->areFieldsValid($extraParams);
        $modRewrite      = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());
        $configWritable  = is_writable(Constant::CONFIG_PATH);

        // Agregar estado como flags
        $this->viewModel->addAll([
            'fields_valid'      => $fieldsValid,
            'db_ok'             => $dbOk,
            'mod_rewrite_ok'    => $modRewrite,
            'config_writable'   => $configWritable,
        ]);

        // Merge con parámetros extra (por ejemplo errores o datos cacheados)
        if (!empty($extraParams)) {
            $this->viewModel->addAll($extraParams);
        }

        return $this->viewModel->all();
    }

    private function areFieldsValid(array $extraParams = []): bool
    {
        if (!empty($extraParams['errors'])) {
            return false;
        }

        return
            $this->config->dbhost &&
            $this->config->dbuser &&
            $this->config->dbname &&
            $this->config->basepath &&
            $this->config->url &&
            $this->config->username &&
            $this->config->firstname &&
            $this->config->lastname &&
            $this->config->email &&
            filter_var($this->config->email, FILTER_VALIDATE_EMAIL);
    }

}
