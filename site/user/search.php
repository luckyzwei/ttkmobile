<?php 
$arr = array (
	'movie' => array (
		array (
			'id' => '34830',
			'name' => '小时代4:灵魂尽头',
			'pic' => 'http://img03.sogoucdn.com/net/a/04/link?appid=100140019&url=http://i1.letvimg.com/lc02_isvrs/201505/13/09/40/5cf58922-c0f2-451c-a5fa-a5961182063b.jpg',
		),
		array (
			'id' => '68239',
			'name' => '情剑',
			'pic' => '',
		),
		array (
			'id' => '70655',
			'name' => '道士下山',
			'pic' => '',
		),
		array (
			'id' => '73173',
			'name' => '功夫',
			'pic' => '',
		),
		array (
			'id' => '30730',
			'name' => '碟中谍4',
			'pic' => '',
		)
	),
	'teleplay' => array (
		array (
			'id' => '42888',
			'name' => '大好时光',
			'pic' => 'http://img02.sogoucdn.com/net/a/04/link?appid=100140019&url=http://pic3.qiyipic.com/image/20151016/94/a0/a_100012874_m_601_m1_195_260.jpg',
		),
		array (
			'id' => '42896',
			'name' => '琅琊榜',
			'pic' => '',
		),
		array (
			'id' => '42895',
			'name' => '多情江山',
			'pic' => '',
		),
		array (
			'id' => '74553',
			'name' => '亲密姐妹',
			'pic' => '',
		),
		array (
			'id' => '42874',
			'name' => '冰与火的青春',
			'pic' => '',
		),
    ),
	'anime' => array (
		array (
			'id' => '52621',
			'name' => '复仇者联盟:英雄集结',
			'pic' => 'http://img02.sogoucdn.com/net/a/04/link?appid=100140019&url=http://img36.pplive.cn/SP423/2015/10/25/22515102167.jpg',
		),
		array (
			'id' => '53380',
			'name' => '蓝猫小学英语600句',
			'pic' => '',
		),
		array (
			'id' => '68226',
			'name' => '樱桃小丸子',
			'pic' => '',
		),
		array (
			'id' => '68307',
			'name' => '少年华佗',
			'pic' => '',
		),
		array (
			'id' => '71140',
			'name' => '星梦园',
			'pic' => '',
		)
    ),
	'variety' => array (
		array (
			'id' => '73146',
			'name' => '快乐大本营',
			'pic' => 'http://g4.ykimg.com/051600005458425F67379F658404D211',
		),
		array (
			'id' => '73126',
			'name' => '天天向上',
			'pic' => '',
		),
		array (
			'id' => '68226',
			'name' => '樱桃小丸子',
			'pic' => '',
		),
		array (
			'id' => '68307',
			'name' => '少年华佗',
			'pic' => '',
		),
		array (
			'id' => '71140',
			'name' => '星梦园',
			'pic' => '',
		),
    ),
);	
	
file_put_contents('search.txt', json_encode($arr));	

?>

