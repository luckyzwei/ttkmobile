<?php 
$arr = array (
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
);	
	
file_put_contents('search_2.txt', json_encode($arr));	

?>

