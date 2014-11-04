<?php 
namespace Uwa;

use Uwa;
use Exception;
use WP_Query; // See: http://www.billerickson.net/code/wp_query-arguments/
use QB; // See: https://github.com/usmanhalalit/pixie

class Mailer extends Uwa\Base {
	private static $instance = false;
	private static $methods = false;
	
	public static function instance() {
		if (self::$instance === false) { self::$instance = new self; }
		return self::$instance;
	}
	
	private function __construct() {
	
	}
	
	public function __call($_method, $_parameters) {
		return parent::handleCalls(self::instance(), $_method, $_parameters);
	}
	
	public static function __callStatic($_method, $_parameters) {
		// See: http://usman.it/laravel-4-uses-static-not-true/
		return parent::handleCalls(self::instance(), $_method, $_parameters);
	}
	
}