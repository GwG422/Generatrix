<?php

	//
	// The base class for all views
	//

	class View {
		private $generatrix;
		private $variables;

		private $head;
		private $body;

		private $control_name;
		private $control_options;

		private $added_generated_css;
		private $end_page_called;

		public $helper;

		public function __construct() {
			$this->added_generated_css = false;
			if(class_exists('viewHelper')) {
				$this->helper = new viewHelper();
			}
			$this->end_page_called = false;
		}

		// Get a variable from the controller
		public function get($var_name) {
			if($this->hasVariable($var_name)) {
				return $this->variables[$var_name];
			} else {
				$bt = bt();
				display_error('The variable <strong>"' . $var_name . '"</strong> is not available in <strong>' . $bt['file'] . ' [ Line ' . $bt['line'] . ']</strong>');
			}
		}

		public function view($details = false) {
			if ( $details === false) {
				return array_keys($this->variables);
			} else {
				return $this->variables;
			}
		}

		public function getSafe($var_name) {
			if($this->hasVariable($var_name)) {
				return $this->variables[$var_name];
			} else {
				return false;
			}
		}

		// Set a variable from the controller
		public function set($var_name, $var_value) {
			$this->variables[$var_name] = $var_value;
		}

		// Check if a variable is defined
		public function hasVariable($var_name) {
			if(isset($this->variables[$var_name]))
				return true;
			return false;
		}

		// Check if the variable has a value
		public function hasValue($var_name) {
			if(isset($this->variables[$var_name]) && ($this->variables[$var_name] != ''))
				return true;
			return false;
		}

		public function getGeneratrix() {
			return $this->generatrix;
		}

		public function setGeneratrix($generatrix) {
			$this->generatrix = $generatrix;
		}

		// Get the <head> object
		public function getHead() {
			return $this->head;
		}

		// Set the <head> object
		public function setHead($head) {
			$this->head = $head;
			return $this;
		}

		// Get the <body> object
		public function getBody() {
			return $this->body;
		}

		// Set the <body> object
		public function setBody($body) {
			$this->body = $body;
			return $this;
		}

		// Start the page, create <head> and <body> elements
		public function startPage($is_html) {
			$this->setHead(new Head());
			$this->setBody(new Body());

			if($is_html) {
				$this->prepareHead();
				$this->addLibraries();
			}
		}

		// End the page, close the <head> and <body> tags and add them to <html>
		public function endPage() {
			// Add google analytics
			$this->addGoogleAnalytics();

			// Prepare the page
			$html = new Html();
			$html->set('lang', 'en');
			$html->set('class', 'no-js');

			if(USE_MANIFEST == true) {
				//$html->set('manifest', href('/cache.manifest'));
			}

			$html->appendContent($this->getHead());
			$html->appendContent($this->getBody());
			$this->end_page_called = true;
			return $html;
		}

		// Get the post value
		public function getPostValue($tag_name) {
			return $this->getGeneratrix()->getPost()->getValue($tag_name);
		}

		// Get the cookie value
		public function getCookieValue($tag_name) {
			return $this->getGeneratrix()->getCookie()->getValue($tag_name);
		}

		// Get the cookie value
		public function getSessionValue($tag_name) {
			return $this->getGeneratrix()->getSession()->getValue($tag_name);
		}

		public function addGoogleAnalytics() {
			if( (GOOGLE_ANALYTICS_CODE != '') ) {
				// from here - http://mathiasbynens.be/notes/async-analytics-snippet
				$this->getHead()->appendContent("
<script>var _gaq=[['_setAccount','" . GOOGLE_ANALYTICS_CODE . "'],['_trackPageview']];(function(d,t){
var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
g.async=1;g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)
}(document,'script'))</script>
				");
			}
		}


		// Add a javscript file to <head>
		public function addJavascript($file) {
			$outside_link = (strpos($file, 'http://') === false) ? false : true;
			if(!$outside_link) {
				if(file_exists(path($file))) {
					return '<script src="' . chref($file) . '"></script>' . "\n";
				} else {
					display_system('The file <strong>' . path($file) . '</strong> does not exist');
				}
			} else {
					return '<script src="' . $file . '"></script>' . "\n";
			}
		}

		// Add a CSS file <head>, check for IE condition
		public function addCss($file, $media = '') {
			$outside_link = (strpos($file, 'http://') === false) ? false : true;
			$media = ($media == '') ? '' : 'media="' . $media . '"';
			if(!$outside_link) {
				if(file_exists(path($file))) {
					return "<link " . $media . " type='text/css' href='" . chref($file) . "' rel='stylesheet' />" . "\n";
				} else {
					display_error('The file <strong>' . path($file) . '</strong> does not exist');
				}
			} else {
				return "<link " . $media . " type='text/css' href='" . $file . "' rel='stylesheet' />" . "\n";
			}
		}

		// Add the generated css
		private function addGeneratedCss() {
			if(!$this->added_generated_css) {
				$this->getHead()->appendContent(
					$this->addCss('/public/style/generatrix-reset.css') .
					$this->addCss('/public/style/generatrix.css')
				);
				$this->added_generated_css = true;
			}
		}

		// Add the GOOGLE Ajax Libraries
		private function addGoogleAjaxLibraries() {
			$content = '';
			if(JS_JQUERY != '') {
				$content .=  $this->addJavascript('/public/javascript/jquery-' . JS_JQUERY . '.min.js');	
			}

			if(JS_COOKIE == '1') {
				$content .= $this->addJavascript('/public/javascript/jquery.cookie.min.js');
			}

			if(GOOGLE_FONTS != '') {
				$fonts = GOOGLE_FONTS;
				$semicolons = explode(';', $fonts);
				foreach($semicolons as $font) {
					$content .= $this->addCss('http://fonts.googleapis.com/css?family=' . trim($font));
				}
			}

			$content .= "
<script>
	var Generatrix = {
		basepath: '" . href('') . "',
		use_cdn: " . USE_CDN . ",
		cbasepath: '" . chref('') . "',
		href: function(path) { return this.basepath + path; },
		chref: function(path) { if(this.use_cdn) { return this.cbasepath + this.href(path) } else { href(path) } },
		loading: function(where, image_name) { $(where).html(\"<img src='\" + this.href('/public/images/' + image_name) + \"' />\"); },
		timestamp: function() { var d = new Date(); return d.getTime() / 1000; },
		rand: function(max) { return Math.ceil(Math.random() * max); },
		encode: function (string) { return escape(this._utf8_encode(string)); },
		decode: function (string) { return this._utf8_decode(unescape(string)); },
		_utf8_encode : function (string) {
			string = string.replace(/" . '\r\n' . "/g,'" . '\n' . "');
			var utftext = '';
			for (var n = 0; n < string.length; n++) {
				var c = string.charCodeAt(n);
				if (c < 128) {
					utftext += String.fromCharCode(c);
				} else if((c > 127) && (c < 2048)) {
					utftext += String.fromCharCode((c >> 6) | 192);
					utftext += String.fromCharCode((c & 63) | 128);
				} else {
					utftext += String.fromCharCode((c >> 12) | 224);
					utftext += String.fromCharCode(((c >> 6) & 63) | 128);
					utftext += String.fromCharCode((c & 63) | 128);
				}
			}
			return utftext;
		},
		_utf8_decode : function (utftext) {
			var string = '';
			var i = 0;
			var c = c1 = c2 = 0;
			while ( i < utftext.length ) {
				c = utftext.charCodeAt(i);
				if (c < 128) {
					string += String.fromCharCode(c);
					i++;
				} else if((c > 191) && (c < 224)) {
					c2 = utftext.charCodeAt(i+1);
					string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
					i += 2;
				} else {
					c2 = utftext.charCodeAt(i+1);
					c3 = utftext.charCodeAt(i+2);
					string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
					i += 3;
				}
			}
			return string;
		}
	};
	String.prototype.trim = function() {
		return this.replace(/^\s*/, \"\").replace(/\s*$/, \"\");
	};
</script>
			";
			$this->getHead()->appendContent($content);
			//return;

			$loadGoogle = false;
			$content = $this->addJavascript('http://www.google.com/jsapi');
			if(JS_COOKIE) {
				$content .= $this->addJavascript('/public/javascript/jquery.cookie.min.js');
			}
			$content .= '<script>';

			if( (JS_JQUERYUI != '') || (JS_PROTOTYPE != '') || (JS_SCRIPTACULOUS != '') || (JS_MOOTOOLS != '') || (JS_DOJO != '') || (JS_SWFOBJECT != '') || (JS_YUI != '') || (JS_EXT_CORE != '') ) {
				$loadGoogle = true;
			}

			if(JS_JQUERYUI != '') $content .= 'google.load("jquery", "' . JS_JQUERYUI . '");';
			if(JS_PROTOTYPE != '') $content .= 'google.load("jquery", "' . JS_PROTOTYPE . '");';
			if(JS_SCRIPTACULOUS != '') $content .= 'google.load("jquery", "' . JS_SCRIPTACULOUS . '");';
			if(JS_MOOTOOLS != '') $content .= 'google.load("jquery", "' . JS_MOOTOOLS . '");';
			if(JS_DOJO != '') $content .= 'google.load("jquery", "' . JS_DOJO . '");';
			if(JS_SWFOBJECT != '') $content .= 'google.load("jquery", "' . JS_SWFOBJECT . '");';
			if(JS_YUI != '') $content .= 'google.load("jquery", "' . JS_YUI . '");';
			if(JS_EXT_CORE != '') $content .= 'google.load("jquery", "' . JS_EXT_CORE . '");';

			$content .= '</script>';
			if($loadGoogle) {
				$this->getHead()->appendContent($content);
			}
		}

		// Add the libraries
		private function addLibraries() {
			if(MOBILE_SITE == 1) {

			} else {
				$this->addGeneratedCss();
				$this->addGoogleAjaxLibraries();
			}
		}

		public function prepareHead() {
			$content = '';
			$content .= "<meta charset=\"utf-8\">\n";
			$content .= "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">\n";
			$content .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";

			if( (APPLICATION_FAVICON != '') && file_exists(path(APPLICATION_FAVICON)) ) {
				$content .= "<link rel=\"shortcut icon\" href=\"" . APPLICATION_FAVICON . "\">\n";
			}

			if( (APPLICATION_TOUCH_ICON != '') && file_exists(path(APPLICATION_TOUCH_ICON)) ) {
				$content .= "<link rel=\"apple-touch-icon\" href=\"" . APPLICATION_TOUCH_ICON . "\">\n";
			}

			$content .= "<script src=\"" . href("/public/javascript/modernizr-1.6.min.js") . "\"></script>\n";

			$this->getHead()->appendContent($content);
		}

		// Public function which adds stuff to the <head> from the view
		public function add($list) {
			$head = $this->getHead();
			$files = (array) json_decode($list, true);

			if(checkArray($files, 'css')) {
				foreach($files['css'] as $file) {
					$head->appendContent($this->addCss($file));
				}
			}

			if(checkArray($files, 'js')) {
				foreach($files['js'] as $file) {
					$head->appendContent($this->addJavascript($file));
				}
			}

			$this->setHead($head);
		}

		// Public function to set the title for a page
		public function title($title) {
			$this->getHead()->appendContent("<title>" . $title . TITLE_TEXT . "</title>\n");
		}

		public function description($desc) {
			$this->getHead()->appendContent("<meta name=\"description\" content=\"" . addslashes($desc) . "\">\n");
		}

		// The function loads the sub views with html in them
		public function loadSubView($sub_view) {
			ob_start();
			include(path('/app/subviews/' . $sub_view . '.php'));
			$data = ob_get_contents();
			ob_end_clean();
			return $data;
		}

		// This function loads the control from the framework directory
		public function loadControl($control_name, $control_options) {
			$this->control_name = $control_name;
			$this->control_options = $control_options;

			ob_start();
			include(path('/framework/controls/' . $control_name . '/' . $control_name . '.php'));
			$data = ob_get_contents();
			ob_end_clean();

			$this->control_name = '';
			$this->control_options = array();

			return $data;
		}

		public function getP($number) {
			$url = $this->getURL();
			$p1 = isset($url[$number]) ? mysql_real_escape_string($url[$number], $this->getGeneratrix()->getController()->getDb()->getConnection()) : false;
			return $p1;
		}

		public function getURL() {
			return $this->getGeneratrix()->getRequestArray();
		}

	}

?>
