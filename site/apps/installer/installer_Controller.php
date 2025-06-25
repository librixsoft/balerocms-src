<?php

/**
 *
 * installer_Controller.php
 * (c) Mar 2, 2013 lastprophet 
 * @author Anibal Gomez (lastprophet)
 * Balero CMS Open Source
 * Proyecto %100 mexicano bajo la licencia GNU.
 * PHP P.O.O. (M.V.C.)
 * Contacto: anibalgomez@icloud.com
 *
**/

class installer_Controller extends  ConfigSettings  {
	
	public $objModel;
	public $objView;

	private $cfgFile;

	private Security $security;
	private RequestHelper $request;
	
	public function __construct() {

        parent::__construct();

		try {

            $this->security = new Security();
            $this->request = new RequestHelper($this->security);

			$this->objView = new installer_View();
			$this->objModel = new installer_Model();
						
			$this->objView->installButton();
		} catch (Exception $e) {
			$this->objView = new installer_View();
			
			if(!is_writable($this->getCfgFile())) {
				$MsgBox = new MsgBox(_ERROR, _CHMOD_ERROR);
				$this->objView->content .= $MsgBox->Show();
			}
			
			if(strpos($e->getMessage(), _UNKNOW_DATABASE)) {
				$this->objView->unknow_database_error();
				$this->objModel->createDB();
				$this->objView->check_db = "";
			} else {
				$this->objView->unknow_database_connect();
				$this->objView->check_db = "";
			}
			
		}

		$this->initBasePath();
		
		$handler = new ControllerHandler($this);

	
	}
	
	public function initBasePath() {

		if(empty($this->getBasepath())) {
			$this->setBasepath($this->getFullBasepath());
		}
		
	}

    public function formDBInfo()
    {
        if (isset($_POST['submit'])) {
            try {

                $this->setDbhost($this->request->post('dbhost'));
                $this->setDbuser($this->request->post('dbuser'));
                $this->setDbpass($this->request->post('dbpass'));
                $this->setDbname($this->request->post('dbname'));

                $this->objView->check_db = $this->objView->check_icon;

            } catch (Exception $e) {
                $this->objView->check_db = "";
                $this->objView->file_error($e->getMessage());
            }
        }

        header("Location: index.php");
    }


    public function formSiteInfo()
    {
        try {
            if (isset($_POST['submit'])) {
                $this->setTitle($this->request->post('title'));
                $this->setUrl($this->request->post('url'));
                $this->setDescription($this->request->post('description'));
                $this->setKeywords($this->request->post('keywords'));
                $basepath = $this->request->post("basepath");
                if ($basepath !== null) {
                    $this->setBasepath($basepath);
                }
            }
        } catch (Exception $e) {
            // Manejo opcional de errores
        }

        header("Location: index.php");
    }

    public function formadminInfo()
    {
        if (isset($_POST['submit'])) {
            try {
                if (empty($this->request->post("username"))) {
                    throw new Exception(_EMPTY_USERNAME);
                }
                if (empty($this->request->post("passwd"))) {
                    throw new Exception(_EMPTY_PASSWORD);
                }
                if ($this->request->post("passwd") != $this->request->post("passwd2")) {
                    throw new Exception(_PASSWORDS_DONT_MATCH);
                }
                if (!filter_var($this->request->post("email"), FILTER_VALIDATE_EMAIL)) {
                    throw new Exception(_INDALID_EMAIL);
                }

                $this->setLastname($this->request->post('lastname'));
                $this->setNewsletter($this->request->post('newsletter'));
                $this->setUser($this->request->post('username'));
                $this->setEmail($this->request->post('email'));

                $obj = new Blowfish();
                $pwd = $obj->genpwd($this->request->post('passwd'));
                $this->setPass($pwd);

                header("Location: index.php");
            } catch (Exception $e) {
                $this->objView->check_admin = "";
                $this->objView->form_field_error($e->getMessage());
                $this->main();
            }
        } else {
            header("Location: index.php");
        }
    }
		
	public function main() {

		$this->objView->is_mod_rewrite_enabled();
		$this->objView->wizard();
		$this->objView->Render();
		
	}
	
	public function progressBar() {

		if(isset($_POST['submit']) && (!preg_match("/_blank/", $this->objView->getPass()))) {
			
			try {
				
				$this->objView->progressBar();
				$this->objModel->install();
				
			} catch (Exception $e) {
				
			}
			
		} else {
			
			header("Location: index.php?app=installer");
		}
		
	}
	
	public function validate($field) {
		if(empty($field)) {
			throw new Exception(_EMPTY_FIELD . " " . $field);
			return false;
		}
		return true;
	}
	
}
