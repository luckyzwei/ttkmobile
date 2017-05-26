<?php
require_once 'config.const.php';
return array (
	'controllor_path' => APP_PATH . 'controllors/',
	
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
	),
	'error_strings' => array(
		'SIGN_ERR' => '签名错误',
		'FIELDS_ERR' => 'fields字段有误'
	),
	
	'search_index' => array(
		'1' => array('爱情','动画','动作','纪录片','惊悚','警匪','剧情','科幻','恐怖','伦理','奇幻','青春','微电影','文艺','武侠','喜剧','悬疑','音乐','灾难','战争'),
		'2' => array('青春','家庭','军旅','言情','古装','武侠','偶像','谍战','宫廷','喜剧','悬疑','历史','年代','都市','伦理','警匪','科幻','动作','农村','神话','战争','情景'),
		'3' => array('动作','亲子','热血','冒险','同人','原创','古代','未来','竞技','体育','搞笑','言情','校园','都市','魔幻','科幻','励志','剧情','悬疑','宠物','LOLI','益智','童话','真人','神话'),
		'years' => array('全部','2015','2014','2013','2012','2011','2010','2009','2008','2007','2006','2005','2004','2003','2002','2001','2000','其他'),
		'areas' => array('内地','香港','台湾','韩国','日本','泰国','欧美','其他')
	),
	
) + require(DATA_PATH . 'config.var.php');