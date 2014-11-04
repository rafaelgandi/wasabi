<?php 
/* 
	LM: 11-03-2014
	Bootstrap file

 */
error_reporting(E_ALL & ~E_NOTICE); // for development 
ini_set('display_errors', '1'); // show errors, remove when deployed

date_default_timezone_set('Australia/Perth');

require_once 'site.config.php';
require_once 'autoloader.php';
require_once 'database.php'; // Database initializations
require_once helpers_dir().'/base_helper.php';
require_once 'hooks.php';
require_once 'ajax.php';