<?php 
/* 
	LM: 10-30-2014
	Site configurations
	Demo site: http://uwaeventscrm.sushidigitaldemo.info/new-events/
 */
$__CONFIG = array(
	'app_namespace' => 'Uwa',
	'database' => array(
		// From wp-config.php
		'host' => $db_host,
		'name' => $db_name,
		'username'  => $db_user,
		'password'  => $db_pass
	),
	'localhost' => array(
		'site_directory' => 'uwaeventscrm'
	)
);
function get_config($_key) { 
	$value = '';
	$_key = trim($_key, '.');
	if (strpos($_key, '.') !== false) {
		// Make dot query logic here. //
		$k = explode('.', $_key);
		$container = $GLOBALS['__CONFIG'];
		foreach ($k as $kv) {
			$container = $container[$kv];
		}
		return $container;
	}
	return $GLOBALS['__CONFIG'][$_key];
}
function sushi_dir() { return get_template_directory(); } 
function app_dir() { return sushi_dir().'/app'; } 
function vendors_dir() { return app_dir().'/vendors'; } 
function public_dir() { return app_dir().'/public'; } 
function helpers_dir() { return app_dir().'/helpers'; } 
function is_local() {
	$localhosts = array('localhost', 'hive');	   
	return in_array($_SERVER['SERVER_NAME'], $localhosts);
}
function doc_root() {
	if (is_local()) {
		return $_SERVER['DOCUMENT_ROOT'].'/'.get_config('localhost.site_directory');
	}
	return $_SERVER['DOCUMENT_ROOT'];
}
function ajax_uri() {
	return get_option('siteurl').'/wp-admin/admin-ajax.php';
}
