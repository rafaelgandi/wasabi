<?php 
namespace App;

use App;
use Exception;
use CacheCache;
use QB;

class Cache extends App\Base {
	public static $instance = false;
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
	private function __construct() {}
}