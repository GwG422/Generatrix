<?php

	//
	// This is the base controller class
	//

	class Controller {
		private $generatrix;
		private $view;
		private $is_html;

		public $helper;

		public function __construct() {
			$this->is_html = true;
			if(class_exists('Helper')) {
				$this->helper = new Helper();
				$this->helper->setController($this);
			}
		}

		public function getSubDomain() {
			$server_name = checkArray($_SERVER, 'SERVER_NAME') ? $_SERVER['SERVER_NAME'] : false;
			if($server_name !== false) {
				$parts = explode('.', $server_name);
				if(count($parts) >= 3) {
					return implode('.', array_splice($parts, 0, count($parts) - 2));
				}
			}
			return false;
		}

		private function getP($i) {
			$url = $this->getURL();

			$output = '';
			if( $this->getDb()->getConnection() !== false ) {
				$output = isset($url[$i]) ? mysql_real_escape_string($url[$i], $this->getDb()->getConnection()) : false;
			} else {
				$output = isset($url[$i]) ? $url[$i] : false;
			}

			$output = $this->getGeneratrix()->removeRequestType($output);

			return $output;
		}

		public function getP1() { return $this->getP(0); }
		public function getP2() { return $this->getP(1); }
		public function getP3() { return $this->getP(2); }
		public function getP4() { return $this->getP(3); }
		public function getP5() { return $this->getP(4); }
		public function getP6() { return $this->getP(5); }
		public function getP7() { return $this->getP(6); }
		public function getP8() { return $this->getP(7); }
		public function getP9() { return $this->getP(8); }

		public function getUploadedFile($tag, $where = '/app/cache/uploads/', $allowed = '') {
			$return = array('error' => false, 'path' => false, 'name' => false);

			$allowed = explode(',', $allowed);
			for($i = 0; $i < count($allowed); $i++) {
				$allowed[$i] = strtolower(trim($allowed[$i]));
			}

			$where = ($where == '') ? '/app/cache/uploads/' : $where;

			if(isset($_FILES[$tag]['name']) && ($_FILES[$tag]['name'] != '')) {
				$image = $_FILES[$tag];

				$orig_name = isset($image['name']) ? $image['name'] : false;
				$type = isset($image['type']) ? $image['type'] : false;
				$tmp_name = isset($image['tmp_name']) ? $image['tmp_name'] : false;
				$error_value = isset($image['error']) ? $image['error'] : false;

				if($error_value === false) {
					$return['error'] = 'The file could not be uploaded because ' . $error_value;
				} else {
					$dots = explode('.', $orig_name);
					if(count($dots) == 1) {
						$return['error'] = 'Your filename does not have an extension';
					} else {
						$extension = strtolower($dots[count($dots) - 1]);
						if(count($allowed) > 0) {
							if(!in_array($extension, $allowed)) {
								$return['error'] = 'You can only upload files with filetype ' . implode(', ', $allowed);
							}
						}
						if($return['error'] == '') {
							unset($dots[count($dots) - 1]);
							$orig_file_name = implode('.', $dots);
							$new_name = sanitize(trim($orig_file_name)) . '_' . md5(time() . $orig_name) . '.' . $extension;
							while(file_exists(path($where . $new_name))) {
								$new_name = (time() % 100) . $new_name;
							}

							if(!move_uploaded_file($tmp_name, path($where . $new_name))) {
								$return['error'] = 'The file could not be uploaded. Please try again.';
							} else {
								$return['path'] = $where . $new_name;
								$return['name'] = $orig_name;
							}
						}
					}
				}
			}

			return $return;
		}

		// Set the view for this controller
		public function setView($view_class) {
			$this->view = $view_class;
		}

		public function getView() {
			return $this->view;
		}

		public function getGeneratrix() {
			return $this->generatrix;
		}

		// If you want to send out an email, use the mail controller defined in generatrix
		public function getMailer() {
			return $this->generatrix->getMail();
		}

		public function setGeneratrix($generatrix) {
			$this->generatrix = $generatrix;
			$this->is_html = true;
		}

		// Set a variable which will be used inside the view
		public function set($tag_name, $tag_value) {
			$this->getView()->set($tag_name, $tag_value);
		}

		// Get access to the database class created inside Generatrix
		// TODO : Enable access for multiple databases
		public function getDb() {
			$default_database = $this->getGeneratrix()->getDatabase();
			//if(isset($default_database) && ($default_database != "")) {
			if(isset($default_database)) {
				return $default_database;
			} else {
				$database = new Database();
				$this->getGeneratrix()->setDatabase($database);
				return $this->getGeneratrix()->getDatabase();
			}
		}
		
		public function db() {
			$default_class = $this->getGeneratrix()->getDatabaseApi();
			if(isset($default_class)) {
				return $default_class;
			} else {
				$database_api = new DbModel($this->getDb());
				$this->getGeneratrix()->setDatabaseApi($database_api);
				return $this->getGeneratrix()->getDatabaseAPI();
			}
		}

		public function saveCookie($tag, $value) {
			$this->getGeneratrix()->getCookie()->add($tag, $value);
		}

		public function deleteCookie($tag) {
			$this->getGeneratrix()->getCookie()->delete($tag);
		}

		public function getPostValue($tag_name) {
			return checkArray($_POST, $tag_name) ? mysql_real_escape_string($_POST[$tag_name], $this->getDb()->getConnection()) : false;
		}

		public function getCookieValue($tag_name) {
			return checkArray($_COOKIE, $tag_name) ? mysql_real_escape_string($_COOKIE[$tag_name], $this->getDb()->getConnection()) : false;
		}

		public function getSessionValue($tag_name) {
			return checkArray($_SESSION, $tag_name) ? mysql_real_escape_string($_SESSION[$tag_name], $this->getDb()->getConnection()) : false;
		}

	    public function getGetValue($tag_name) {
				return checkArray($_GET, $tag_name) ? mysql_real_escape_string($_GET[$tag_name], $this->getDb()->getConnection()) : false;
	    }

		public function getURL() {
			return $this->getGeneratrix()->getRequestArray();
		}

		public function isControllerHtml() {
			return $this->is_html;
		}

		public function isHtml($value = true) {
			$this->is_html = $value;
		}

    public function setSessionValue($tag_name, $value) {
      if ((check($tag_name)) && (!is_array($value)) && (check($value))) {
        $_SESSION[$tag_name] = trim($value);
      } else if ((check($tag_name)) && (is_array($value))) {
        $_SESSION[$tag_name] = $value;
      } else if ((check($tag_name)) && (($value === FALSE) || ($value === NULL))) {
        $_SESSION[$tag_name] = $value;
      }
    }

    public function removeSessionValue($tag_name) {
      if ((check($tag_name)) && (checkArray($_SESSION, $tag_name))) {
        unset($_SESSION[$tag_name]);
      }
    }

	}

?>
