<?php 
namespace App;

use App;
use Exception;
use CacheCache;
use QB;

class Base {
	protected static function handleCalls($_instance, $_method, $_parameters) {
		if (in_array($_method, get_class_methods($_instance))) {
			if (strpos($_method, '_') !== 0) {
				return call_user_func_array(array($_instance, $_method), $_parameters);
			}
			throw new Exception('[[[Method "'.$_method.'()" is private]]]');	
		}
		else {
			throw new Exception('[[[Method "'.$_method.'()" not found in class "'.get_class($_instance).'"]]]');	
		}	
	}
}