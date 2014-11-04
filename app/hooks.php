<?php 
/* 
	Place all wordpress hooks here

 */
 

// See: http://codex.wordpress.org/Plugin_API/Action_Reference/wp_logout
function uwa_on_logout() {
    $cache = App\Cache::instance();
	$cache->delete('user_cache:details');
	$cache->delete('user_cache:roles');	
}
add_action('wp_logout', 'uwa_on_logout');


// See: http://codex.wordpress.org/Plugin_API/Action_Reference/wp_login
function uwa_on_login($user_login, $user) {
    //$user =  wp_get_current_user();
	$cache = App\Cache::instance();
	$user_cache = $cache->ns('user_cache');
	if (!! $user) {
		$user_cache->set('details', json_encode($user->data));
		$user_cache->set('roles', json_encode($user->roles));
	}
}
add_action('wp_login', 'uwa_on_login', 10, 2);
