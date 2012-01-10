<?php

	//
	// This class is an associative list, used with the comman line interface
	//

	require_once(DISK_ROOT . '/framework/library/assoclist.php');

	class Cli extends AssocList {
		public function __construct($argv) {
			$this->setData($argv);
			$this->processParameters();
		}

		// Process the data array to create the key/value pair structure
		private function processParameters() {
			$value = $this->getData('data');
			for($i = 0; $i < count($this->getData('data')); $i++) {
				if(isset($value[$i])) {
					if($i == 0)
						$this->addParameter('file', $value[$i]);
					else if($i == 1)
						$this->addParameter('controller', $value[$i]);
					else if($i == 2)
						$this->addParameter('method', $value[$i]);
					else
						$this->addParameter('p' . ($i - 3), $value[$i]);
				}
			}

			// if $_SERVER['argc'] is greater than 0, it's CLI
			// !!! BUG !!!
			// $_SERVER['argc'] is not way definite way of checking CLI mode
			// This check breaks when USE_CATCH_ALL is turned on and it triggers CLI mode below
			// A safer option till now is using PHP_SAPI, if php_sapi_name() returns "cli", we know that its cli
			// and enable the boolean
			if(php_sapi_name() == 'cli') {
				$this->addParameter('enabled', true);
			}
		}
	}

?>
