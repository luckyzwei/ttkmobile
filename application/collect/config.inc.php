<?php
define('DATA_PATH', APP_PATH . 'data/');
define('CACHE_PATH', DATA_PATH . 'cache/');
define('CONFIG', 'site_config');

return array(
	'controllor_path' => APP_PATH . 'controllors/',
	'admin_controllor_path' => APP_PATH . 'admin/controllors/',
	'key' => 'e3mn0a6ef18ae59bi',
	'controllor_path' => APP_PATH . 'controllors/',
			
	'admin' => array(
		'username' => 'admin',
		'password' => '8b3a88a36e12b8c097cf70595593ae29'
	),
	
	'admin_purview' => array(
		'movie/' => array('name' => '影片')
	),
	
	'cfg_sys_servers' => array(
		'http://cfg.m.ttkvod.com/cfg_sync.php','http://list.m.ttkvod.com/cfg_sync.php'
	),
	
	'server_uid' => 1
) + require_once(DATA_PATH . 'config.var.php');
