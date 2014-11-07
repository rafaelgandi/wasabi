<?php 
function xplog($_msg, $_class_info=false) {
	$logfile = doc_root().'/debug.logs';
	if (! is_readable($logfile)) {
		touch($logfile);
	}	
	$nl = chr(10);
	$msg = '';
	$data = array();
	$data['msg'] = '['.trim($_msg).']';
	$data['request_uri'] = $_SERVER['REQUEST_URI'];
	$data['ip'] = $_SERVER['REMOTE_ADDR'];
	if (!! $_class_info) {
		if (is_object($_class_info)) {
			$data['classname'] = get_class($_class_info);
		}
		else {
			$data['code'] = $_class_info;
		}
	}
	$msg = str_replace('\n', ' ', json_encode($data));
	$msg = $nl.'INFO - '.date('Y-m-d h:i:s A').' --> '.str_replace('\\', '', $msg);  
	@file_put_contents($logfile, $msg, FILE_APPEND);
}

function make_file($_file_path, $_contents='') {
	$file_handler = fopen($_file_path, 'w');
	fwrite($file_handler, $_contents);
	fclose($file_handler);
	return basename($_file_path);
}

function flash_message($_message='') {
	
}