<?php

class Installer_View extends View
{
    public string $page;
    public string $check_db;
    public string $check_site;
    public string $check_admin;
    public string $check_icon = "<img src=\"site/apps/installer/html/images/check-icon.png\">";

    private ConfigSettings $configSettings;
    private RequestHelper $request;

    public function __construct()
    {
        parent::__construct("/themes/tundra/main.html");

        $this->check_db = $this->check_icon;
        $this->check_site = $this->check_icon;
        $this->check_admin = $this->check_icon;

        $this->page = _PAGE;
        $this->request = new RequestHelper();
        $this->configSettings = new ConfigSettings();
        $this->configSettings->LoadSettings();
    }

    public function renderView(): void
    {
        $params = [
            'title' => $this->configSettings->getTitle(),
            'url' => $this->configSettings->getUrl(),
            'page' => $this->page,
            'keywords' => $this->configSettings->getKeywords(),
            'description' => $this->configSettings->getDescription(),
            'content' => $this->content,
            'virtual_pages' => '',
            'basepath' => $this->configSettings->getBasepath(),
            'langs' => ''
        ];

        $this->renderLayout($params);
    }

    public function is_mod_rewrite_enabled(): void
    {
        if (in_array('mod_rewrite', apache_get_modules())) {
            $msg = new MsgBox(_INSTALLER_MESSAGE_MW_TITLE_OK, _INSTALLER_MESSAGE_MW_MESSAGE_OK, "S");
        } else {
            $msg = new MsgBox(_INSTALLER_MESSAGE_MW_TITLE_ERROR, _INSTALLER_MESSAGE_MW_MESSAGE_ERROR, "E");
        }
        $this->content .= $msg->Show();
    }

    public function installButton(): void
    {
        try {
            if ($this->request->hasPost("submit") && empty($this->request->post("passwd"))) {
                throw new Exception("Empty passwd");
            }

            $array = ['title' => _INSTALL_TITLE, 'btn_install' => _INSTALL_BUTTON];
            $this->content .= $this->renderFragment(APPS_DIR . "/installer/html/finish_install.html", $array);

        } catch (Exception $e) {
            $this->content .= $this->tips_messages();
        }
    }

    public function progressBar(): void
    {
        $array = ['basepath' => $this->configSettings->getBasepath()];
        echo $this->renderFragment(APPS_DIR . "installer/html/UI.html", $array);
    }

    public function wizard(): void
    {
        $basepath = $this->configSettings->getBasepath() ?: $this->configSettings->FullBasepath();

        if (
            empty($basepath) ||
            empty($this->configSettings->getTitle()) ||
            empty($this->configSettings->getUrl()) ||
            empty($this->configSettings->getDescription()) ||
            empty($this->configSettings->getKeywords())
        ) {
            $this->check_site = "";
        }

        if (
            empty($this->configSettings->getUser()) ||
            empty($this->configSettings->getPass()) ||
            empty($this->configSettings->getFirstname()) ||
            empty($this->configSettings->getLastname()) ||
            empty($this->configSettings->getEmail())
        ) {
            $this->check_admin = "";
        }

        $array = [
            'check_db' => $this->check_db,
            'check_site' => $this->check_site,
            'check_admin' => $this->check_admin,
            'lbl_dbconfig' => _DB_CONFIG,
            'lbl_dbhost' => _DB_HOST,
            'lbl_dbusername' => _DB_USER,
            'lbl_dbpass' => _DB_PASS,
            'lbl_dbname' => _DB_NAME,
            'lbl_dbname_note' => _DB_IF_NOT_EXIST,
            'lbl_siteinfo' => _SITE_INFO,
            'lbl_basepath' => _BASEPATH,
            'lbl_basepath_note' => _NOTE_BASEPATH,
            'lbl_title' => _TITLE,
            'lbl_url' => _URL,
            'lbl_keywords' => _TAGS,
            'lbl_description' => _DESCRIPTION,
            'lbl_adminconfig' => _ADMIN_CONFIGURATION,
            'lbl_administrator' => _ADMIN,
            'lbl_pass' => _PASS,
            'lbl_retype' => _RETYPE_PASS,
            'lbl_firstname' => _NAME,
            'lbl_lastname' => _LAST_NAME,
            'lbl_email' => _EMAIL,
            'lbl_newsletter' => _NEWSLETTER,
            'txt_dbhost' => $this->configSettings->getDbhost(),
            'txt_dbuser' => $this->configSettings->getDbuser(),
            'txt_dbpass' => $this->configSettings->getDbpass(),
            'txt_dbname' => $this->configSettings->getDbname(),
            'txt_basepath' => $basepath,
            'txt_title' => $this->configSettings->getTitle(),
            'txt_url' => $this->configSettings->getUrl(),
            'txt_keywords' => $this->configSettings->getKeywords(),
            'txt_description' => $this->configSettings->getDescription(),
            'txt_administrator' => $this->configSettings->getUser(),
            'txt_pass' => '',
            'txt_retype' => '',
            'txt_firstname' => $this->configSettings->getFirstname(),
            'txt_lastname' => $this->configSettings->getLastname(),
            'txt_email' => $this->configSettings->getEmail(),
            'txt_newsletter' => $this->configSettings->getNewsletter(),
            'btn_save' => _INSTALLER_SAVE
        ];

        $this->content .= $this->renderFragment(APPS_DIR . "installer/html/wizard.html", $array);
    }

    public function tips_messages(): string
    {
        $msg = new MsgBox(_NOTE, _INSTALLER_TIP1, "I");
        return $msg->Show();
    }

    public function unknow_database_error(): void
    {
        $msg = new MsgBox(_DB_DONT_EXIST, _DATABASE_CREATED, "I");
        $this->content .= $msg->Show();
    }

    public function unknow_database_connect(): void
    {
        $msg = new MsgBox(_INSTALLER_WARNING, _INSTALLER_WARNING_MESSAGE, "I");
        $this->content .= $msg->Show();
    }

    public function form_field_error($e): void
    {
        $msg = new MsgBox(_ADMIN_CONFIGURATION, _CHECK_FIELDS . $e, "E");
        $this->content .= $msg->Show();
    }

    public function file_error($e): void
    {
        $msg = new MsgBox(_PERMISSIONS_ERROR, _PERMISSIONS_ERROR_MESSAGE . $e, "E");
        $this->content .= $msg->Show();
    }

    public function create_db_error($e): void
    {
        $msg = new MsgBox(_ERROR_CREATING_DATABASE, _ERROR_CREATING_DATABASE_MESSAGE . " " . $e, "E");
        $this->content .= $msg->Show();
    }

    public function database_created(): void
    {
        $msg = new MsgBox(_WARNING, _DATABASE_CREATED, "S");
        $this->content .= $msg->Show();
    }


}
