<?php
class indexControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'index';
	}
	
	/**
	 * @author: fjz
	 * 
	 * res_data:
	 * 	's' =>  0  系统错误
	 * 		    1  成功
	 * 
	 * 	'd' => array();
	 */
	public function indexAction()
	{
		$channel_data = $this->getDb('movie')->query('select id,name,logo from channel where is_lock = 0 order by sort desc')->toArray();
		$this->showResults(1, array('data' => $channel_data));
	}
	
	/**
	 * @author: fjz
	 * 列表页
	 * 	req_data:
	 *		$id int
	 *		$sign string
	 *		$ct int
	 *		
	 * 	res_data:
	 * 	's' =>  0  系统错误
	 * 		    1  成功
	 *			-1 id错误
	 * 			-2 签名错误
	 * 	'd' => array(
			 '0' => array, 
			 '1' => array, 
			......
	 );
	 */
	public function getPlayUrlAction()
	{	
		$id = trim($this->mRequest->id);
		$sign = trim($this->mRequest->sign);
		$ct  = trim($this->mRequest->ct);
		
		if (!Lamb_Utils::isInt($id, true)) {
			$this->showResults(-11, null, 'ID_ERR23454');
		}
		
		if (md5($id . '|' . $ct . '|' . self::SALT) != $sign) {
			//$this->showResults(-2, null, 'SIGN_ERR');
		}
		
		$channel = $this->getDb('movie')->query("select url,is_dynamic from channel where id = {$id}")->toArray();
		if (!$channel) {
			$this->showResults(-12, null, 'ID_ERR234');
		}
		
		$channel = $channel[0];
		if (!$channel['is_dynamic']) {
			$this->showResults(1, array('url' => $channel['url']));
		}
		
		$this->mResponse->redirect($channel['url']);
	}
	
	
}