<?php 
namespace Uwa;

use App;
use Uwa;
use Exception;
use WP_Query; // See: http://www.billerickson.net/code/wp_query-arguments/
use WP_User_Query; // See: http://codex.wordpress.org/Class_Reference/WP_User_Query
use QB; // See: https://github.com/usmanhalalit/pixie

class Users extends Uwa\Base {
	private static $instance = false;
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
		$uid = $curr_user->ID;
		return (object) array(
			'wp' => $curr_user, // Wordpress object
			'details' => $curr_user->data,
			'roles' => $curr_user->roles,
			'ID' => $uid
		);
	}
	
	protected function mustBeLogin($_redirect=false) {
		$user = $this->getLoggedUserDetails();
		if ($user === false) {
			if ($_redirect !== false) {
				// User not logged in yet, redirect to homepage
				wp_logout(); // Make sure the logout hook is called.
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
		global $wp_roles; // See: http://codex.wordpress.org/Function_Reference/remove_cap
		// See: http://stackoverflow.com/questions/13162330/wordpress-list-user-roles
		$roles = $wp_roles->get_names();
		$uwa_roles = array();
		foreach ($roles as $key=>$label) {
			if (in_array($key, $this->roles)) {
				$uwa_roles[$key] = $label;
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
	
	protected function getAll($_filters=array()) {
		$return_arr = array();
		$f = array_merge(array(
			// Defaults //
			'num_per_page' => -1,
			'role' => 'all'
		), $_filters);
		
		$that = $this;
		$query = QB::table('wp_users')
		->select(array(
			'wp_users.ID',
			'wp_users.user_login',
			'wp_users.user_nicename',
			'wp_users.user_email',
			'wp_users.display_name'
		))
		->join('wp_usermeta', function ($table) {
			// See: https://github.com/usmanhalalit/pixie#join
			$table->on('wp_usermeta.user_id', '=', 'wp_users.ID');
		})
		->where('wp_usermeta.meta_key', 'wp_capabilities')
		->where('wp_users.user_status', 0);
		if ($f['role'] === 'all') {
			$query->where(function ($q) use (&$that) {
				$q->where('wp_usermeta.meta_value', 'LIKE', '%uwa_students%');
				foreach ($that->getUwaRoles() as $role=>$label) {
					$q->orWhere('wp_usermeta.meta_value', 'LIKE', '%'.$role.'%');
				}		
			});	
		}
		else {
			if (! in_array($f['role'], $this->roles)) { return false; } // Make sure the roles passed is valid.
			$query->where('wp_usermeta.meta_value', 'LIKE', '%'.$f['role'].'%');
		}
		$query->orderBy('wp_users.user_login', 'ASC');
		
		if ($f['num_per_page'] !== -1) { // If pagination is enabled.
			$total = $query->count();
			$big = 999999999;
			$page = (get_query_var('page')) ? intval(get_query_var('page')) : 1;
			$offset = $f['num_per_page'] * ($page - 1);
			// See: http://www.kevinleary.net/wordpress-pagination-paginate_links/
			$paginate_links = paginate_links(array(
				'base' => str_replace($big, '%#%', get_pagenum_link($big)),
				'current' => max(1, $page),
				'total' => ceil($total/$f['num_per_page']),
				'prev_text'    => __('&laquo;'),
				'next_text'    => __('&raquo;'), 
				'mid_size' => 5
			));	
			// See: https://github.com/usmanhalalit/pixie#limit-and-offset	
			$query->limit($f['num_per_page']);
			$query->offset($offset);
		}
		$users = $query->get();	
		if (!! $users) {
			foreach ($users as $u) {
				$metas = get_user_meta($u->ID);
				$u->meta = array(
					'first_name' => $metas['first_name'],
					'last_name' => $metas['last_name'],
					'wp_capabilities' => $metas['wp_capabilities'],
					'nickname' => $metas['nickname']
				);
				$u->roles =array_keys(unserialize($u->meta['wp_capabilities'][0]));
				$return_arr[] = $u;
			}
			if ($f['num_per_page'] === -1) { // Without pagination
				return $return_arr;
			}
			return (object) array(
				'list' => $return_arr,
				'pagination' => str_replace('page-numbers', 'uwa-page-numbers', $paginate_links)
				//'pagination' => $paginate_links
			);
		}
		else { xplog('Unable to get all users', __METHOD__); }
		return false;
	}
	
	protected function getUserById($_user_id) {
		$uid = intval($_user_id);
		if ($uid <= 0) { return false; }
		// See: http://codex.wordpress.org/Function_Reference/get_userdata
		$user_details = get_userdata($uid);
		return $user_details;
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
		return $user_id;
	}
	
	protected function edit($_user_id, $_details=array()) {
		$uid = intval($_user_id);
		$_details['ID'] = $uid;
		// See: http://codex.wordpress.org/Function_Reference/wp_update_user
		$user_id = wp_update_user($_details);
		if (! is_numeric($user_id)) {
			xplog('Unable to update user with the following details['.json_encode($_details).']', __METHOD__);
			return false;
		}
		return true;
	}
	
}