<?php 
/* 
	Place all wordpress hooks here

 */
 
// See: http://codex.wordpress.org/Plugin_API/Action_Reference/wp_logout
function uwa_on_logout() {
    $curr_user = wp_get_current_user(); 

}
add_action('wp_logout', 'uwa_on_logout');


// See: http://codex.wordpress.org/Plugin_API/Action_Reference/wp_login
function uwa_on_login($user_login, $user) {
	$uwa_user = Uwa\Users::instance();
	$current_user_roles = $user->roles;
	if ($uwa_user->onlyAllowRoles('uwa_admin', $user->roles)) {
		wp_redirect(home_url()); exit;
	}
	else {
		wp_redirect(home_url().'/new-events/'); exit;
	}
}
add_action('wp_login', 'uwa_on_login', 10, 2);

// See: http://codex.wordpress.org/Plugin_API/Action_Reference/init
function check_signout() {
	if(isset($_REQUEST['signout'])) {
		wp_clear_auth_cookie();
		wp_logout(); //LM: 11-03-2014
		wp_redirect( home_url().'/login');
		exit;
	}
}
add_action('init', 'check_signout');