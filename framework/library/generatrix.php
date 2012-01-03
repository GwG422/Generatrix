<?php

	define('DISK_ROOT', str_replace('framework\library\generatrix.php', '', str_replace('framework/library/generatrix.php', '', __FILE__)));

	require_once(DISK_ROOT . 'framework/library/utils.php');
	require_once(DISK_ROOT . 'framework/library/startup.php');

	class Generatrix {
		private $request;
		private $cli;

		private $post;
		private $session;
		private $cookie;

		private $database;
		private $database_api;

		private $controller;
		private $method;
		private $request_type;

		private $mail;
		private $file;
		private $cache;

		public function __construct($argv = '') {
			$this->debugValues();
			$this->bootstrap($argv);
			$this->cache = new Cache();
			set_error_handler('handle_errors');
			$this->handleRequest();
		}

		public function __destruct() {
		}

		public function setDatabase($database) {
			// Create a copy of the database class
			$this->database = $database;
			return $this;
		}

		public function getDatabase() {
			return $this->database;
		}

		public function setDatabaseAPI($database_api) {
			$this->database_api = $database_api;
			return $this;
		}

		public function getDatabaseAPI() {
			return $this->database_api;
		}

		private function debugValues() {
			// Check if system wide error messages are to be shown
			if(DEBUG_VALUES) {
				ini_set('error_reporting', E_ALL);
				error_reporting(E_ALL);
			}
		}

		private function checkCache() {
			$get_url = isset($_GET['url']) ? $_GET['url'] : '';
			$url = APPLICATION_ROOT . $get_url;
			$groups = array();
			foreach($_GET as $key => $value) {
				if($key != 'url') $groups[] = "$key=$value";
			}
			if(count($groups) > 0) {
				$url .= ('?' . implode('&', $groups));
			}

			if(count($_POST) > 0) return false;

			$cached_output = $this->cache->get($url);
			echo $cached_output;
			return ($cached_output == '') ? false : true;
		}

		public function getController() {
			return $this->controller;
		}

		public function getMethod() {
			return $this->method;
		}

		public function getMail() {
			return $this->mail;
		}

		public function getRequestArray() {
			$request = explode('/', $this->request->getData());
			for($i = 0; $i < 10; $i++) {
				if(!isset($request[$i]))
					$request[$i] = '';
			}
			return $request;
		}

		public function getCliArray() {
			$cli_array = $this->cli->getData();
			$output_array = array();
			for($i = 1; $i < count($cli_array); $i++) {
				$output_array[] = $cli_array[$i];
			}
			return $output_array;
		}

		private function bootstrap($argv) {
			// Bootstrap the framework and calcuate all values
			$this->requireFiles();
			$this->cli = new Cli($argv);
			$this->request = new Request();
			$this->post = new Post();
			$this->mail = new Mail();
		}

		private function handleRequest() {
			// We have got the url value from .htaccess, use it to find which page is to be displayed
			$this->getControllerAndMethod();
			$controller_class = $this->controller . 'Controller';
			$view_class = $this->controller . 'View';
			$controller_method = $this->method;

			// Check if the page is available in the cache
			$found_cached_page = $this->checkCache();
			if($found_cached_page == true)
				return;

			if(class_exists($controller_class)) {
				if(method_exists($controller_class, $controller_method)) {
					if(class_exists($view_class)) {
						if(method_exists($view_class, $controller_method)) {
							// Everything is perfect, create the controller and view classes
							$controller = new $controller_class;
							$view = new $view_class;

							// Set the generatrix value in both controller and view so that they can use the other components
							$controller->setGeneratrix($this);
							$controller->setView($view);
							$view->setGeneratrix($this);

							// Execute the controller
							$controller->$controller_method();

							$final_page = '';
							// If the page is running via CLI (Comman Line Interface) don't show the DTD
							if(!$this->cli->isEnabled() && $controller->isControllerHtml()) {
								$final_page .= addDTD();
								$final_page .= conditionalClasses();
							}
							// Create the header etc
							$view->startPage($controller->isControllerHtml());
							// Get the final page to be displayed

							if( in_array($this->request_type, array('json') ) ) {

								$view_variables = $view->getAllVariables();
								if($view_variables == '') {
									$view_variables = array();
								}

								showJSON($view_variables);

							} else if( in_array($this->request_type, array('pdf') ) ) {

								if(version_compare(PHP_VERSION, '5.2.0') >= 0) {
									$view->$controller_method();
									if($controller->isControllerHtml()) {
										$final_page .= $view->endPage();
									}
								} else {
									$view->$controller_method();
									$html_object = $view->endPage();
									if ( is_object($html_object) && $controller->isControllerHtml()  ) {
										$final_page .= $html_object->_toString();
									}
								}

								if(!$this->cli->isEnabled()) {

									try {
									    $wkhtmltopdf = new Wkhtmltopdf( array( "path" => path("/app/cache/") ) );
									    $wkhtmltopdf->setHtml( $final_page );
									    $wkhtmltopdf->output(Wkhtmltopdf::MODE_DOWNLOAD, createHash(time() * rand(), 8) . ".pdf");
									} catch(Exception $e) {
									    echo $e->getMessage();
									}

								}

							} else {

								if(version_compare(PHP_VERSION, '5.2.0') >= 0) {
									$view->$controller_method();
									if($controller->isControllerHtml()) {
										$final_page .= $view->endPage();
									}
								} else {
									$view->$controller_method();
									$html_object = $view->endPage();
									if ( is_object($html_object) && $controller->isControllerHtml()  ) {
										$final_page .= $html_object->_toString();
									}
								}

								if(!$this->cli->isEnabled()) {
									echo $final_page;
								}

							}
						} else {
							if( $this->request_type == 'json' ) {
								display_404_json('The method <strong>"'. $controller_method . '"</strong> in class <strong>"'. $view_class .'"</strong> does not exist');
							} else {
								display_404('The method <strong>"'. $controller_method . '"</strong> in class <strong>"'. $view_class .'"</strong> does not exist');
							}
						}
					} else {
						if( $this->request_type == 'json' ) {
							display_404_json('The class <strong>"'. $view_class . '"</strong> does not exist');
						} else {
							display_404('The class <strong>"'. $view_class . '"</strong> does not exist');
						}
					}
				} else {
					if( $this->request_type == 'json' ) {
						display_404_json('The method <strong>"'. $controller_method . '"</strong> in class <strong>"'. $controller_class .'"</strong> does not exist');
					} else {
						display_404('The method <strong>"'. $controller_method . '"</strong> in class <strong>"'. $controller_class .'"</strong> does not exist');
					}
				}
			} else {
				if( $this->request_type == 'json' ) {
					display_404_json('The class <strong>"' . $controller_class .'"</strong> does not exist');
				} else {
					display_404('The class <strong>"' . $controller_class .'"</strong> does not exist');
				}
			}
		}

		private function getControllerAndMethod() {
			// Parse the values obtained from the url (obtained from .htaccess) to get the controller and view
			if(USE_CATCH_ALL) {
				require_once(path('/app/settings/mapping.php'));

				$request = array();
				if($this->cli->isEnabled()) {
					$request = $this->getCliArray();
				} else {
					$request = $this->getRequestArray();
				}

				$last_element = '';
				for($i = 9; $i >= 0; $i--) {
					if( ($last_element == '') && isset($request[$i]) && ($request[$i] != '') ) {
						$last_element = $request[$i];
					}
				}

				$dots = explode('.', $last_element);

				$type = '';
				if( count($dots) > 1)
					$type = $dots[count($dots) - 1];

				$details = mapping($request);
				if( _g($details, 'controller') ) $this->controller = _g($details, 'controller');
				if( _g($details, 'method') ) $this->method = _g($details, 'method');

				$this->request_type = $type;

				if( $this->controller == '' ) {
					$this->controller = (isset($request[0]) && ($request[0] != '')) ? $request[0] : DEFAULT_CONTROLLER;
				}

				if( $this->method == '' ) {
					$this->method = (isset($request[1]) && ($request[1] != '')) ? $request[1] : 'base';
				}

				// Do not destroy the generatrix controller
				$c_id = ($this->cli->isEnabled()) ? 1 : 0;
				if(isset($request[$c_id]) && ($request[$c_id] == 'generatrix')) {
					$this->controller = $request[$c_id];
					$c_id++;
					if(isset($request[$c_id]) && ($request[$c_id] != '')) {
						$this->method = $request[$c_id];
					} else {
						$this->method = 'base';
					}
				}

			} else {
				// If no controller or method is defined, we need to use the DEFAULT_CONTROLLER (defined in app/settings/config.php)
				// If cli is enabled, we use the format site.com/index.php controller function
				// 		Hence we need to get the values from the arguments as $argv[0], $argv[1] etc
				if($this->cli->isEnabled()) {
					if($this->cli->getValue('controller') == "") {
						header('HTTP/1.1 301 Moved Permanently');
						location('/' . DEFAULT_CONTROLLER);
					}
					$this->controller = $this->cli->getValue('controller') == "" ? DEFAULT_CONTROLLER : $this->cli->getValue('controller');
					$this->method = $this->cli->getValue('method') == "" ? 'base' : $this->cli->getValue('method');

					$type = '';
					$ARGV = _g($_SERVER, 'argv');
					if(isset($ARGV[0])) {
						$last_element = $ARGV[ count($ARGV) - 1];
						$dots = explode('.', $last_element);

						if( count($dots) > 1 )
							$type = $dots[count($dots) - 1];
						$type = $type;
					}
					$this->request_type = $type;
				} else {
					// If this request is coming from the browser, we need to get the value from url (obtained from .htaccess)
					if($this->request->getValue('controller') == "") {
						header('HTTP/1.1 301 Moved Permanently');
						location('/' . DEFAULT_CONTROLLER);
					}
					$this->controller = $this->request->getValue('controller') == "" ? DEFAULT_CONTROLLER : $this->request->getValue('controller');
					$this->method = $this->request->getValue('method') == "" ? 'base' : $this->request->getValue('method');

					$URL = _g($_SERVER, 'REQUEST_URI');
					$slashes = explode('/', $URL);
					// Take the last slash and explode on .
					$dots = explode('.', $slashes[count($slashes) - 1]);

					$type = '';
					if( count($dots) > 1 )
						$type = $dots[count($dots) - 1];
					$this->request_type = $type;
				}


			}

			// check for cache.manifest
			/* if($details['controller'] == 'cache.manifest') {
				$details['controller'] = 'cacheManifest';
				$details['method'] = 'base';
			} */

			$this->controller = $this->removeRequestType($this->controller);
			$this->method = $this->removeRequestType($this->method);
			//return $details;
		}

		private function requireFiles() {
			// Include all files in the /app/external folder (but not the ones inside sub-folders)
			$requires_directories = array('app/external');
			$core_requires = array('framework/library/', 'app/components', 'app/model', 'app/controllers', 'app/views', 'app/uicomponents');

			$all_requires = array_merge($core_requires, $requires_directories);
			foreach($all_requires as $dir) {
				$dir_handle = opendir(DISK_ROOT . $dir);
				while(false != ($file = readdir($dir_handle))) {
					if(substr($file, strlen($file) - strlen(".php") ) === ".php") {
						require_once(DISK_ROOT . $dir . '/' . $file);
					}
				}
			}
		}

		public function getPost() {
			// Get all post values
			return $this->post;
		}

		public function getSession() {
			// Get the session values
			return $this->session;
		}

		public function getCookie() {
			// Get the cookie values
			if($this->cookie == NULL)
				$this->cookie = new Cookie();
			return $this->cookie;
		}

		public function getRequest() {
			return explode('/', $this->request->getData());
		}

		// Get memory footprint
		public function getMemoryFootprint($message) {
			display($message . '  Usage: ' . memory_get_usage(true) . ' Peak: ' . memory_get_peak_usage(true));
		}

		public function removeRequestType($string) {
			if( strpos($string, '.' . $this->request_type) !== false ) {
				$string = str_replace('.' . $this->request_type, '', $string);
			}
			return $string;
		}
	}

?>
