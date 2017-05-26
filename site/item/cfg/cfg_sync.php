<?php
$trustIp = '42.121.14.26';
$data = isset($_POST['cfg']) ? $_POST['cfg'] : '';

try {
	$data = json_decode(rawurldecode($data), true);
} catch (Exception $e) {
	exit;
}

if (getIp() != $trustIp || !$data) {
	//exit;
}

$map = array(
	array('search_hot', 0)
);
$change = array();

if (count($map) == 1) {
	$map = $map[0];
	if ($map[1]) {//需要该key值作为配置文件中的key
		$change[$map[0]] = $data[$map[0]];
	} else {
		$change = $data[$map[0]];
	}
} else {
	foreach ($map as $item) {
		if ($item[1]) {//需要该key值作为配置文件中的key
			$change[$item[0]] = $data[$item[0]];
		} else {
			$change[] = $data[$item[0]];
		}
	}
}

file_put_contents('search.txt', json_encode($change));	

function getIp()
{
	$ip = '';
    if (@$_SERVER['HTTP_CLIENT_IP']) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if (@$_SERVER['HTTP_X_FORWARDED_FOR']) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;	
}

function debug($str)
{
	@ob_clean();
	if(is_array($str))
		print_r($str);
	elseif(is_bool($str))
	{
		if($str)
			echo "this value is true";
		else
			echo "this value is false";
	}
	else
		var_dump($str);		
	exit();		
}
?>


