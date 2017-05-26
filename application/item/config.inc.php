<?php
require_once 'config.const.php';
return array (
	'controllor_path' => APP_PATH . 'controllors/',
	'scws_rule_path' => 'e:\\scws\\rules.ini',
	'scws_dict_path' => 'e:\\scws\\dict.xdb',
	'encode_key' => 'tl)~t@y|m(^kj#lb%`%t$t^h*n(i)o%5',
	'form_rank_key' => 'm3n93adb04$fd12!',
	'form_rank_expire' => 180,
	'week_lock_path' => DATA_PATH . '/week.lock',
	
	'error_strings' => array(
		'SIGN_ERR' => '签名错误'
	)
	
) + require(DATA_PATH . 'config.var.php');