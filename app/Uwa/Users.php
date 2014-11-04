<?php 
namespace Uwa;

use App;
use Uwa;
use Exception;
use WP_Query; // See: http://www.billerickson.net/code/wp_query-arguments/
use QB; // See: https://github.com/usmanhalalit/pixie

class Users extends Uwa\Base {
	private static $instance = false;
	private static $methods = false;
	private $roles = array('uwa_students', 'non_club_applicant', 'uwa_admin', 'uwa_events_staff', 'uwa_security', 'uwa_stakeholders');
	private $cache;
	
	public static function instance() {
		if (self::$instance === false) { self::$instance = new self; }
		return self::$instance;
	}
	
	private function __construct() {
		$this->cache = App\Cache::instance();
	}
	
	public function __call($_method, $_parameters) {
		return parent::handleCalls(self::instance(), $_method, $_parameters);
	}
	
	public static function __callStatic($_method, $_parameters) {
		// See: http://usman.it/laravel-4-uses-static-not-true/
		return parent::handleCalls(self::instance(), $_method, $_parameters);
	}
	
	protected function getLoggedUserDetails() {
		if (! is_user_logged_in()) { return false; }
		// See: http://codex.wordpress.org/Function_Reference/wp_get_current_user
		$curr_user = wp_get_current_user(); 
		return (object) array(
			'wp' => $curr_user, // Wordpress object
			'details' => ($this->cache->exists('user_cache:details')) ? json_decode($this->cache->get('user_cache:details')) : false,
			'roles' => ($this->cache->exists('user_cache:roles')) ? json_decode($this->cache->get('user_cache:roles'), true) : false
		);
	}
	
	protected function mustBeLogin($_redirect=false) {
		$user = $this->getLoggedUserDetails();
		if ($user === false) {
			if ($_redirect !== false) {
				// User not logged in yet, redirect to homepage
				if (is_string($_redirect)) {
					wp_redirect(home_url($_redirect)); exit;
				}
				wp_redirect(home_url()); exit;
			}		
			return false;
		}
		return $this;
	}
	
	protected function roleMustBe($_role, $_redirect_on_error_uri=false) {
		$roles = (! is_array($_role)) ? array(trim(strtolower($_role))) : $_role;
		$curr_user = $this->getLoggedUserDetails();
		$err_redirect = ($_redirect_on_error_uri !== false) 
							? home_url($_redirect_on_error_uri) 
							: home_url();
		if ($curr_user === false) {
			wp_redirect($err_redirect); exit;
		}
		if (is_array($curr_user->roles) && count($curr_user->roles) > 0) {
			foreach ($curr_user->roles as $r) {	
				if (in_array(trim(strtolower($r)), $roles)) {
					return $this;
				}	
			}		
		}
		wp_redirect($err_redirect); exit;
	}
	
	protected function onlyAllowRoles($_allowed_roles, $_current_user_roles) {
		if (! is_array($_allowed_roles)) { $_allowed_roles = array($_allowed_roles); }
		if (! is_array($_current_user_roles)) { $_current_user_roles = array($_current_user_roles); }
		foreach ($_allowed_roles as $allowed) {
			if (in_array($allowed, $_current_user_roles)) {
				return true;
			}
		}
		return false;
	}
	
	protected function getUwaRoles() {
		global $wp_roles;
		// See: http://stackoverflow.com/questions/13162330/wordpress-list-user-roles
		$roles = $wp_roles->get_names();
		$uwa_roles = array();
		foreach ($roles as $key=>$value) {
			if (in_array($key, $this->roles)) {
				$uwa_roles[$key] = $value;
			}
		}
		ksort($uwa_roles);
		return $uwa_roles;
	}
	
	protected function exists($_username) {
		$u = trim($_username);
		$res = QB::table('wp_users')->where('user_login', $u)->first();
		return !! $res;
	}
	
	protected function getAll($_opt=array()) {
		$return_arr = array();
		$opt = array_merge(array(
			// Defaults //
			'orderby' => 'display_name'
		), $_opt);
		// See:http://codex.wordpress.org/Function_Reference/get_users
		$users = get_users(array(
			'orderby' => $opt['orderby']
		));
		if (!! $users && is_array($users)) {
			foreach ($users as $wpu) {
				if ($this->onlyAllowRoles($this->roles, $wpu->roles)) {
					unset($wpu->allcaps);
					$return_arr[] = $wpu;
				}
			}
			return ((count($return_arr) > 0) ? $return_arr : false);
		}
		else {
			xplog('Unable to get all users', __METHOD__);
		}
		return false;
	}
	
	protected function add($_details=array()) {
		$details = array_merge(array(
			// Defaults //
			'first_name' => '',
			'last_name' => ''
		), $_details);
		if ($this->exists($details['username'])) {
			xplog('Username "'.$details['username'].'" already exists', __METHOD__);
			return false;
		}
		// See: http://codex.wordpress.org/Function_Reference/wp_insert_user
		$user_id = wp_insert_user(array(
			'user_login' => $details['username'], 
			'user_pass' => $details['password'],
			'role' => $details['role'],
			'first_name' =>  $details['first_name'],
			'last_name' =>  $details['last_name']
		));
		if (! is_numeric($user_id)) {
			xplog('Something went wrong while trying to add user "'.$details['username'].'"', __METHOD__);
			return false;
		}
		return true;
	}
	
}