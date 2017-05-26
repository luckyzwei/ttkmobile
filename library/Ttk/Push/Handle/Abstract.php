<?php
/**
 * @author lamb
 */
abstract class Ttk_Push_Handle_Abstract extends Ttk_Push_Utils
{
	/**
	 * @var string 推送公共标题
	 */
	protected $mPublicTitle;
	
	/**
	 * @var string
	 */
	protected $mCfg;
	
	public function __construct()
	{
		parent::__construct();
		$cfg = Lamb_Registry::get(PUBLIC_CFG);
		$this->mPublicTitle = $cfg['notify_public_title'];
		$this->mCfg = $cfg['notify_tpl_cfg'][$this->getEventName()];
		
	}
	
	/**
	 * 根据配置模版生成内容
	 * 
	 * @param string $template 模版
	 * @param array $params 参数
	 * 
	 * @return string
	 */
	public function getBodyFromTemplate($template, $params)
	{
		foreach ($params as $index => $val) {
			$template = str_replace('{$' . ($index + 1) . '}', $val, $template);
		}
		return $template;
	}
	
	/**
	 * 推送代理方法
	 */
	protected function sendProxy($uids, $pushData, $body, $options = array())
	{
		$en = $this->getEventName();
		$pushData['summary'] = $body;
		$pushData = array(
			'en' => $en,
			'data' => $pushData
		);		
		$music = '';
		if (isset($options['music'])) {
			$music = $options['music'];
			unset($options['music']);
		}
		$this->iosDevPush($uids, $body, $music);
		return $this->sendByUids($uids, 0, $pushData, $this->mPublicTitle, $options);	
	}
	
	/**
	 * IOS开发版通知推送
	 * 
	 * @param string | array $uids 指定要推送的用户
	 * 
	 */
	public static function iosDevPush($uids, $body, $music = '')
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
		
		if ($music) {
			$req->setExt('{"sound":"' . $music . '"}');
		} else {
			$req->setExt('{"sound":"xy.mp3"}');
		}
		$client->execute($req);		
	}
	
	/**
	 * 针对指定用户发起推送
	 * 
	 * @param string | array $uids 指定要推送的用户
	 * @param string $notifyTitle 通知的标题
	 * @param array $pushData 通知的结构体 = array(
	 * 		'client_data' => array 发给客户端的结构体,
	 * 		'body_param' => array 构造推送的内容参数，用于模版的生成
	 * )
	 * @param array $options 选项
	 * 
	 * @return boolean
	 */
	abstract public function notify($uids, $pusData, $options =array());
	
	/**
	 * 获取该推送的事件名
	 * 
	 * @return string
	 */
	abstract public function getEventName();
}
