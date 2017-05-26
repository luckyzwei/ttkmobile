<?php
abstract class Ttk_Controllor_Abstract extends Lamb_Controllor_Abstract
{
	/**
	 * 加密解密session_key的密钥
	 */
	const SESSION_MECRYPT_KEY = 'e3mn0a6ef18ae59bi';
	
	/**
	 * 
	 */
	const SALT = 'e2fh5a9ej18a2dfbi';
	
	/**
	 * 密钥超时时间
	 */
	const SESSION_MECRYPT_EXPIRE = 86400;
	
	/**
	 * 加密解密推送信息的密钥
	 */
	const NOTICE_MECRYPT_KEY = '30fa1e=bc298efcbd39';
	
	/**
	 * 密钥超时时间
	 */
	const NOTICE_MECRYPT_EXPIRE = 300;
	
	/**
	 * 用于编辑检测字段合法性的正则表达式
	 */
	const CHK_EDIT_FIELD_PREG_BOOLEAN = '/^0|1$/is';
	
	/**
	 * 验证字段不能为空
	 */
	const CHK_EDIT_FIELD_PREG_STR_NOT_EMPTY = '/[\s\S]+/is';
	
	/**
	 * 验证字段是正整数
	 */
	const CHK_EDIT_FIELD_PREG_INT_POS = '/^\d+$/is';
	
	/**
	 * 验证字段是否为正数字
	 */
	const CHK_EDIT_FIELD_PREG_NUMBER_POS = '/^((\d+\.\d+)|(\d+))$/s';
	
	/**
	 * 每页获取最大的数据数
	 */
	const MAX_PAGESIZE = 200;
		
	/**
	 * @var array
	 * 公用配置
	 */	
	protected $mPublicCfg;
	
	/**
	 * @var array
	 * 当前application的配置
	 */
	protected $mSiteCfg;
	
	/**
	 * @var int
	 * 设备类型 1-安卓 2-IOS
	 */
	protected $mClientDevice;
	
	/**
	 * @var string
	 * 客户端的版本
	 */
	protected $mClientVersion;
	
	/**
	 * @var int
	 * crc32加密后的mac
	 */
	protected $mClientMac = 0;
	
	/**
	 * @var string
	 */
	protected $mSessionKey;
		
	public function __construct()
	{
		parent::__construct();
		$this->mPublicCfg = Lamb_Registry::get(PUBLIC_CFG);
		$this->mSiteCfg = Lamb_Registry::get(CONFIG);

		if ($this->mRequest->debug) {		
			$this->mClientDevice = 1;
			$this->mClientVersion = '2.0';
			
			if ($this->mRequest->_dv) {
				$this->mClientDevice = $this->mRequest->_dv;
			}	
			
			if ($this->mRequest->_cv) {
				$this->mClientVersion = $this->mRequest->_cv;
			}
			
			$this->mClientMac = $this->mRequest->_cm;
			
			$this->mSessionKey = Ttk_Utils::auth_encode($this->mRequest->_sk, self::SESSION_MECRYPT_KEY, 1800);
		} else {		
			$userAgent = trim($this->mRequest->getHeader('USER_PARAM'));
			if ($userAgent) {
				$userAgent = explode(',', $userAgent);
				if (count($userAgent) == 4) {
					$this->mClientDevice = $userAgent[0] == '2' ? 2 : 1;
					$this->mClientVersion = $userAgent[1];
					
					if (Lamb_Utils::isInt($userAgent[2])) {
						$this->mClientMac = $userAgent[2];
					}
					$this->mSessionKey = $userAgent[3];
				} else {
					$this->debug('user-agent非法 ' . print_r($userAgent, true));
				}
			} else {
				$this->debug('err');
			}
		}
	}
	
	/**
	 * 判断用户是否登录
	 * @param &boolean $isExpire 当返回值为false可以通过
	 * 			$isExpire的值来判断，是因为sesskey过期，而导致的还是因为
	 * 			sesskey非法而导致。如果$isExpire=1则表示为过期导致的
	 * @param 当没有登录，或超时时，是否直接输出错误码
	 * 
	 * @return int 如果没有登录则返回0，登录则返回>0
	 */
	public function isLogin(&$isExpire = 0, $isExitWhenError = true)
	{
		if ($this->mSessionKey) {
			if ($this->mSessionKey == 'abcd') {
				if ($isExitWhenError) {
					$this->showResults(-1);
				}
				return FALSE;
			}
			
			$uid = Ttk_Utils::auth_decode($this->mSessionKey, self::SESSION_MECRYPT_KEY, $isExpire);
			
			if (Lamb_Utils::isInt($uid)) {
				return $uid;
			}	
		}
		
		if ($isExitWhenError) {
			$this->showResults($isExpire ? -2 : -1);			
		}

		unset($isExpire);
		return false;
	}
	
	/**
	 * 获取数据库对象
	 */
	public function getDb($type) 
	{
		return Ttk_Db::get($type);
	}
	
	/**
	 * 带错误信息的输出
	 *
	 * @param int $code 错误码
	 * @param array $data 输出的内容
	 * @param string $errorString 错误信息，如果为空，当$code=0,-1,-2则会输出固定的错误信息，如果不为空，则会先从配置文件error_strings找出对应的映射，
	 * 如果找不到映射，则直接将该值输出
	 */
	public function showResults($code, array $data = null, $errorString = '')
	{
		static $fixedErrorStr = array(
			'0' => '服务器繁忙，请稍后再试',
			'-1' => '您还没有登录',
			'-2' => '登录过期，请重新登录'
		);
		
		$ret = array('s' => $code);
		
		if ($data) {
			$ret['d'] = $data;
		}
		
		if ($errorString && isset($this->mSiteCfg['error_strings']) && isset($this->mSiteCfg['error_strings'][$errorString])) {
			$errorString = $this->mSiteCfg['error_strings'][$errorString];
		}
		
		if (!$errorString && isset($fixedErrorStr[$code])) {
			$errorString = $fixedErrorStr[$code];
		}
		
		$ret['err_str'] = $errorString;
		
		$ret = json_encode($ret);
		$this->mResponse->eecho($ret);	
	}
	
	/**
	 * 验证字段是否为合法的字段
	 * 
	 * @param array & $allowFields合法的字段 = array(
	 * 		'字段名' => 1 或者字符串
	 * )
	 * @param string $fields 多个字段以逗号隔开
	 * @param string $default 如果$fields为空则会返回默认值
	 * @return string 如果字符串返回为空则表示$fields字段存在不支持的字段
	 */
	public function checkFields(&$allowFields, $fields, $default = '')
	{
		if (is_array($default)) {
			$newdefault = array();
			foreach ($default as $field) {
				if (!isset($allowFields[$field])) {
					unset($allowFields);
					return '';
				}
				
				if ($allowFields[$field] == 1) {
					$newdefault[] = $field;
				} else {
					$newdefault[] = $allowFields[$field];
				}
			}
			$default = implode(',', $newdefault);
		}
		
		if (!$fields) {
			return $default;
		}
		
		$isAppend = false;
		if ('+' == substr($fields, 0, 1)) {
			$isAppend = true;
			$fields = substr($fields, 1);
		}
		$fields = explode(',', $fields);
		$ret = array();
		
		foreach ($fields as $field) {
			if (!isset($allowFields[$field])) {
				unset($allowFields);
				return '';
			}
			
			if ($allowFields[$field] == 1) {
				$newField = $field;
			} else {
				$newField = $allowFields[$field];
			}
			
			if ($isAppend && strpos($default, $newField) !== false){
				continue;
			}
			$ret[] = $newField;
		}
		
		if (!count($ret)) {
			return $default;
		}
		
		unset($allowFields);
		$ret = implode(',', $ret);
		if ($isAppend) {
			$ret = "{$default},{$ret}";
		}
		return $ret;
	}
	
	/**
	 * 从字符串中获取表 a 和 b 对应的表属性
	 * @param string $fields 多个字段以逗号隔开
	 * @param array $arr_fields 从$fields中取出$arr_fields对应的字段
	 * return string
	 */
	public function checkTableFields($fields, array $allowFields)
	{
		$fields = explode(',', $fields);
		
		if (empty($fields)) {
			return '';
		}
		
		$ret = array();
		foreach($fields as $field) {
			if (isset($allowFields[$field])) {
				$ret[] = $field;	
			}	
		}
		return implode(',', $ret);
	}
	
	/**
	 * 通用检测输入的数据，是否允许被修改
	 * 
	 * @param array & $inputData 客户端输入的数据
	 * @param array $fieldsRule 字段规则，其结构 = array(
	 * 		'字段的名字' => array(
	 * 			'exec' => mixed,根据type的值不同，含义也不同
	 * 			'type' => int 类型，1表示exec字段为函数，2表示exec为正则表达式
	 * 							当type=1时，
	 * 								还会有以下几个字段
	 * 								param键，代表传入到exec的参数，可以允许为空
	 * 									如果为空的话，会自动代入$inputData，和$val作为exec的参数
	 * 									占位符，:inptdata:  :val:
	 * 									这2个占位符，代表$inputData和$val的位置，可选
	 * 							对于exec的返回值，如果检测成功则返回1，失败则返回<1
	 * 									<1，如果为0，则输出的错误码由系统自动累计得出，
	 * 										如果<0，则代表错误码，系统将会将其值输出
	 * 			'code' => int 错误码，如果为空，则会接着上一次错误码累减
	 * 								
	 * 		),
	 * 		......
	 * )
	 * @param int $errorCode 起始的错误码，主要用于自动累计错误码
	 */
	public function commCheckCanEditFields(&$inputData, $fieldsRule, $errorCode = -3)
	{
		$inputData = (object)$inputData;
		
		if ($errorCode != 0) {
			foreach ($fieldsRule as $rule) {
				if (!isset($rule['code'])) {
					$rule['code'] = $errorCode;
				} else {
					$errorCode = $rule['code'];
				}
				
				$errorCode --;
			}
		}
		
		foreach ($inputData as $filedname => $val) {
			if (!array_key_exists($filedname, $fieldsRule)) {
				$this->showResults(0);
			}
			
			$rule = $fieldsRule[$filedname];
			
			if (null === $rule || !$rule['exec']) {
				continue;
			}
			
			if ($rule['type'] == 2 && !preg_match($rule['exec'], $val)) {
				$this->showResults($rule['code']);
			}
			
			if ($rule['type'] == 1 && is_callable($rule['exec'])) {
				if (!isset($rule['param']) || !$rule['param']) {
					$rule['param'] = array($inputData, $val);
				} else {
					foreach ($rule['param'] as $pk => $pv) {
						if ($pv == ':inptdata:') {
							$rule['param'][$pk] = $inputData;
						} else if ($pv == ':val:') {
							$rule['param'][$pk] = $val;
						}
					}
				}
				
				if (($code = call_user_func_array($rule['exec'], $rule['param'])) < 1) {
					$this->showResults($code == 0 ? $rule['code'] : $code);
				}
			}			
		}
		
		$inputData = (array)$inputData;
		unset($inputData);
	}		
	
	
	/**
	 * 替换数组的键(处理touserinfo的键)
	 * @param $pending array 需要处理的数组
	 * @param $standard array 键的标准
	 */
	public function convertArrayKey($pending, $standard)
	{
		foreach($pending as $i => $item) {
			if (!$item) {
				continue ;
			}
			foreach($item as $k => $v) {
				if (in_array($k, $standard)) {
					$key = array_search($k, $standard);
					$pending[$i][$key] = $pending[$i][$k];
					unset($pending[$i][$k]);
				}
			}
		}
		return $pending;
	}
	
	/**
	 * (把用户传过来的列替换成数据库支持的列)
	 * @param $realields array 键的标准
	 * @param $fields array 需要处理的数组
	 */
	public function getRealFields($realields, $fields)
	{
		$fields = explode(',', $fields);
		$ret = array();
		
		foreach($fields as $field) {
			if (isset($realields[$field])) {
				array_push($ret, $realields[$field]);
			}
		}
		return implode(',', $ret);
	}
	
	/**
	 * 根据用户传过来的列判断是否需要获取userinfo 和 touserinfo
	 * @param $fields array 键的标准
	 * @param $checkStr array 需要处理的数组
	 */
	public function isHas($fields, $checkStr)
	{
		$checkStr = explode(',', $checkStr);
		foreach($checkStr as $s) {
			if (isset($fields[$s])) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 获取模块的地址
	 * 
	 * @param string $modelname 模块的名字
	 * @param array url get的参数
	 * @return string
	 */
	public function getModelUrlByName($modelname, $param = array())
	{
		$url = '';
		if (!isset($this->mPublicCfg['url_maps'][$modelname])) {
			return $url;
		}
		
		$urls = $this->mPublicCfg['url_maps'][$modelname];
		$url = $urls[rand(0, 10000) % count($urls)];
		
		if (count($param)) {
			$result = array();
			foreach ($param as $key => $val) {
				$result[] = rawurlencode($key) . '=' . rawurlencode($val);
			}
			$url .= '?' . implode('&', $result);
		}
		
		return $url;
	}	
	
	
	public function accessCommentContent($content)
	{
		$bRet = false;
		$content = preg_replace('/[\s\r\n]*/is', '', strtolower($content));
		$aContent = explode(',', $this->mPublicCfg['forbin_words']);
		foreach ($aContent as $item) 
		{
			if ($item && strpos($content, $item) !== false ) {
				$bRet = true;
				break;
			}
		}
	
		return $bRet;
	}
	
	/**
	 * 调试方法
	 */
	public function debug($val)
	{
		$this->mResponse->eecho("{$val}");
	}
	
	public function d($val)
	{
		Lamb_Debuger::debug($val);
	}
	
	
}