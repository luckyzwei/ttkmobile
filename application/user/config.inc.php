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
	'out_services_hash' => array(
		'ttkcoll' => array(
					'key' => 'm2c9ja710n4h6b81ap4sd3q',
					'expire' => 600
				),
		'ttkitem' => array(
					'key' => 'lamb2003cachettkvod12fa',
					'expire' => 600,
					'id' => 'ttkitem'
				)				
	)
) + require(DATA_PATH . 'config.var.php');