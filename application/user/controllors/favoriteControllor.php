<?php
class favoriteControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'favorite';
	}
	
	/**
	 * @author kay
	 * @method get 
	 * 收藏/取消收藏影片
	 * 
	 * req_data : 
	 * 		mid : int 影片ID
	 *		ac : int 1收藏， 2取消收藏
	 * res_data:
	 * 		s : 1
	 * 		d :
	 */
	public function favoriteAction()
	{
		$uid = $this->isLogin();
		$mid = trim($this->mRequest->mid);
		$ac = trim($this->mRequest->ac);
		
		if (!Lamb_Utils::isInt($mid, true)) {
			$mid = 0;	
		}
		
		if ($ac != 2) {
			$ac = 1;
		}
		
		$this->getDb('movie')->quickPrepare('exec addfavorite :uid,:mid,:ac,:time',array(
				':uid' => array($uid, PDO::PARAM_INT),
				':mid' => array($mid, PDO::PARAM_INT),
				':ac' => array($ac, PDO::PARAM_INT),
				':time' => array(time(), PDO::PARAM_INT)
			));
			
			
		$this->showResults(1);
	}
	
	/**
	 * @author kay
	 * @method get 
	 * 获取收藏的影片
	 * 
	 * req_data : 
	 * 		fileds : string,默认字段 id,favorite_time
	 * 			支持字段 ： 
	 * 				id : int 影片ID,
	 * 				favorite_time int 收藏时间
	 * res_data:
	 * 		s : -3 fileds错误
	 * 		d : 
	 * 			data : array
	 */
	public function listAction()
	{	
		$uid = $this->isLogin();
		$data = $this->getDb('movie')->quickPrepare('exec getfavorite :uid', array(':uid' => array($uid, PDO::PARAM_INT)))->toArray();
		$this->showResults(1, array('data' => $data));
	}

}