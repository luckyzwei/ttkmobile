<?php
class userControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'user';
	}
	
	/**
	 * @author kay
	 * @method get 
	 * 登陆
	 * 
	 * req_data : 
	 * 		id : int 用户id
   	 *		fields : string 默认全部
	 *			支持字段	
	 *			id string
	 *			nickname string
	 *			username string
	 *			avatar string
	 *			status int
	 *			regtime int
	 * res_data:
	 * 		s : -3 账号或密码错误
	 * 		d : 
	 * 			
	 */
	public function getInfoAction()
	{
		$isExpire = 0;
		$uid = $this->isLogin(&$isExpire, false);
		$id = trim($this->mRequest->id);
		$fields = trim($this->mRequest->fields);
		
		if (!Lamb_Utils::isInt($id)) {
			//既没有登录又没有传id
			if (!$uid) {
				$this->showResults(10);
			}
			$id = $uid;
		}
		
		static $allowFields = array(
			array('id' => 1, 'nickname' => 1, 'username' => 1, 'avatar' => 1, 'status' => 1,'regtime' => 1)
		);
		$defaultFields = 'id,nickname,username,avatar,status,regtime';
		
		$fieldObj = new Ttk_Fields_CheckForList($fields, $allowFields, $defaultFields);
		
		if (!($fields = $fieldObj->toArray())) {
			$this->showResults(-3, null, 'fields错误');
		}
		
		$member = new Ttk_Cache_Member;
		$userinfo = $member->get("$id", $fields[0]);
		$this->showResults(1, $userinfo[0]);
	}
	
	/**
	 * @author kay
	 * @method get 
	 * 登陆
	 * 
	 * req_data : 
	 * 		uids : int 用户id
   	 *		fields : string 默认全部
	 *			支持字段	
	 *			id string
	 *			nickname string
	 *			username string
	 *			avatar string
	 *			status int
	 *			regtime int
	 * res_data:
	 * 		s : -3 账号或密码错误
	 * 		d : 
	 *			data array
	 * 			
	 */
	public function batchGetInfoAction()
	{
		$this->isLogin(); 
		
		$uids = trim($this->mRequest->uids);
		$fields = trim($this->mRequest->fields);
		
		if (!preg_match('/^\d+(,\d+){0,19}$/', $uids))	{
			$this->showResults(-3, null, 'uid错误');
		}		
		
		$uids = explode(',', $uids);
		
		if (count($uids) > 20) {
			$this->showResults(-3, null, 'uid错误');
		}
		
		static $allowFields = array(
			array('id' => 1, 'nickname' => 1, 'username' => 1, 'avatar' => 1, 'status' => 1,'regtime' => 1)
		);
		$defaultFields = 'id,nickname,username,avatar,status,regtime';

		$fieldObj = new Ttk_Fields_CheckForList($fields, $allowFields, $defaultFields);		
		if (!($fields = $fieldObj->toArray())) {
			$this->showResults(-4, null, 'fields错误');
		}
		
		$member = new Ttk_Cache_Member;
		$data = $member->get($uids, $fields[0]);

		$this->showResults(1, array('data' => array_values($data)));
	}
	
	/**
	 * @author kay
	 * @method post 
	 * 登陆
	 * 
	 * req_data : 
	 * 		u : string 帐号
   	 *		p : string 密码(传到服务端时需要用des加密)
	 *		device_id : string 客户端连接阿里云服务器返回的设备ID,可不传
	 * res_data:
	 * 		s : -3 账号或密码错误
	 * 		d : 
	 * 			userinfo : json 
	 *				id : int,
	 *				nickname : string,
	 *				username : string,
	 *				avatar : string,
	 *				status : int,
	 *				regtime : int,
	 *				key : string
	 */
	public function loginAction()
	{
		if (!$this->mRequest->isPost()) {
			$this->showResults(0);
		}
		
		$u = trim($this->mRequest->getPost('u'));
		$p = trim($this->mRequest->getPost('p'));
		$device_id = trim($this->mRequest->getPost('device_id'));
		
		if (empty($u) || empty($p)) {
			$this->showResults(-3, null, '用户名或密码错误');
		}
		
		if (strlen($p) < 6) {
			$this->showResults(-3, null, '密码不能小于6位');
		}
		
		$member = new Ttk_Cache_Member;
		$cache = $member->getCacheByUsername($u);
		
		if (!$cache->isCached()) {
			$uid = $member->username2uid($u);
			if (!Lamb_Utils::isInt($uid, true) || !$uid) {
				$this->showResults(-3, null, '用户名或密码错误');
			}
			$userinfo = $member->get($uid, 'id,nickname,username,avatar,status,salt,password,regtime');		
			$userinfo = $userinfo[0];
		} else {
			$userinfo = $cache->read();
			$uid = $userinfo['id'];
		}
		
		if (md5(md5($p) . $userinfo['salt']) != $userinfo['password']) {
			$this->showResults(-3, null, '用户名或密码错误');
		}
		
		$this->getDb('movie')->quickPrepare('exec loginCallback :uid,:device_id,:device_type,:version,:mac,:ip,:time',array(
				':uid' => array($uid, PDO::PARAM_INT),
				':device_id' => array($device_id, PDO::PARAM_STR, 32),
				':device_type' => array($this->mClientDevice, PDO::PARAM_INT),
				':version' => array($this->mClientVersion, PDO::PARAM_STR, 10),
				':mac' => array($this->mClientMac, PDO::PARAM_INT),
				':ip' => array($this->mRequest->getClientIp(), PDO::PARAM_STR, 50),
				':time' => array(time(), PDO::PARAM_INT)
		))->toArray();
		
		$userinfo['key'] = Ttk_Utils::auth_encode($uid, self::SESSION_MECRYPT_KEY, self::SESSION_MECRYPT_EXPIRE);
		unset($userinfo['password'], $userinfo['salt']);
		$this->showResults(1, array('userinfo' => $userinfo));
	}
	
	/**
	 * @author kay
	 * @method get 
	 * 退出登陆
	 * 
	 * req_data : 
	 * 		
	 * res_data:
	 * 		s : 1
	 * 		d : 
	 * 			
	 */
	public function logoutAction()
	{
		$uid = $this->isLogin();
		$this->getDb('movie')->quickPrepare('exec logout(:uid)',array(':uid' => array($uid, PDO::PARAM_INT)));
		$this->showResults(1);
	}
	
	public function clearCacheByIdAction()
	{
		$id = trim($this->mRequest->id);
		if (!Lamb_Utils::isInt($id)) {
			return ;
		}
		Ttk_Cache_Member::clear($id);
	}
	
	/**
	 * @author kay
	 * @method post 
	 * 注册
	 * req_data : 
	 * 		u : string 帐号
   	 *		p : string 密码(传到服务端时需要用des加密)
	 *		device_id : string 客户端连接阿里云服务器返回的设备ID,可不传
	 *		
	 * res_data:
	 * 		s : -3 账户错误 
	 * 			-4 密码错误
	 * 		d : 
	 * 			'key' : string 客户端session_key,'id' : 用户的ID
	 */
	public function regAction()
	{
		if (!$this->mRequest->isPost()) {
			$this->showResults(0);
		}
		
		$u = trim($this->mRequest->getPost('u'));
		$p = trim($this->mRequest->getPost('p'));
		$device_id = trim($this->mRequest->getPost('device_id'));
		$_p = $p;
		if (empty($u)) {
			$this->showResults(-3, null, '用户名不能为空');
		}
		
		if (empty($p)) {
			$this->showResults(-4, null, '密码不能为空');
		}
		
		if (strlen($p) < 6) {
			$this->showResults(-4, null, '密码不能小于6位');
		}
		
		$salt = Ttk_Utils::createSalt();
		$p = md5(md5($p) . $salt);
		$regip = $this->mRequest->getClientIp();
		
		$userApi = new Ttk_UserApi;
		
		$ret = $userApi->addUser(array(
			'username' => $u,
			'nickname' => $u,
			'salt' => $salt,
			'password' => $p,
			'regip' => $regip
		), true);
		
		if ($ret['s'] < 0) {
			if ($ret['s'] == -3) {
				$this->showResults(-3, null, '用户名已存在');
			} else if ($ret['s'] == -4) {
				$this->showResults(-3, null, '用户名错误');
			} else if ($ret['s'] == -5) {
				$this->showResults(-4, null, '密码错误');
			}
		}
		$ret = $ret['d']['uid'];
		
		$this->syncMember(array(
			'uid' => $ret,
			'username' => $u,
			'salt' => $salt,
			'password' => $_p
		));
		
		if (strlen($device_id) == 32) {
			$this->getDb('movie')->quickPrepare('exec updateDevice :uid,:device_id,:device_type,:time', array(
				'uid' => array($ret, PDO::PARAM_INT),
				'device_id' => array($device_id, PDO::PARAM_STR, 32),
				'device_type' => array($this->mClientDevice, PDO::PARAM_INT),
				'time' => array(time(), PDO::PARAM_INT)
			));
		}
		
		$key = Ttk_Utils::auth_encode($ret, self::SESSION_MECRYPT_KEY, self::SESSION_MECRYPT_EXPIRE);
		$this->showResults(1, array('id' => $ret, 'key' => $key, 'regtime' => time(), 'username' => $u));
	}
	
	public function syncMember($data)
	{
		$data['username'] = iconv("UTF-8", "GBK//IGNORE", $data['username']);
		$_reqParams = '';
		$_requestUrl = 'http://member.ttkvod.com/?s=member/sync';
		foreach ($data as $k => $v) {
			$_reqParams .= "$k=" . urlencode($v) . "&";
		}
		
		$ret = Lamb_Http::quickPost($_requestUrl, $_reqParams);
	}
	
	/**
	 * @author kay
	 * @method post 
	 * 修改密码
	 * req_data : 
	 * 		oldPwd : string 旧密码
   	 *		pwd : string 新密码
	 *		
	 * res_data:
	 * 		s : -3 旧密码错误 
	 * 			-4 新旧密码不能相同 
	 * 		d : null
	 */
	public function updatePwdAction()
	{
		$uid = $this->isLogin();
		
		if (!$this->mRequest->isPost()) {
			$this->showResults(0);
		}
		
		$oldPwd = trim($this->mRequest->getPost('oldPwd'));
		$pwd = trim($this->mRequest->getPost('pwd'));
		
		if ($oldPwd == $pwd) {
			$this->showResults(-4, null, '新旧密码不能相同');
		}
		$len = strlen($pwd);
		if ($len < 6 || $len > 18) {
			$this->showResults(-4, null, '旧密码错误');
		}
		
		$member = new Ttk_Cache_Member;
		$userinfo = $member->get("$uid", 'salt,password');
		$userinfo = $userinfo[0];
		if (!count($userinfo)) {
			$this->showResults(0);
		}
		
		if (md5(md5($oldPwd) . $userinfo['salt']) != $userinfo['password']) {
			$this->showResults(-3, null, '旧密码错误');
		}
		
		$pwd = md5(md5($pwd) . $userinfo['salt']);
		
		$userApi = new Ttk_UserApi;
		$ret = $userApi->updateUser(array(
			'password' => $pwd,
			'id' => $uid
		));

		if ($ret) {
			Lamb_Http::quickGet("http://member.ttkvod.com/?s=member/syncPassword/uid/{$uid}/p/{$pwd}");
			Ttk_Cache_Member::clear($uid);
			$this->showResults(1);
		}
		$this->showResults(0);
	}
	
	/**
	 * @author kay
	 * @method post
	 * 修改用户资料
	 * req_data : 
	 * 		fields : string {'要修改的字段名' : '值'}
	 * 			支持字段
	 * 				nickname
	 * 				avatar
	 *		
	 * res_data:
	 * 		s : -3 fields参数非法 
	 * 			-4 昵称错误  
	 * 			-5 头像错误
	 * 		d : null
	 */
	public function updateInfoAction()
	{
		$uid = $this->isLogin();
		
		if (!$this->mRequest->isPost()) {
			$this->showResults(0);
		}
		
		$fields = trim($this->mRequest->getPost('fields'));
		
		try {
			$fields = json_decode(rawurldecode($fields), true);
		} catch (Exception $e) {
			$this->showResults(-3);
		}
		
		if (!$fields) {
			$this->showResults(-3);
		}
		
		if (isset($fields['nickname'])) {
			$fields['nickname'] = trim($fields['nickname']);
			if (empty($fields['nickname'])) {
				$this->showResults(-4, null, '昵称不能为空');	
			}
			if (Ttk_Utils::strLen($fields['nickname']) > 30) {
				$this->showResults(-4, null, '昵称长度不能超过30个字');	
			}
		}
		
		if (isset($fields['avatar']) && !Lamb_Utils::isHttp($fields['avatar'])) {
			$this->showResults(-5, null, '头像错误');	
		}
		
		$fields['id'] = $uid;
				
		$userApi = new Ttk_UserApi;
		$ret = $userApi->updateUser($fields);
	
		if ($ret) {
			if (isset($fields['nickname'])) {
				$this->getDb('movie')->quickPrepare('exec updateNicknameToComment :uid,:nickname', array(
					'uid' => array($uid, PDO::PARAM_INT),
					'nickname' => array($fields['nickname'], PDO::PARAM_STR, 50),
				));
			}
			Ttk_Cache_Member::clear($uid);
			$this->showResults(1);
		}
		$this->showResults(0);
	}
	
	/**
	 * @author kay
	 * @method get
	 * 更新设备ID
	 * req_data : 
	 * 		device_id string 设备id
	 *		
	 * res_data:
	 * 		s : 1
	 * 		d : null
	 */
	public function updateDeviceIdAction()
	{
		$uid = $this->isLogin();
		$device_id = trim($this->mRequest->device_id);
		
		if (strlen($device_id) != 32) {
			$this->showResults(1);
		}
		
		$this->getDb('movie')->quickPrepare('exec updateDevice(:uid,:device_id,:device_type,:time)',array(
				':uid' => array($uid, PDO::PARAM_INT),
				':device_id' => array($device_id, PDO::PARAM_STR, 32),
				':device_type' => array($this->mClientDevice, PDO::PARAM_INT),
				':time' => array(time(), PDO::PARAM_INT)
			));
		
		$this->showResults(1);
	}
	
	/**
	 * 客户端刷新超时的sess_key
	 * req_data:
	 * res_data:
	 * 	's' =>	0 系统错误
	 * 			1  成功
	 * 			-3 旧的sesskey无效
	 *
	 *'d' => array(
	 *  	'sesskey' => 新sesskey
	 *  )
	 *   
	 */
	public function freshSesskeyAction()
	{
		$isExpire = -1;
		$ret = Ttk_Utils::auth_decode($this->mSessionKey, self::SESSION_MECRYPT_KEY, $isExpire);
		
		if ($ret) {
			$this->showResults(1, array('sesskey' => Ttk_Utils::auth_encode($ret, self::SESSION_MECRYPT_KEY, self::SESSION_MECRYPT_EXPIRE)));
		} 
		$this->showResults(-3, null, '旧的sesskey无效');
	}
}