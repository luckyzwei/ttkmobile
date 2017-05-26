<?php
class Ttk_Push_Utils
{
	public function __construct()
	{
		
	}
	
	/**
	 * @author jude
	 * 
	 *  我的收藏，更新推送通知
	 *
	 */
	public function favorite($uids, $body)
	{	
		$deviceids = Ttk_Db::get('movie')->quickPrepare('exec getDevicesIdByUid :uids,:is_online', array(
			':uids' => array($uids, PDO::PARAM_STR),
			':is_online' => array(1, PDO::PARAM_INT)
		))->toArray();
		
		if (!count($deviceids)) {
			return false;
		}
		$devids = array();
		
		foreach ($deviceids as $item) {
			$devids[] = $item['device_id'];
		}
		$devids = implode(',', $devids);
		
		$client = TopClient::getInstance();
		$req = new CloudpushNoticeIosRequest;
		$req->setSummary($body);
		$req->setTarget('device');
		$req->setTargetValue($devids);
		$req->setEnv('PRODUCT');
	
		$client->execute($req);	
		
		return $this->sendByUids($uids, 1, null, '天天看');
		
	}

	/**
	 * @author lamb
	 * 
	 * 推送核心，针对指定用户推送 包括消息，通知
	 * @param array | string $uids，要推送的用户id，如果是字符串则以逗号隔开
	 * ...
	 * 其他的参数参考core方法
	 * 
	 * @return boolean
	 */
	public function sendByUids($uids, $type = 0, $pushData = null, $notifyTitle = '', $options = array())
	{
		if (is_array($uids)) {
			$uids = implode(',', $uids);
		}
		
		$deviceids = Ttk_Db::get('movie')->quickPrepare('exec getDevicesIdByUid :uids,:is_online', array(
			':uids' => array($uids, PDO::PARAM_STR),
			':is_online' => array(1, PDO::PARAM_INT)
		))->toArray();
	
		if (!count($deviceids)) {
			return false;
		}
		$devids = array();
		
		foreach ($deviceids as $item) {
			$devids[] = $item['device_id'];
		}
		$devids = implode(',', $devids);
		
		return $this->core('device', $devids, $type, $pushData, $notifyTitle, $options);
	}
	
	/**
	 * @author lamb
	 * 
	 * 推送消息+通知给指定用户
	 * 
	 * @param string | array $uids 要推送的指定用户
	 * @param string $eventname 事件名
	 * @param array $pushData 事件结构
	 * @param string $notifyTitle 通知标题
	 * @param array $options 同上
	 * 
	 * @return boolean
	 */
	public function sendPushByUids($uids, $eventname, $pusData, $notifyTitle, $options = array())
	{
		$data = array(
			'en' => $eventname,
			'data' => $pushData
		);
				
		return $this->sendByUids($uids, 0, $data, $notifyTitle, $options);
	}
	
	/**
	 * @author lamb
	 * 
	 * 推送给所有用户 包括消息，通知
	 * 参数参考core方法
	 * 
	 * @return boolean
	 */
	public function sendAll($type = 0, $pushData = null, $notifyTitle = '', $options = array())
	{
		return $this->core('all', 'all', $type, $pushData, $notifyTitle, $options);
	}
	
	
	/**
	 * @author lamb
	 * 
	 * 推送消息给全部用户
	 * 
	 * @param string $eventname 事件名
	 * @param array $pushData 事件结构
	 * @param array $options 同上 
	 *  
	 * @return boolean
	 */
	public function sendAllPush($eventname, $pushData, $options = array())
	{
		$data = array(
			'en' => $eventname,
			'data' => $pushData
		);
		
		return $this->sendAll(0, $data, '', $options);
	}
	
	/**
	 * @author lamb
	 * 
	 * 推送通知给全部用户
	 * 
	 * @param string $notifyTitle 通知标题
	 * @param array $options 同上
	 * 
	 * @return boolean
	 */
	public function sendAllNotify($notifyTitle, $options = array())
	{
		return $this->sendAll(1, null, $notifyTitle, $options);
	}
	
	/**
	 * @author lamb
	 * 推送核心
	 * 
	 * @param string $target 推送目标 根据设备，也可以全局推送
	 * @param string $targetval 推送值
	 * @param int $type 类型 0-消息类型 1-通知类型
	 * @param array $pushData 消息类型的数据结构，该结构最终会转化成字符串，通过body发送
	 * @param string $notifyTitle 通知标题
	 * @param array $options 选项 = array(
	 * 		'store_offline' => 是否保存离线消息，默认为1
	 * 		'remind' => 是否断线提醒，默认为0，只针对IOS设备
	 * 		其他的选项根据文档可以自行增加
	 * )
	 * 
	 * @return boolean 
	 */
	public function core($target, $targetval, $type = 0, $pushData = null, $notifyTitle = '', $options = array())
	{
		$defaultOptions = array(
			'store_offline' => "true",
			'timeout' => '72',
			'remind' => 'false'
		);
		Lamb_Utils::setOptions($defaultOptions, $options);
				
		$c = TopClient::getInstance();
		$req = new CloudpushPushRequest();
		$req->setTarget($target);
		$req->setTargetValue($targetval);
		$req->setType($type);
		$req->setDeviceType(3);
		$req->setAndroidOpenType("2");
		
		if (is_array($pushData)) {
			$req->setBody(json_encode($pushData));
		} else {
			$req->setBody($pushData);
		}

		if ($notifyTitle) {
			$req->setTitle($notifyTitle);
		}
		
		foreach ($defaultOptions as $key => $val) {
			$funcname = 'set';
			foreach (explode('_', $key) as $item) {
				$funcname .= ucfirst($item);
			}
			
			call_user_func(array($req, $funcname), $val);
		}
		
		$resp = $c->execute($req);
		
		if (isset($resp->is_success) && $resp->is_success) {
			return true;
		}
		
		return false;		
	}	
}
