<?php

/**
* Plantilla de la clase appModel para Balero CMS.
* Declare aqui todas las conexiones a la Base de datos.
**/

/**
 * Multi-Language Fixes
 */

class blog_Model extends configSettings {
	
	/**
	* Variables globales
	**/
	
	public $result;
	public $db;
	
	public $rows; // pasar variable a vista
	
	/**
	 * 
	 * Language code
	 */
	
	public $lang; // get default lang
	
	/**
	* Conectar a la base de datos en el constructor.
	**/
	
	public function __construct() { 
		
		$this->LoadSettings();
		
		try {
			$this->db = new mySQL($this->getDbhost(), $this->getDbuser(), $this->getDbpass(), $this->getDbname());
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
		
		
	}
	
	public function limit() {

	    $limit = 0;

		$admin_god = 1;
		
		$this->db->query("SELECT * FROM custom_settings WHERE id = '$admin_god'");
		$this->db->get();

        $rows = $this->db->getRows();
        foreach ($rows ?? [] as $row) {
            $limit = $row['pagination'] ?? null; // o algún valor por defecto, por ejemplo 10
        }
		
		/**
		 * Siempre (siempre) debemos de matar la variable $rows despues de una consulta,
		 * para limpiar los datos y esten limpios en la siguiente consulta. 
		 */

		return $limit;
		
	}
	
	public function theme() {

	    $theme = '';

		$admin_god = 1;
		
		$this->db->query("SELECT * FROM custom_settings WHERE id = '$admin_god'");
		$this->db->get();
		
		foreach ($this->db->getRows() as $row) {
			$theme = $row['theme'];
		}
		
		/**
		 * Siempre (siempre) debemos de matar la variable $rows despues de una consulta,
		 * para limpiar los datos y esten limpios en la siguiente consulta.
		 */

		return $theme;
		
	}
	
	public function loadModelvars() {
		
		$this->rows = $this->db->getRows();
		
	}
	
	
	public function get_fullpost($id) {
	
		if(empty($this->lang) || $this->lang == "main") {
		
			$this->db->query("SELECT * FROM blog WHERE id = '$id'");
			$this->db->get(); // cargar la variable de la clase $this->db->rows[] (MySQL::rows[]) con datos.
			$this->rows = $this->db->getRows();
			
		} else {
		
			$this->db->query("SELECT * FROM blog_multilang WHERE code = '".$this->lang."' AND id = '".$id."'");
			$this->db->get(); // cargar la variable de la clase $this->db->rows[] (MySQL::rows[]) con datos.
			$this->rows = $this->db->getRows();
		
		}
	
	}
	
	/**
	 * Get post multilingual and default
	 * @param unknown $min
	 * @param unknown $max
	 * @return boolean
	 */
	
	public function get_post($min, $max) {
	
		/**
		 * if empty lang then default load language
		 */
		
		if(empty($this->lang) || ($this->lang == "main")) {
			
			$this->db->query("SELECT * FROM blog ORDER BY id DESC LIMIT $min, $max");
			$this->db->get(); // cargar la variable de la clase $this->db->rows[] (MySQL::rows[]) con datos.
				
			$this->rows = $this->db->getRows();
				
			if(empty($this->rows) OR !is_array($this->db->getRows())) {
				return false;
			}
			
		} else {
			
			$this->db->query("SELECT * FROM blog_multilang WHERE code = '".$this->lang."' ORDER BY id DESC LIMIT $min, $max");
			$this->db->get(); // cargar la variable de la clase $this->db->rows[] (MySQL::rows[]) con datos.
				
			if(!empty($this->db->getRows())) {
				$this->rows = $this->db->getRows();
			}
			
		}

	}

	
	public function total_post() {
		
		$rows = array();
		
		if(empty($this->lang) || ($this->lang == "main")) {
			$this->db->query("SELECT * FROM blog");
			$this->db->get();
			$rows = $this->db->num_rows();
			return $rows;
		} else {
			$this->db->query("SELECT * FROM blog_multilang WHERE code = '$this->lang'");
			$this->db->get();
			$rows = $this->db->num_rows();
			return $rows;
		}
	}

	/**
	* Metodos
	**/
		
	public function full_post_model($id) {
		
		$result = array();
		
		try {
		$this->db->query("SELECT * FROM blog WHERE id = '$id'");
		$this->db->get();
		if(empty($this->db->getRows())) {
			throw new Exception(_GET_POST_ERROR);
		}
		$result = $this->db->getRows();
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}

		return $result;
		
	}
	
	public function getLangList() {
		$array = array();
		$this->db->query("SELECT * FROM languages");
		$this->db->get();
		
		//print_r($this->db->rows);
		
		try {
			
			if(empty($this->db->getRows())) {
				throw new Exception();
			}
			if(is_array($this->db->getRow())) {
                foreach ($this->db->getRow() as $row) {
                    $array[] = $row['code'];
                }
            }
			
		} catch (Exception $e) {
			
			/**
			 * No actions
			 */
			
		}

		return $array;
	}
	
	public function setVirtualCookie($name, $value, $expire) {
		$this->db->query("INSERT INTO cookie (name, value, expire) VALUES ('".$name."', '".$value."', '".$expire."')
							ON DUPLICATE KEY UPDATE value = '".$value."'");
	}
	
	/**
	 * 
	 * @return default language 
	 */
	
	public function getLang() {
		
		$defaultLang = "";
		
		$this->db->query("SELECT * FROM cookie WHERE name = '".$_SERVER['REMOTE_ADDR']."'");
		$this->db->get();
		
		try {

			/**
			 * is 2nd if sentence is on top will be an error
			 * Dont edit for now
			 */

            $rows = $this->db->getRows();

            foreach ($rows ?? [] as $row) {
                $defaultLang = $row['value'] ?? null; // Usa null o un valor por defecto
                // echo $defaultLang;
            }


            if(empty($this->db->getRows()) || empty($defaultLang)) {
				throw new Exception();
			}
			
		} catch (Exception $e) {
			
			/**
			 * NULL
			 */
			
			$defaultLang = "main";
			
		}

		return $defaultLang;
		
	}
	

	
	
}
