<?php 
namespace Uwa;

use App;
use Uwa;
use Exception;

class Base extends App\Base {
	protected static function handleCalls($_instance, $_method, $_parameters) {
		return parent::handleCalls($_instance, $_method, $_parameters);
	}
}