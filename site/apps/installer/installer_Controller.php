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

class installer_Controller {
	
	public $objModel;
	public $objView;
		
	private $cfgFile;
	
	private $objConfig;

	
	public function __construct() {

		
		$this->cfgFile = LOCAL_DIR . "/site/etc/balero.config.xml";
		
		try {

			$this->objView = new installer_View();
			$this->objModel = new installer_Model();
						
			$this->objView->installButton();
		} catch (Exception $e) {
			$this->objView = new installer_View();
			
			if(!is_writable($this->cfgFile)) {
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
		
		$this->objConfig = new ConfigSettings();
		$this->initBasePath();
		
		$handler = new ControllerHandler($this);

	
	}
	
	public function initBasePath() {

		$basepath = $this->objConfig->getBasepath();
		
		
		if(empty($basepath)) {
		
			$cfg = new XMLHandler($this->cfgFile);
			$cfg->editChild("/config/site/basepath", $this->objConfig->FullBasepath());
		
		}
		
	}

	public function formDBInfo() {

		if(isset($_POST['submit'])) {

			try {

			$cfg = new XMLHandler($this->cfgFile);

			$cfg->editChild("/config/database/dbhost", $_POST['dbhost']);
			$cfg->editChild("/config/database/dbuser", $_POST['dbuser']);
			$cfg->editChild("/config/database/dbpass", $_POST['dbpass']);
			$cfg->editChild("/config/database/dbname", $_POST['dbname']);

			$cfg->editChild("/config/system/firsttime", "no");

			$this->objView->check_db = $this->objView->check_icon;

			} catch (Exception $e) {
				$this->objView->check_db = "";
				$this->objView->file_error($e->getMessage());
			}
		}

		header("Location: index.php");

	}
	

	public function formSiteInfo() {
				
		try {
								
			if(isset($_POST['submit'])) {
				
				$admcfg = new XMLHandler($this->cfgFile);
		
				$admcfg->editChild("/config/site/title", $_POST['title']);
				$admcfg->editChild("/config/site/url", $_POST['url']);
				$admcfg->editChild("/config/site/description", $_POST['description']);
				$admcfg->editChild("/config/site/keywords", $_POST['keywords']);
				$admcfg->editChild("/config/site/basepath", $_POST['basepath']);	
							
			}
						
		} catch (Exception $e) {
			
			
		}
	
		header("Location: index.php");
	
	}
	
	public function formadminInfo() {
	
		if(isset($_POST['submit'])) {
		try {
			
			$admcfg = new XMLHandler($this->cfgFile);
			
			if(empty($_POST['username'])) {
				throw new Exception(_EMPTY_USERNAME);
			}
			if(empty($_POST['passwd'])) {
				throw new Exception(_EMPTY_PASSWORD);
			}
			if($_POST['passwd'] != $_POST['passwd2']) {
				throw new Exception(_PASSWORDS_DONT_MATCH);
			}
			
			if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				throw new Exception(_INDALID_EMAIL);
			}
						
			$admcfg->editChild("/config/admin/firstname", $_POST['firstname']);
			$admcfg->editChild("/config/admin/lastname", $_POST['lastname']);
			$admcfg->editChild("/config/admin/newsletter", $_POST['newsletter']);
			$admcfg->editChild("/config/admin/username", $_POST['username']);
			$admcfg->editChild("/config/admin/email", $_POST['email']);
				
			$obj = new Blowfish(); // crear objeto
			$pwd = $obj->genpwd($_POST['passwd']); // generar passwd encriptado
			$admcfg->editChild("/config/admin/passwd", $pwd);
			
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
