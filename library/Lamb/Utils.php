<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb
 */
class Lamb_Utils
{
	const OBJECT_CALL_RET_ERROR = 0xffff;
	
	const FETCH_MODE_CURL = 1;
	
	const FETCH_MODE_HTTP = 2;
	
	const FETCH_MODE_FILE = 4;	
	
	/**
	 * @var array
	 */
	protected static $sSingleInstanceMaps = array();
	
	/**
	 * @var array
	 */
	protected static $sObjectCallHashs = array();
	
	/**
	 * @param string $url
	 * @param int $connectTimeout
	 * @param int $type enum[FETCH_MODE_CURL,FETCH_MODE_HTTP,FETCH_MODE_FILE]
	 * @return string
	 */
	public static function fetchContentByUrl($url, $connectTimeout = 10, $type = self::FETCH_MODE_CURL)
	{
		switch($type) {
			case self::FETCH_MODE_HTTP:
				return self::fetchContentByUrlH($url, $connectTimeout);
			case self::FETCH_MODE_FILE:
				return file_get_contents($url);
			default:
				return self::fetchContentByUrlC($url, $connectTimeout);
		}
	}
	
	/**
	 * @param string $url
	 * @param int $connectTimeout
	 * @return string
	 */
	public static function fetchContentByUrlC($url, $connectTimeout = 10)
	{
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;		
	}
	
	/**
	 * @param string $url
	 * @param int $connectTimeout
	 * @return string
	 */	
	public static function fetchContentByUrlH($url, $connectTimeout = 20)
	{
		$ret = Lamb_Http::quickGet($url, $connectTimeout, false, $status);
		if ($status == 200) {
			return $ret;
		}
		return '';
	}	
	
	/**
	 * 澶氩瓧鑺傜増镄剆ubstr
	 *
	 * @param string $str 瑕佹搷浣灭殑瀛楃涓?
	 * @param int $start
	 * @param int $len
	 * @return string
	 */
	public static function mbSubstr($str, $start)
	{
		preg_match_all("/[\x80-\xff]?./",$str,$ar);
		if(func_num_args() >= 3) {
		    $end = func_get_arg(2);
		    return join("",array_slice($ar[0],$start,$end));
		}else
		    return join("",array_slice($ar[0],$start));	
	}
	
	/**
	 * 澶氩瓧鑺傜増镄剆trlen
	 *
	 * @param string $str
	 * @return int
	 */
	public static function mbLen($str)
 	{
		preg_match_all("/[\x80-\xff]?./",$str,$ar);
		return count($ar[0]);
	}
	
	/**
	 * 甯﹀瘑阍ョ殑锷犲瘑瑙ｅ瘑鏂规硶
	 *
	 * @param string $string
	 * @param string $key
	 * @param string $operation 锷犲瘑 DECODE 瑙ｅ瘑 ENCODE
	 * @param int $expriy 链夋晥链?
	 * @return string
	 */
	public static function authcode($string, $key, $operation = 'DECODE',  $expiry = 0, $ckey_length = 7) 
	{	
		$key = md5($key);	
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
	
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
	
		$result = '';
		$box = range(0, 255);
	
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
					return '';
				}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	
	}
	
	/**
	 * @param string $str
	 * @return string
	 */
	public static function crc32FormatHex($str)
	{
		return sprintf("%0x", crc32($str));
	}
	
	/**
	 * @param int $feed
	 * @return string
	 */
	public static function getRandString($feed = 10000, $encode = false)
	{
		return $encode ? self::crc32FormatHex(microtime(1).rand(1,$feed)) : microtime(1).rand(1,$feed);
	}
	
	/**
	 * 鐢ㄤ簬涓€浜涘甫链夐粯璁ら厤缃殑阃夐」
	 *
	 * @param & array $arrDefault
	 * @param array $arrSrc
	 * @return void
	 */
	public static function setOptions(&$arrDefault, $arrSrc)
	{
		foreach($arrSrc as $k => $v){
			$arrDefault[$k]		=	$v;
		}
		unset($arrDefault);
	}	
	
	/**
	 * @param string $str
	 * @return boolean
	 */
	public static function isEmail($str)
	{
		return preg_match('/^[\w]+@[\w]+(?:\.[a-zA-Z]+)+$/is', (string)$str);
	}	
	
	/**
	 * @param string $str
	 * @return boolean
	 */
	public static function isHttp($str)
	{
		return !empty($str) && (strtolower(substr((string)$str, 0, 7)) == 'http://' || strtolower(substr((string)$str, 0, 8)) == 'https://');
	}
	
	/**
	 * 鍒ゆ柇婧愬瓧绗︿覆鏄惁涓烘暣鏁板鏋滃弬鏁?
	 * $bPostive涓篓rue锛屽垯鍒よ鏄惁姝ｆ暣鏁?
	 *
	 * @param string $str
	 * @param boolean $bPostive
	 * @return boolean
	 */
	public static function isInt($str, $bPostive = false)
	{
		return preg_match($bPostive ? '/^\d+$/s' : '/^-?\d+$/s', (string)$str);
	}
	
	/**
	 * 鍒ゆ柇$str鏄惁涓烘暟瀛楀寘鎷暣鏁板皬鏁帮紝
	 * 濡傛灉绗簩涓弬鏁颁负true锛屽垯鍒ゆ柇$str鏄惁姝ｇ殑鏁板瓧
	 *
	 * @param string $str
	 * @param boolean $bPostive
	 * @return boolean
	 */
	public static function isNumber($str, $bPostive = false)
	{
		return $bPostive ? preg_match('/^((\d+\.\d+)|(\d+))$/s', (string)$str) : is_numeric($str);
	}
	
	/**
	 * 妫€镆ユ槸鍚﹁緭鍏ヤ负姹夊瓧瀛?
	 *
	 * @param string $str
	 * @return boolean
	 */	
	public static function isChinese($str) 
	{
		return strlen($str) && !preg_match('/[^\x80-\xff]/is', $str);
    }	
	
	/**
	 * @param object $object
	 * @return void
	 */
	public static function registerCallObject($object)
	{
		self::$sObjectCallHashs[spl_object_hash($object)] = $object;
	}
	
	/**
	 * @param object $object
	 * @return void
	 */
	public static function unregisterCallObject($object)
	{
		$hash = spl_object_hash($object);
		if (array_key_exists($hash, self::$sObjectCallHashs)) {
			unset(self::$sObjectCallHashs[$hash]);
		}
	}
	
	/**
	 * @param string $hash
	 * @param string $method
	 * @param array $param
	 * @return mixed
	 */
	public static function objectCall($hash, $method, array $param = null)
	{
		$objectHashs = self::$sObjectCallHashs;
		if (array_key_exists($hash, $objectHashs)) {
			return $param === null ? call_user_func(array($objectHashs[$hash], $method)) : 
						call_user_func_array(array($objectHashs[$hash], $method), $param);
		}
		return self::OBJECT_CALL_RET_ERROR;
	}
	
	/**
	 * @param string $class
	 * @param array $param
	 * @param boolean $reset
	 * @return object
	 */
	public static function getSingleInstance($class, array $param = array(), $reset = false)
	{
		$hash = self::crc32FormatHex($class . print_r($param, true));
		
		if (!$reset && array_key_exists($hash, self::$sSingleInstanceMaps) && self::$sSingleInstanceMaps[$hash]) {
			return self::$sSingleInstanceMaps[$hash];
		}
		
		$ref = new ReflectionClass($class);
		$obj = $ref->newInstanceArgs($param);
		self::$sSingleInstanceMaps[$hash] = $obj;
		return $obj;
	}	
	
	/**
	 * @param string $string
	 * @return int
	 */
	public static function isUtf8($string)
	{
		return preg_match('%^(?:
			 [\x09\x0A\x0D\x20-\x7E]            # ASCII
		   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
		   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
		   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
		   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	   )*$%xs', $string);
	}

	public static function pinyin($source, $charset = 'gbk')
	{
		static $datakey = 'a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo'; 
						
		static $dataval = '-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274|-10270|-10262|-10260|-10256|-10254';
		
		static $data = null;
		$source = strtolower($source);
		
		if (!$data) {
			$data = array_combine(explode('|', $datakey), explode('|', $dataval));
			arsort($data); 
			reset($data);		
		}				 	
	
		if ($charset != 'gbk') {
			$temp = ''; 
			if($source < 0x80){
					$temp .= $source;
			}elseif($source < 0x800) { 
					$temp .= chr(0xC0 | $source >> 6); 
					$temp .= chr(0x80 | $source & 0x3F); 
			}elseif($source < 0x10000){ 
					$temp .= chr(0xE0 | $source >> 12); 
					$temp .= chr(0x80 | $source >> 6 & 0x3F); 
					$temp .= chr(0x80 | $source & 0x3F); 
			}elseif($source < 0x200000) { 
					$temp .= chr(0xF0 | $source >> 18); 
					$temp .= chr(0x80 | $source >> 12 & 0x3F); 
					$temp .= chr(0x80 | $source >> 6 & 0x3F); 
					$temp .= chr(0x80 | $source & 0x3F); 
			} 
			$source = iconv('utf-8', 'gbk', $temp);	
			unset($temp);
		}	
		
		$ret = '';	
	
		for($i=0, $j = strlen($source); $i < $j; $i++) { 
			$_P = ord(substr($source, $i, 1)); 
			
			if($_P > 160) { 
				$_P = $_P * 256 + ord(substr($source, ++$i, 1)) - 65536;
			} 

			if($_P > 0 && $_P < 160 ){
				$ret .= chr($_P);
			}elseif($_P < -20319 || $_P > -10247){
				$ret .= '';
			}else{ 
				foreach($data as $k=>$v){ if($v <= $_P) break; } 
				$ret .= $k; 
			}  
		} 
		
		return preg_replace("/[^a-z0-9]*/", '', $ret); 			
	}	
}