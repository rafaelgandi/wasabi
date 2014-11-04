<?php 
namespace Uwa;

use App;
use Uwa;
use Exception;
use WP_Query; // See: http://www.billerickson.net/code/wp_query-arguments/
use QB; // See: https://github.com/usmanhalalit/pixie

class Events extends Uwa\Base {
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
	
	protected function encode($_arr) {
		return json_encode($_arr);
	}
	
	protected function decode($_str) {
		return json_decode($_str, true);
	}
	
	protected function test() {
		xplog('hehehe', __METHOD__);
		return QB::table('uwa_clubssocities')->where('club_id', 2)->get();
	}
}