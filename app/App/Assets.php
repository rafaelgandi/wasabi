<?php 
namespace App;

use App;
use Exception;
use CacheCache;
use QB;

class Assets extends App\Base {
	public static $instance = false;

	public static function instance() {
		if (self::$instance === false) { self::$instance = new self; }
		return self::$instance;
	}
	public function __call($_method, $_parameters) {
		return parent::handleCalls(self::instance(), $_method, $_parameters);
	}
	
	public static function __callStatic($_method, $_parameters) {
		// See: http://usman.it/laravel-4-uses-static-not-true/
		return parent::handleCalls(self::instance(), $_method, $_parameters);
	}
	private function __construct() {}
	
	protected function js($_scripts=array(), $_in_footer=false) {
		if (! is_array($_scripts)) {
			// Normalize into array
			$_scripts = array($_scripts);
		}
		// See: http://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
		add_action('wp_enqueue_scripts', function () use ($_scripts, $_in_footer){
			foreach ($_scripts as $name => $path) {
				if (is_numeric($name)) {
					$name = md5($path.time());
				}
				// See: http://codex.wordpress.org/Function_Reference/wp_register_script
				wp_enqueue_script($name, $path, false, false, $_in_footer);
			}			
		});
		return $this;
	}
	
	protected function css($_css=array()) {
		if (! is_array($_css)) {
			// Normalize into array
			$_css = array($_css);
		}
		// See: http://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
		add_action('wp_enqueue_scripts', function () use ($_css){
			foreach ($_css as $name => $path) {
				if (is_numeric($name)) {
					$name = md5($path.time());
				}
				wp_enqueue_style($name, $path, false);
			}			
		});
		return $this;
	}
}