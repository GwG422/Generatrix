<?php

	function createHash($string, $length = 6) {
		return substr(md5($string), 0, $length);
	}

	function multi_str_replace($replace, $haystack) {
		$content = $haystack;
		foreach($replace as $original => $changed) {
			$content = str_replace($original, $changed, $content);
		}
		return $content;
	}

	function sanitize($term, $separator = '_') {
		return preg_replace('/-+/', $separator, trim(preg_replace('/[^a-zA-Z0-9]/', $separator, trim(strtolower(str_replace($separator, ' ', $term))) ) ) );
	}

	// Creates a link based on the APPLICATION PATH defined in /app/settings/config.json
	// USAGE : href('/app/views/homeView.php');
	function chref($path) {
		if(true == USE_CDN) {
			return CDN_URI . href($path);
		} else {
			return href($path);
		}
	}

	function href($path) {
		$relative_root = '';
		$slashes = explode('/', APPLICATION_ROOT);
		for($i = 0; $i < count($slashes); $i++) {
			if(($i > 2) && (isset($slashes[$i])) && ($slashes[$i] != '')) {
				$relative_root .= ( '/' . $slashes[$i]);
			}
		}
		return $relative_root . $path;
	}

	// Calculate time in readable format
	function getReadableTime($seconds) {
		$mult = 1;
		if($seconds < 0) {
			$mult = -1;
			$seconds = $seconds * (-1);
		}
		if($seconds < 60) {
			if($seconds == 1)
				return ($seconds * $mult) . " second";
			return ($seconds * $mult) . " seconds";
		}
		$minutes = round($seconds / 60);
		if($minutes < 60) {
			if($minutes == 1)
				return ($minutes * $mult) . " minute";
			return ($minutes * $mult) . " minutes";
		}
		$hours = round($minutes / 60);
		if($hours < 24) {
			if($hours == 1)
				return ($hours * $mult) . " hour";
			return ($hours * $mult) . " hours";
		}
		$days = round($hours / 24);
		if($days < 30) {
			if($days == 1)
				return ($days * $mult) . " day";
			return ($days * $mult) . " days";
		}
		$months = round($days / 30);
		if($months < 12) {
			if($months == 1)
				return ($months * $mult) . " month";
			return ($months * $mult) . " months";
		}
		$years = round($months / 12);
		if($years == 1)
			return ($years * $mult) . " year";
		return ($years * $mult) . " years";
	}


	// Use timthumb to create smaller iamges
	function cimage($path, $width = '', $height = '') {
		if(true == USE_CDN) {
			return CDN_URI . image($path, $width, $height);
		} else {
			return image($path, $width, $height);
		}
	}

	function image($path, $width = '', $height = '') {
		if(check($path)) {
			return href('/public/scripts/timthumb.phpx?src=' . href($path) . '&w=' . $width . '&h=' . $height);
		} else {
			display_error('The image ' . href($path) . ' was not found');
		}
	}

	function fileExists($path) {
		if(file_exists(path($path)) && !is_dir(path($path))) {
			return true;
		}
		return false;
	}

	function dirExists($path) {
		if(file_exists(path($path)) && is_dir(path($path))) {
			return true;
		}
		return false;
	}

	// Checks the file permission for a file inside generatrix
	function perms($path) {
		if(file_exists(path($path))) {
			return substr(sprintf('%o', fileperms(path($path))), -3);
		} else {
			display_error('You are trying to edit the permissions, but the <strong>file ' . path($path) . ' does not exist</strong>');
			return false;
		}
	}

	// Returns the full path of the file (use the property defined in /app/settings/config.json
	// USAGE : path('/app/views/homeView.php');
	function path($path) {
		$relative_root = substr(DISK_ROOT, 0, strlen(DISK_ROOT) - 1);
		return $relative_root . $path;
	}

	// check if a value has been set and is not null
	function check($value) {
		if(isset($value) && ($value != ''))
			return true;
		return false;
	}

	function _c($array, $value1 = false, $value2 = false, $value3 = false, $value4 = false, $value5 = false, $value6 = false, $value7 = false, $value8 = false) {
		if($value8 !== false) {
			return isset($array[$value1][$value2][$value3][$value4][$value5][$value6][$value7][$value8]) ? true : false;
		} else if($value7 !== false) {
			return isset($array[$value1][$value2][$value3][$value4][$value5][$value6][$value7]) ? true : false;
		} else if($value6 !== false) {
			return isset($array[$value1][$value2][$value3][$value4][$value5][$value6]) ? true : false;
		} else if($value5 !== false) {
			return isset($array[$value1][$value2][$value3][$value4][$value5]) ? true : false;
		} else if($value4 !== false) {
			return isset($array[$value1][$value2][$value3][$value4]) ? true : false;
		} else if($value3 !== false) {
			return isset($array[$value1][$value2][$value3]) ? true : false;
		} else if($value2 !== false) {
			return isset($array[$value1][$value2]) ? true : false;
		} else if($value1 !== false) {
			return isset($array[$value1]) ? true : false;
		} else {
			return isset($array) ? true : false;
		}
	}

	function _g($array, $value1 = false, $value2 = false, $value3 = false, $value4 = false, $value5 = false, $value6 = false, $value7 = false, $value8 = false) {
		if($value8 !== false) {
			return isset($array[$value1][$value2][$value3][$value4][$value5][$value6][$value7][$value8]) ? $array[$value1][$value2][$value3][$value4][$value5][$value6][$value7][$value8] : false;
		} else if($value7 !== false) {
			return isset($array[$value1][$value2][$value3][$value4][$value5][$value6][$value7]) ? $array[$value1][$value2][$value3][$value4][$value5][$value6][$value7] : false;
		} else if($value6 !== false) {
			return isset($array[$value1][$value2][$value3][$value4][$value5][$value6]) ? $array[$value1][$value2][$value3][$value4][$value5][$value6] : false;
		} else if($value5 !== false) {
			return isset($array[$value1][$value2][$value3][$value4][$value5]) ? $array[$value1][$value2][$value3][$value4][$value5] : false;
		} else if($value4 !== false) {
			return isset($array[$value1][$value2][$value3][$value4]) ? $array[$value1][$value2][$value3][$value4] : false;
		} else if($value3 !== false) {
			return isset($array[$value1][$value2][$value3]) ? $array[$value1][$value2][$value3] : false;
		} else if($value2 !== false) {
			return isset($array[$value1][$value2]) ? $array[$value1][$value2] : false;
		} else if($value1 !== false) {
			return isset($array[$value1]) ? $array[$value1] : false;
		} else {
			return isset($array) ? $array : false;
		}
	}

	function _h($array, $value1 = false, $value2 = false, $value3 = false, $value4 = false, $value5 = false, $value6 = false, $value7 = false, $value8 = false) {
		return htmlentities( _g($array, $value1, $value2, $value3, $value4, $value5, $value6, $value7, $value8) );
	}

	// Check if a value inside an array isset and is not null
	function checkArray($array, $value) {
		if(is_array($array) && isset($array[$value]) && ($array[$value] != ''))
			return true;
		return false;
	}

	// Create json object
	function json($data) {
		header('Content-Type: application/json');
		return json_encode($data);
	}

	// Redirect to a particular path
	// USAGE : location('/user/forgotpass');
	function location($path) {
		$file_name = '';
		$line_number = '';
		if(!headers_sent($file_name, $line_number)) {
			header("Location: " . href($path));
			exit();
		} else {
			display_system('Cannot redirect the page to <strong>' . href($path) . '</strong> because headers have already been sent. The headers were started by <strong>' . $file_name. ' [ Line Number : '. $line_number . ']</strong>');
		}
	}

	function bt() {
		$bt = debug_backtrace();
		$output = array(
			'file' => $bt[0]['file'],
			'line' => $bt[0]['line']
		);
		return $output;
	}

	function ut($variable) {
		return urlencode(trim($variable));
	}

	function facebook_like() {
		$url = APPLICATION_ROOT . href('/' . $_GET['url']);
		return '<iframe src="http://www.facebook.com/plugins/like.php?href=' . urlencode($url) . '&amp;layout=button_count&amp;show_faces=true&amp;width=150&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:150px; height:21px;" allowTransparency="true"></iframe>';
	}

	function prepare($text) {
		return stripslashes(html_entity_decode($text));
	}

	function prepareExcerpt($text) {
		return prepare(str_replace('&lt;br&gt;', ' ', $text));
	}

	function requirePackage($package, $file) {
		if(fileExists('/app/packages/' . $package . '/' . $file)) {
			require_once(path('/app/packages/' . $package . '/' . $file));
		} else {
			display_error('The file ' . path('/app/packages/' . $package . '/' . $file) . ' does not exist');
		}
	}

	function chopToWord($string, $length, $ellipsis = true) {
		if(strlen($string) <= $length) {
			return $string;
		} else {
			return substr($string, 0, strrpos(substr($string, 0, $length), ' ')) . ($ellipsis ? '&hellip;' : '');
		}
	}

	function br2nl($string) {
		return str_replace(array('<br>', '<br />', '<br/>'), PHP_EOL, $string);
	}

	function timeDiffLog($running, $timer1, $timer2) {
		display_system('[' . ($timer2 - $timer1) * 1000 . ' s] ' . $running);
	}
	
	/**
		Validate an email address.
		Provide email address (raw input)
		Returns true if the email address has the email 
		address format and the domain exists.
	**/
	function validEmail($email) {
	   $isValid = true;
   		$atIndex = strrpos($email, "@");
   		if (is_bool($atIndex) && !$atIndex) {
      		$isValid = false;
   		} else {
      		$domain = substr($email, $atIndex+1);
      		$local = substr($email, 0, $atIndex);
      		$localLen = strlen($local);
      		$domainLen = strlen($domain);
      		if ($localLen < 1 || $localLen > 64) {
         		// local part length exceeded
         		$isValid = false;
      		} else if ($domainLen < 1 || $domainLen > 255) {
         		// domain part length exceeded
         		$isValid = false;
         	} else if ($local[0] == '.' || $local[$localLen-1] == '.') {
         		// local part starts or ends with '.'
         		$isValid = false;
         	} else if (preg_match('/\\.\\./', $local)) {
         		// local part has two consecutive dots
         		$isValid = false;
      		} else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
         		// character not valid in domain part
         		$isValid = false;
      		} else if (preg_match('/\\.\\./', $domain)) {
         		// domain part has two consecutive dots
         		$isValid = false;
      		} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
         		// character not valid in local part unless 
         		// local part is quoted
         		if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
            		$isValid = false; 
            	} 
            }
      		//if ($isValid && !(checkdnsrr($domain,"MX") || ↪checkdnsrr($domain,"A"))) {
         	//	// domain not found in DNS
         	//	$isValid = false;
         	//} 
         } 
         return $isValid; 
     }

?>
