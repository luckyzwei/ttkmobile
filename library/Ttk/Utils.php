<?php
class Ttk_Utils
{
	

	/**
	 * @param string $str
	 * @return string
	 */
	public static function encode($str)
	{
		return str_replace('=', 'c', str_replace('/', 'b', str_replace('+', 'a', trim($str))));
	}
	
	/**
	 * @param string $words
	 * @param int $wordsLen
	 * @return array
	 */
	public static function splitWords($words, $wordsLen = 200)
	{
		$ret = array();
		$words = Lamb_Utils::mbSubstr($words, 0, $wordsLen);
		$words = preg_replace('/,|\.|，|。|\.|;|\:|"|\'/is', '', $words);
		$cfg = Lamb_Registry::get(PUBLIC_CFG);
		
		$cws = scws_new();
		$cws->set_charset(Lamb_App::getGlobalApp()->getCharset());
		$cws->set_rule($cfg['scws_rule_path']);
		$cws->set_dict($cfg['scws_dict_path']);
		unset($cfg);
		$cws->send_text($words);
		while ($temp = $cws->get_result()) {
			foreach ($temp as $temp1) {
				$ret[] = $temp1['word'];
			}
		}
		$cws->close();
		return $ret;
	}
	
	/**
	 *
	 */
	public static function encodeFullSearchStr($string, $len = 200)
	{
		$ret = '';
		foreach (Ttk_Utils::splitWords($string, $len) as $item) {
			$ret .= base64_encode(strtoupper($item)) . ' ';
		}
		
		return self::encode(trim($ret));
	}
	
	/**
	 * 按密钥加密
	 */
	public static function auth_encode($data, $key, $expire)
	{
		$key = md5($key);
		$iv = substr($key, 0, 16);
		$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		mcrypt_generic_init($cipher, $key, $iv);
		$data = $expire . ',' . time() . ',' . $data;
		
		$padsize = Lamb_Utils::mbLen($data) % 32;
		if ($padsize > 0) {
			$padsize = 32 - $padsize;
			$data .= str_repeat(chr(0), $padsize);
		}
		
		$str = mcrypt_generic($cipher, $data);
		mcrypt_generic_deinit($cipher);
		return base64_encode($str);		
	}
	
	public static function flushCDN($url)
	{
	//----item.m.ttkvod.com/?c=index&a=info&id=10
	}
	
	/**
	 * 按密钥解密
	 */
	public static function auth_decode($data, $key, &$isExpire = 0)
	{
		$key = md5($key);
		$iv = substr($key, 0, 16);
		$decipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		mcrypt_generic_init($decipher, $key, $iv);
		$data = base64_decode($data);
		
		if (!$data) {
			return '';
		}
		
		$str = mdecrypt_generic($decipher, $data);
		mcrypt_generic_deinit($decipher);
		$str = str_replace(chr(0), '', $str);
		
		if (!empty($str) && ($pos = strpos($str, ',')) !== false) {
			$expire = substr($str, 0, $pos);
			$str = substr($str, $pos + 1);
			
			if (($pos = strpos($str, ',')) !== false) {
				$ts = substr($str, 0, $pos);
				$str = substr($str, $pos + 1);
				
				if (Lamb_Utils::isInt($expire, true) && Lamb_Utils::isInt($ts, true) && time() - $ts < $expire) {
					$isExpire = 0;
					return $str;
				}
				
				if ($isExpire == -1) {
					$isExpire = 1;
					return $str;
				}
				$isExpire = 1;
			}
		}
		
		return '';		
	}
	
	/**
	 * 生成密码的salt，防止密码hash碰撞 
	 *
	 * @param int $min
	 * @param int $max
	 * @return string
	 */
	public static function createSalt($min = 5, $max = 10)
	{
		$ret = '';
		if ($min > $max) {
			$max = $min;
		}
		$key = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$len = rand($min, $max);
		$salt_len = strlen($key) - 1;
		for ($i = 1; $i <= $len; $i ++) {
			$ret .= $key{rand(0, $salt_len)};
		}
		return $ret;
	}
	
	/**
	 * 验证是否为手机号
	 * 注：只验证是否为1开头的11位数字
	 * 
	 * @param string $val
	 * @return boolean
	 */
	public static function isPhone($val)
	{
		return Lamb_Utils::isInt($val, true) && strlen($val) == 11 && substr($val, 0, 1) == 1;
	}	
	
	/**
	 * 获取当前日期0时0分0秒的时间戳
	 * 
	 * @return int
	 */
	public static function getCurrentDaytime()
	{
		return strtotime(date('Y-m-d 00:00:00'));
	}	
	
	
	/**
	 * 合并2个数组，并可以以inner,left,rigth,normal模式合并
	 * inner:如果任意一个数组成员中有null，则会放弃合并这一行
	 * left:以primaryData为主，不管otherData有没有null的成员，都会合并。如果primaryData成员有null，则不会合并
	 * right:以otherData为主，不管primaryData有没有null的成员，都会合并。如果otherData成员有null,则不会合并
	 * 
	 * @param array &$primaryData 主数据
	 * @param array $otherData 要合并的其他数据
	 * @param string $model 模式
	 * @return void
	 */
	public static function arrayCombine(&$primaryData, $otherData, $mode = 'inner')
	{
		$isDelete = false;
		foreach ($primaryData as $index => $item) {
			if (!array_key_exists($index, $otherData)) {
				continue;
			}
			
			if ($mode == 'inner') {
				if (!isset($primaryData[$index]) || !isset($otherData[$index])) {
					$isDelete = true;
					unset($primaryData[$index]);
					continue;
				}			
			} else if ($mode == 'left') {
				if (!isset($primaryData[$index])) {
					$isDelete = true;
					unset($primaryData[$index]);
					continue;
				}
			} else if (!isset($otherData[$index])){
				$isDelete = true;
				unset($otherData[$index], $primaryData[$index]);
				continue;
			}
			
			if (isset($primaryData[$index]) && isset($otherData[$index])) {
				$primaryData[$index] += $otherData[$index];
			}
		}
		
		if ($isDelete) {
			$primaryData = array_values($primaryData);
		}
		unset($primaryData);
	} 
	
	/**
	 * 通过键将2个数组连接在在一起
	 * 
	 * @param array & $srcData 主数组
	 * @param array $joinData 附加拼接数组
	 * @param string $srcKey 主数组用于连接的键名
	 * @param string $joinKey 附加拼接数组用于连接的键名
	 * @param boolean $isDeleNotMatchs 是否删除未匹配到的
	 * @param int $deleKeyFlag 拼接完成后是否删除 $srcKey,$joinKey的标记，如果为0，则都不删除。1删除srcKey 2删除joinKey 3全部都删除
	 */
	public static function arrayCombineByKey(&$srcData, $joinData, $srcKey, $joinKey, $isDeleNotMatchs = true, $deleKeyFlag = 0)
	{
		$newJoinData = array();
		$isDelete = false;
		
		foreach ($joinData as $index => $item) {
			$newJoinData[$item[$joinKey]] = $item;
		}
		
		foreach ($srcData as $index => $item) {
			$val = $srcData[$index][$srcKey];
			
			if (isset($newJoinData[$val])) {
				$srcData[$index] = $newJoinData[$val] + $item;
				
				if ($deleKeyFlag & 1) {
					unset($srcData[$index][$srcKey]);
				}
				
				if ($deleKeyFlag & 2) {
					unset($srcData[$index][$joinKey]);
				}
			} else if ($isDeleNotMatchs){
				$isDelete = true;
				unset($srcData[$index]);
			}
		}
		
		if ($isDelete) {
			$srcData = array_values($srcData);
		}
		unset($srcData);
	}	
	
	/**
	 * 计算字符串所占字节长度
	 * 
	 * @param string $str 要计算的字符串
	 * @return int
	 */
	public static function strLen($str)
	{
		return (strlen($str) + mb_strlen($str,'UTF8')) / 2;
	}
	
	/**
	 * 查找或过滤emoji表情字符串
	 * 
	 * @param {string} $str字符串
	 * @param {string} $replaceMenu 要替换的字符串，如果不要替换则为null
	 * @param {boolean} &$isFind 是否找到
	 * @return {string} 返回替换后最新的结果
	 */
	public static function findEmojiString($str, $replaceMent = null, &$isFind)
	{
		$isFind = false;
		$len = mb_strlen($str, 'UTF8');
		
		for ($i = 0; $i < $len; $i++) {
			$word = mb_substr($str, $i, 1, 'UTF8');
			if (strlen($word) == 4) {
				$isFind = true;
				if ($replaceMent !== null) {
					$str = mb_substr($str, 0, $i, 'UTF8') . $replaceMent . mb_substr($str, $i + 1, $len - $i + 1, 'UTF8');
					$len = $len + mb_strlen($replaceMent, 'UTF8') - 1;
					$i = $i + mb_strlen($replaceMent, 'UTF8') - 1;
				}
			}
		}
		
		return $str;
	}	
}
