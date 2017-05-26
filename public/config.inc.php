<?php
return array(
	'db_cfg' => array(
		'movie' => array (
			'dsn' => 'sqlsrv:Database=ttk;Server=localhost;MultipleActiveResultSets=true;LoginTimeout=10',
			'username' => 'm_ttkvod',
			'password' => 's_a_d_m_y_s_9185~'  //benben2003~!		
		),
		'ttk_api' => array (
			'dsn' => 'sqlsrv:Database=ttk_user_api;Server=localhost;MultipleActiveResultSets=true;LoginTimeout=10',
			'username' => 'ttk_user_api',
			'password' => 's_a_d_m_y_s_9185~'		
		)	
	),
	'cache_cfg' => array (
		'timeout' => 15,
		'type' => Ttk_Cache_Factory::CACHE_MEMCACHED,
		'mem_host' => '11c1e363edd34b46.m.cnhzaliqshpub001.ocs.aliyuncs.com',
		'mem_port' => 11211,
		'mem_pconnect' => true,
		'mem_connect_timeout' => 10
	),
	'notify_public_title' => '天天看',
	'notify_tpl_cfg' => array(
		'comment' => array(
			'body' => array(
				'有人回复了您的评论'
			)
		)
	),
	
	'top_client_cfg' => array(
		'appkey' => 23288900,
		'secretKey' => '9dd871c78594eed1e5ff7ad7a0d4df86'
	),
	
	'redis_cfg' => array(
	),
	
	'nkey_expire' => 600,
	'movie_cache_expire' => 86400,
	'user_cache_expire' => 86400,
	'event_encode_key' => 'efa53a5573bc208f69d40a',
	
	'scws_rule_path' => 'D:\ttk_mobile\scws\rules.ini',
	'scws_dict_path' => 'D:\ttk_mobile\scws\dict.xdb',
) + require_once(PUBLIC_DATA_PATH . 'config.var.php');