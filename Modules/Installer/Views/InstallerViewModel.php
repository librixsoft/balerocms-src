<?php

namespace Modules\Installer\Views;

use Framework\Core\ConfigSettings;

class InstallerViewModel
{
    public static function getDefaultParams(ConfigSettings $config): array
    {
        return [
            'title' => $config->getTitle(),
            'keywords' => $config->getKeywords(),
            'description' => $config->getDescription(),
            'basepath' => $config->getBasepath(),

            // etiquetas
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

            // valores
            'txt_dbhost' => $config->getDbhost(),
            'txt_dbuser' => $config->getDbuser(),
            'txt_dbpass' => $config->getDbpass(),
            'txt_dbname' => $config->getDbname(),
            'txt_basepath' => $config->getBasepath(),
            'txt_title' => $config->getTitle(),
            'txt_url' => $config->getUrl(),
            'txt_keywords' => $config->getKeywords(),
            'txt_description' => $config->getDescription(),
            'username' => $config->getUsername(),
            'txt_pass' => '',
            'txt_retype' => '',
            'txt_firstname' => $config->getFirstname(),
            'txt_lastname' => $config->getLastname(),
            'txt_email' => $config->getEmail(),

            'btn_save' => "Guardar",
        ];
    }

    public static function getSetupWizardParams(ConfigSettings $config, array $extra = []): array
    {
        $params = self::getDefaultParams($config);
        $params['mod_rewrite_enabled'] = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());
        $params['welcome'] = "Welcome to Balero CMS Setup Wizard.";
        $params['btn_install'] = "Instalar";
        $configFilePath = LOCAL_DIR . '/resources/config/balero.config.xml';
        $params['config_writeable'] = is_writable($configFilePath);

        return array_merge($params, $extra);
    }
}
