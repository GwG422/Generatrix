<?php

	// This is where the procedural part begins. This isn't a static class becuase you need to use too many words to write to everytime.

	session_start();
	prepare_config();

	// Use this to catch all errors other than 'Fatal' and display them in a box above the screen
	function handle_errors($errlevel, $errstr, $errfile = '', $errline = '', $errcontext = '') {
		$message = htmlentities($errstr) . " [ On <strong>" . $errfile . "</strong> Line " . $errline . " ]";
		if(($errlevel == E_WARNING) && (DEBUG_VALUES)) {
			display_warning($message);
		} else {
			display_error($message);
		}
	}

	// Display an error message properly. CSS has to be defined inline because we're not sure if the page has started yet
	function display($message, $file = '', $line = '') { display_message($message, $file, $line, 'normal', true); }
	function display_warning($message, $file = '', $line = '') { display_message($message, $file, $line, 'warning'); } 
	function display_error($message, $file = '', $line = '') { display_message($message, $file, $line, 'error'); } 
	function display_system($message, $file = '', $line = '') { display_message('SYSTEM: ' . $message, $file, $line, 'system'); }
	function display_404 ($message, $file = '', $line = '') { 
		if(isset($_SERVER['argc']) && ($_SERVER['argc'] > 1)) {
			display("This page does not exist");
		} else {
			header("HTTP/1.1 404 Not Found");
			echo "<div style='width: 600px; margin: 150px auto; text-align: center; background-color: #F7F7F7; border: 10px solid #EEEEEE; padding: 40px 0px; font-family: Georgia; Arial, sans-serif;'>This page does not exist</div>";
			// To show error page on Chrome, text needs to be bigger than 512 bytes, so just repeating
			echo "<div style='display: none;'>";
			echo "<div style='width: 600px; margin: 150px auto; text-align: center; background-color: #F7F7F7; border: 10px solid #EEEEEE; padding: 40px 0px; font-family: Georgia; Arial, sans-serif;'>Chrome does not show your error page if the content size is lesser than 512 bytes, hence we have had to take this extreme step.</div>";
			echo "<div style='width: 600px; margin: 150px auto; text-align: center; background-color: #F7F7F7; border: 10px solid #EEEEEE; padding: 40px 0px; font-family: Georgia; Arial, sans-serif;'>This page really does not exist</div>";
			echo "</div>";
		}
	}
	function display_404_json ($message, $file = '', $line = '') { 
		if(isset($_SERVER['argc']) && ($_SERVER['argc'] > 1)) {
			showJSON("This page does not exist", 'File not found');
		} else {
			header("HTTP/1.1 404 Not Found");
			showJSON("This page does not exist", 'File not found');
		}
	}

	function add_file_and_line($file, $line) {
		$return = (($file != '') || ($line != '')) ? '<br />In file <strong>'. str_replace(DISK_ROOT, '', $file) . '</strong> on line <strong>' . $line . '</strong>' : '';
		return $return;
	}

	// Show JSON
	function showJSON($data, $error = '') {
		$url = _g($_SERVER, 'REQUEST_URI');

		$has_error = false;
		if($error != '') {
			$has_error = true;
		}

		$variables = array(
			'url' => $url,
			'error' => $has_error,
			'error_description' => $error,
			'data' => $data,
			'timestamp' => time() 
			);

		header('Content-Type: application/json');
		echo json_encode( $variables );
		die();
	}

	// This displays an error in php, shows up in red
	function display_message($message, $file, $line, $level, $dump = false) {
		$using_cli = (isset($_SERVER['argc']) && ($_SERVER['argc'] > 1)) ? true : false;

		$background_color = '#000000';
		if($level == 'error') $background_color = '#D02733';
		if($level == 'warning') $background_color = '#FF8110';
		
		$output = $using_cli ? "" : "<pre style='margin: 2px; padding: 8px 16px; border: 1px solid #444444; color: #FFFFFF; font-size: 12px; font-family: \"Trebuchet MS\", Arial, sans-serif; text-align: left; background-color: " . $background_color . ";'>";
		echo $output;

		$output = '';
		if($dump && is_array($message)) {
			var_dump($message);
			$output .= add_file_and_line($file, $line, true);
		} else {
			$output .= ($message . add_file_and_line($file, $line));
		}

		$output .= $using_cli ? "\n" : "</pre>";
		echo $output;
	}

	// Adds a DTD to the page by default
	function addDTD() {
		return "<!DOCTYPE html>\n";
	}

	// Conditional stylesheets vs CSS Hacks answer neither
	// paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/
	function conditionalClasses() {
		$content = '';
		$content .= "<!--[if lt IE 7 ]> <html lang=\"en\" class=\"no-js ie6\"> <![endif]-->\n";
		$content .= "<!--[if IE 7 ]>    <html lang=\"en\" class=\"no-js ie7\"> <![endif]-->\n";
		$content .= "<!--[if IE 8 ]>    <html lang=\"en\" class=\"no-js ie8\"> <![endif]-->\n";
		$content .= "<!--[if IE 9 ]>    <html lang=\"en\" class=\"no-js ie9\"> <![endif]-->\n";
		return $content;
	}

	// Read the json in the config and create defines (eg. 'time-zone' in config creates define('TIME_ZONE', 'value')
	function prepare_config() {

		$complete_config = array();

		// Find out where the file is. path() will not work yet because config is not loaded
		// Load the default values from config.json.defaults
		$config_defaults_file = DISK_ROOT . 'app/settings/config.json.defaults';
		$config_defaults = (array) json_decode(file_get_contents($config_defaults_file));

		// Foreach value store them in an array
		foreach($config_defaults as $key => $value) {
			$define_element_key = str_replace("-", "_", strtoupper($key));
			$define_element_value = $value;

			if($value == "true")
				$define_element_value = 1;
			if($value == "false")
				$define_element_value = 0;

			$complete_config[$define_element_key] = $define_element_value;
		}

		// Read the user config file
		$config_file = DISK_ROOT . 'app/settings/config.json';

		if(file_exists($config_file)) {
			$config = (array) json_decode(file_get_contents($config_file));

			foreach($config as $key => $value) {
				$define_element_key = str_replace("-", "_", strtoupper($key));
				$define_element_value = $value;

				if($value == "true")
					$define_element_value = 1;
				if($value == "false")
					$define_element_value = 0;

				$complete_config[$define_element_key] = $define_element_value;
			}
		} else {
			display_system("You have not created a <strong>config file</strong> yet. You can fix that by going to the generatrix folder and typing <strong>cp " . path('/app/settings/config.json.defaults') . " " . path('/app/settings/config.json') . "</strong>");
		}

		// Create Macros (define(SOMETHING, VALUE);)
		foreach($complete_config as $key => $value) {
			define($key, $value);
		}

		checkDefaults();

		// Set the default time zone by using the config value 'time-zone'
		date_default_timezone_set(TIME_ZONE);
	}

	function checkDefaults() {
		checkMinimumPHPVersion();
	}

	function checkMinimumPHPVersion() {
		$status = false;
		if(function_exists('version_compare') && version_compare(phpversion(), MIN_PHP_VERSION, '>=')) {
		} else {
			// Show an error
			display_system("The <strong>version of PHP</strong> (" . phpversion() . ") is not greater than the minimum version supported [" . MIN_PHP_VERSION . "]");
		}
	}

?>
