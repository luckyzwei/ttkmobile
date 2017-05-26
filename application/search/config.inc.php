<?php
require_once 'config.const.php';
return array (
	'controllor_path' => APP_PATH . 'controllors/',
	'encode_key' => 'tl)~t@y|m(^kj#lb%`%t$t^h*n(i)o%5',
	
	'error_strings' => array(
		'FIELDS_ERR' => 'fields错误'
	),
	
	
) + require(DATA_PATH . 'config.var.php');