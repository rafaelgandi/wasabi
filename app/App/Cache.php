<?php 
namespace App;

use App;
use Exception;
use CacheCache;
use QB;

class Cache extends App\Base {
	public static $instance = false;
	public static $local_instance = false;
	public static $HOUR = 3600; // Number of seconds in an hour
	public static $flash_message_prefix = 'appflash_';
	// Basically just an alias for CacheCache\CacheManager
	// See: http://maximebf.github.io/CacheCache/
	public static function instance() {
		if (self::$instance === false) {
			$cache_dir = doc_root().'/'.get_config('app_namespace').'_Cache';
			if (! is_dir($cache_dir)) {
				@mkdir($cache_dir, 0777, true);
			}
			// Cache initializations //
			CacheCache\CacheManager::setup(new CacheCache\Backends\File(array(
				'dir' => $cache_dir,
				'file_extension' => '.cache'
			)));
			self::$instance = CacheCache\CacheManager::get();
		}
		return self::$instance;
	}
	
	public static function localInstance() {
		if (self::$local_instance === false) { self::$local_instance = new self; }
		return self::$local_instance;
	}
	
	private function __construct() {}
	
	public function __call($_method, $_parameters) {
		return parent::handleCalls(self::localInstance(), $_method, $_parameters);
	}
	
	public static function __callStatic($_method, $_parameters) {
		// See: http://usman.it/laravel-4-uses-static-not-true/
		return parent::handleCalls(self::localInstance(), $_method, $_parameters);
	}
	
	protected function flashSet($_key, $_value, $_iden=false) {
		$cache = self::instance();
		if ($_iden !== false) {
			$cache->set(self::$flash_message_prefix.$_iden.$_key, $_value);
		}
		else {
			$cache->set(self::$flash_message_prefix.$_key, $_value);
		}
		return $this;
	}
	
	protected function flashGet($_key, $_iden=false) {
		$cache = self::instance();
		$key = ($_iden !== false) 
					? self::$flash_message_prefix.$_iden.$_key 
					: self::$flash_message_prefix.$_key;
		if (! $cache->exists($key)) { return false; }		
		$value = $cache->get($key);
		$cache->delete($key);
		return $value;
	}
	
	protected function flashExists($_key, $_iden=false) {
		$cache = self::instance();
		$key = ($_iden !== false) 
					? self::$flash_message_prefix.$_iden.$_key 
					: self::$flash_message_prefix.$_key;			
		return $cache->exists($key);
	}
}