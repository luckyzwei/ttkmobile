<?php
/**
 * @auther kay
 * 用于调用远程userAPI 
 */
class Ttk_UserApi
{
	/*
	 *
	 */
	protected $mAppId;
	
	/*
	 *
	 */
	protected $mAppKey;
	
	public function __construct()
	{
		//$this->mAppId = 2630418;
		//$this->mAppKey = 'a6e8eb2fbb97d8a679ad2de811634ef7';
		
		$this->mAppId = 23254070;
		$this->mAppKey = '9d067bc0c49b5897a69ad042bd431a1e';
	}
	
	/**
	 * @param uids
	 * @return array
	 * 根据用户id批量获取用户资料
	 */
	public function getInfoByUIds($uids, $fields = 'id', $isReturnCode = false)
	{
		$data = $this->execute('user', 'getByIds', array('uids' => $uids, 'fields' => $fields));
		
		if ($isReturnCode) {
			return $data;
		}
		
		if ($data['s'] == 1) {
			return $data['d'];
		}
		return null;
	}
	
	/**
	 * @param uids
	 * @return array
	 * 根据用户名获取用户资料
	 */
	public function getInfoByUsername($username, $fields = 'id', $isReturnCode = false)
	{
		$data = $this->execute('user', 'getByUsername', array('username' => $username, 'fields' => $fields));
		
		if ($isReturnCode) {
			return $data;
		}
		
		if ($data['s'] == 1) {
			return $data['d'];
		}
		return null;
	}
	
	
	/**
	 * @param $data array
	 *  		{
	 * 				nickname : string
	 * 				username : string
	 * 				salt : string
	 * 				password : string
	 * 				email : string
	 * 				avatar : string
	 * 				regip : string
	 *			}
	 * @return array
	 * 添加用户
	 */
	public function addUser(array $data, $isReturnCode = false)
	{
		$_data = array(
			'nickname' => '',
			'username' => '',
	  		'salt' => '',
	  		'password' => '',
	  		'email' => '',
	  		'avatar' => '',
	  		'regip' => ''
		);
		
		Lamb_Utils::setOptions($_data, $data);
		$ret = $this->execute('user', 'add', $_data, 'POST');
		
		if ($isReturnCode) {
			return $ret;
		}
		
		if ($ret['s'] == 1) {
			return $ret['d'];
		}
		return $ret;
	}
	
	/**
	 * @param $data array
	 *  		{
	 * 				nickname : string
	 * 				username : string
	 * 				salt : string
	 * 				password : string
	 * 				email : string
	 * 				avatar : string
	 * 				regip : string
	 *			}
	 * @return array
	 * 添加用户
	 */
	public function updateUser(array $data)
	{
		$ret = $this->execute('user', 'update', array('fields' => json_encode($data)), 'POST');
		if ($ret['s'] == 1) {
			return 1;
		}
		return 0;
	}
	
	/**
	 * @param string
	 * @return array
	 */
	public function execute($c, $a, array $params, $method = 'GET')
	{
		$_reqParams = array(
			'app_id' => $this->mAppId,
			'ct' => time()
		);
		
		$requestUrl = $this->getUrl($a) . '&';
		$sign = $c . '|' . $a . '|';
		
		ksort($params);
		foreach ($params as $k => $v) {
			$sign .= $k . '=' . $v . '|';
		}
		//Lamb_Debuger::debug($sign. $this->mAppKey);
		$sign = md5($sign . $this->mAppKey);
		$_reqParams['sign'] = $sign;
		
		//拼装系统参数
		foreach ($_reqParams as $k => $v) {
			$requestUrl .= "$k=" . urlencode($v) . "&";
		}
		if ($method == 'GET') {
			//拼接业务参数
			foreach ($params as $k => $v) {
				$requestUrl .= "$k=" . urlencode($v) . "&";
			}
			//Lamb_Debuger::debug($requestUrl);
			$ret = Lamb_Http::quickGet($requestUrl);
		} else {
			$_reqParams = '';
			foreach ($params as $k => $v) {
				$_reqParams .= "$k=" . urlencode($v) . "&";
			}
			//Lamb_Debuger::debug($requestUrl);
			$ret = Lamb_Http::quickPost($requestUrl, $_reqParams);
		}
		
		try {
			$ret = json_decode($ret, true);
		} catch (Exception $e) {
			$ret = array('s' => 0);
		}
		
		return $ret;
	}	
	
	public function getUrl($ac)
	{
		return 'http://121.40.218.51:8080/?c=user&a=' . $ac;
	}
}
