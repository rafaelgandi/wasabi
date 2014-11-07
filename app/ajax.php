<?php 
/* 
	Ajax stuff should go here

 */
class AjaxController {
	private $p;
	private $g;
	public function __construct() {
		$this->p = $_POST;
		$this->g = $_GET;
	}
	public function add_uwa_user() {
		echo 'hohohohoooho';
		exit;
	}
	
}
$ajax_instance = new AjaxController();
$methods = get_class_methods($ajax_instance); 
// Register the ajax methods to wordpress // 
if (count($methods) > 0) {
	foreach ($methods as $action) {
		if (strpos($action, '_') !== 0) {
			// See: http://www.andrewmpeters.com/blog/how-to-make-jquery-ajax-json-requests-in-wordpress/
			add_action('wp_ajax_nopriv_'.$action, array(&$ajax_instance, $action));
			add_action('wp_ajax_'.$action, array(&$ajax_instance, $action));	
		}			
	}	
}
