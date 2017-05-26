<?php
class Ttk_Cache_Member
{
	/**
	 * 公用的配置
	 */
	protected $mPublicCfg;
	
	/**
	 * @var array
	 * 要缓存的列
	 */
	protected static $sCachedColumns = array(
		'id' => 1, 'nickname' => 1, 'username' => 1, 'avatar' => 1, 'status' => 1,
		'salt' => 1, 'password' => 1, 'email' => 1, 'regtime' => 1, 'regip' => 1, 'level' => 1
	);
	
	public function __construct()
	{
		$this->mPublicCfg = Lamb_Registry::get(PUBLIC_CFG);
	}
	
	/**
	 * 获取多个用户的数据
	 * 
	 * @param string | array $uids 多个用户的uid，如果是字符串，以英文逗号隔开
	 * @param string | array $fields 指定返回的列，如果是字符串，以英文逗号隔开
	 * 
	 * @return array(
	 * 	用户1的信息，array，如果没有则为空
	 * 	用户2的信息，array
	 * 	....
	 * )
	 * 
	 * 性能分析，假如$uids的个数为m，$fields的个数为n个
	 * 常量：可缓存的字段数目i，不可缓存的字段数目j，附加表的数目k
	 * 	1.对于只获取可以缓存的列：
	 * 		a.最少的循环次数为全部缓存了，m*n
	 * 		b.最多的循环次数为全部都没缓存，m + m*i*n
	 * 	2.如果要获取可缓存的列：
	 * 		a.当可缓存的字段全部缓存了 时候，m*n+2+i+j+m*2
	 * 		b.当可缓存字段都没缓存：m+2+i+j+m*(n+2)
	 */
	public function get($uids, $fields, $isNotExistsFillNull = true)
	{
		if (is_string($fields)) {
			$fields = explode(',', $fields);
		}
		
		if (is_string($uids)) {
			$uids = explode(',', $uids);
		}
		
		$result = array();
		
		//未缓存用户
		$noCacheUsers = array();	
		foreach ($uids as $index => $uid) {
			$cache = $this->getCache($uid);

			if ($cache->isCached()) {
				$data = $cache->read();
				
				//只返回指定列
				$newdata = array();				
				foreach ($fields as $_fields) {
					if (array_key_exists($_fields, $data)) {
						$newdata[$_fields] = $data[$_fields];
					}	
				}
				$result[$index] = $newdata;
			} else {
				$noCacheUsers[$index] = $uid;
			}
		}
		
		$allFields = implode(',', array_keys(self::$sCachedColumns));		
		
		if (count($noCacheUsers)) {
			$noCacheUids = implode(',', array_values($noCacheUsers));
			$userApi = new Ttk_UserApi;
			$noCacheData = $userApi->getInfoByUIds($noCacheUids,$allFields);
			$noCacheData = $noCacheData['data'];
			
			foreach ($noCacheUsers as $index => $uid) {
				$isFind = false;
				foreach ($noCacheData as $i => $_data) {
					if ($_data['id'] == $uid) {
						$isFind = true;
						$cache = $this->getCache($uid);
						$cache->write($noCacheData[$i]);
						$this->writeUsernameKeyCache($noCacheData[$i]);
						
						//只返回指定列						
						$newdata = array();
						foreach ($fields as $_fields) {
							$newdata[$_fields] = $_data[$_fields];	
						}
						$result[$index] = $newdata;							
						unset($noCacheData[$i]);
						break;
					}
				}
				
				if (!$isFind && $isNotExistsFillNull) {
					$result[$index] = null;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 通过用户名获得用户的uid
	 * @param int $username 手机号
	 * @return int uid | 0
	 */
	public function username2uid($username)
	{
		$cache = $this->getCacheByUsername($username);
		
		if ($cache->isCached()) {
			$data = $cache->read();
			return $data['id'];
		}
		
		$userApi = new Ttk_UserApi;
		$result = $userApi->getInfoByUsername($username);
		
		if (!count($result)) {
			return 0;
		}
		
		//Lamb_Debuger::debug($result);
		return $result['id'];
	}

	/**
	 * 获取存放用户的缓存
	 */
	public function getCache($uid) 
	{
		return Ttk_Cache_Factory::getCache()->setIdentity("TTK_USER_{$uid}")
					->setCacheTime($this->mPublicCfg['user_cache_expire']);	
	}
	
	/**
	 * 根据手机号拿到用户的缓存
	 */
	public function getCacheByUsername($username)
	{
		return Ttk_Cache_Factory::getCache()->setIdentity("TTK_USER_{$username}")
					->setCacheTime($this->mPublicCfg['user_cache_expire']);	
	}
	
	/**
	 * 写入手机号为键的用户信息缓存
	 */
	protected function writeUsernameKeyCache($data)
	{
		$this->getCacheByUsername($data['username'])->write($data);
	}
	
	
	/**
	 * 清除用户缓存
	 */
	public static function clear($uid)
	{
		$obj = new self;
		$cache = $obj->getCache($uid);
		
		if ($cache->isCached()) {
			$data = $cache->read();
			$obj->getCacheByUsername($data['username'])->flush();
			$cache->flush();
		}
	}	
}