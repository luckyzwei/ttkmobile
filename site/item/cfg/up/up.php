<?php 
	$arr = array(
		'type' => 1,
		'version' => 1,
		'desc' => '',
		'surl' => '',
		'rurl' => 'http://www.tiantiankan123.com/'
	);

	file_put_contents('up.txt', json_encode($arr));	

?>

