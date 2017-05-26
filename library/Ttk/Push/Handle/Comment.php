<?php
class Ttk_Push_Handle_Comment extends Ttk_Push_Handle_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	
	/**
	 * 评论推送
	 * 
	 * @param array $pushData = array(
	 * 		'client_data' => array(
	 * 			'commid' => int 评论消息id
	 * 		)
	 * )
	 */
	public function notify($uids, $pushData = null, $options = array())
	{
		//Lamb_Debuger::debug($this->mPublicCfg);
		$body = $this->getBodyFromTemplate($this->mCfg['body'][0], array());
		//$options['android_activity'] = 'com.shendou.xiangyue.qq.QQActivity';
		return $this->sendProxy($uids, $pushData['client_data'], $body, $options);
	}
	
	public function getEventName()
	{
		return 'comment';
	}
}
