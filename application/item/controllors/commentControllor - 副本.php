<?php
class commentControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'comment';
	}
	
	/**
	 * @author jude
	 * @method get 
	 * 评论列表
	 * 
	 * req_data : 
	 *		mid : int 影片id
	 * 		page : int 分页页数 默认 1
	 *		pagesize : int 默认 10
	 *
	 * res_data :
	 * 		s : -2-fields错误  
	 * 			
	 * 		d : {
	 *			'data' : [
	 *				{'id' : 1, 'uid' : ..., 'floor_msg' : 
	 *					[
	 *						'id' : 2, 'uid' : ...
	 *					]
	 *				}
	 *			]
	 *		}
	 */
	public function listAction()
	{
		$id = trim($this->mRequest->id);
		$mid = trim($this->mRequest->mid);
		$pagesize = trim($this->mRequest->pagesize);
		$fields = trim($this->mRequest->fields);
		
		if (!Lamb_Utils::isInt($id, true)) {
			$id = 0;
		}
		
		if (!Lamb_Utils::isInt($mid, true)) {
			$this->showResults(-2, null, '影片不存在');
		}
		
		if (!Lamb_Utils::isInt($pagesize,true)) {
			$pagesize = 10;
		}	
		$pagesize = min(max($pagesize, 1), self::MAX_PAGESIZE);	
		
		static $allowFields = array(
			array(
				'id' => 1, 'uid' => 1, 'movieid' => 1, 'msg' => 1, 'time' => 1, 'ip' => 1, 'up' => 1, 'relas' => 1,  'down' => 1, 
				'username' => 1,'touid' => 1,'tousername' => 1,  'floor_msg' => 1
			)
		);
		
		
		$defaultFields = 'id,uid,movieid,msg,time,ip,up,relas,down,username,touid,tousername,floor_msg';
		$fieldObj = new Ttk_Fields_CheckForList($fields, $allowFields, $defaultFields);
		
		$isAppendFloorMsg = false;
		$isAppendFloorMsg = $fieldObj->findAndReplace('floor_msg');
		
		//保证在需要获取盖楼数据前，有floor_msg
		if (!$fieldObj->hasField('floor_msg')) {
			$isAppendFloorMsg = true;
		}
		
		if (!($fields = $fieldObj->toArray())) {
			$this->showResults(-2);
		}
			
		$m_nLoopNum = 0;
		$db = $this->getDb('movie');
		$smt = $db->prepare('exec getMovieComment :mid,:commid,:pagesize,:num');
		$smt->bindValue(':mid', $mid, PDO::PARAM_INT);
		$smt->bindValue(':commid', $id, PDO::PARAM_INT);
		$smt->bindValue(':pagesize', $pagesize, PDO::PARAM_INT);
		$smt->bindParam(':num', $m_nLoopNum, PDO::PARAM_INT|PDO::PARAM_INPUT_OUTPUT, 2);
		if ( !$smt->execute() || !($aData = $smt->fetchAll())){
			$this->showResults(1, array());
		}
		
		$smt->nextRowset();	
		if ($m_nLoopNum <= 0) {
			$this->showResults(1, array());
		}
		
		$smt = null;	
		if ($m_nLoopNum >= $pagesize) {
			foreach($aData as $key => $val) {
				$aData[$key]['floor_msg'] = array();
			}
			$this->showResults(1, $aData);
		}	
		
		$aNewData = array();
		for ($i = 0; $i < $m_nLoopNum; $i++) {
			$aNewData[$i] = $aData[$i];
		}

		$bNewData = array();
		for ($i = $m_nLoopNum, $j = count($aData); $i < $j; $i++) {
			$id = $aData[$i]['id'];
			if (!array_key_exists($id, $aNewData)) {
				$bNewData[$i] = $aData[$i];			
			}
		}

		foreach ($aNewData as $key => $aItem) {
			foreach($bNewData as $k => $bItem) {
				if ($aItem['id'] == $bItem['relas']) {
					$aNewData[$key]['floor_msg'][] = $bItem;
				}
			}
		}
		
		$this->showResults(1, $aNewData);
		
	}
	
	/**
	 * @author jude
	 * @method post 
	 * 发表评论
	 * 
	 * req_data : 
	 * 		
	 *		mid : int 影片id
	 * res_data :
	 * 		s : 
	 *			 1-成功
	 *			-2-评论的影片不存在 
	 *			-3-评论不能为空
	 *			-4-评论长度在2000个字以内
	 * 			
	 * 		d : null
	 */
	public function addAction()
	{
		$uid = 10008;
		$username = 'kkkkkk';
		
		if (!$this->mRequest->isPost()) {
			$this->showResults(0);
		}
		
		$mid = trim($this->mRequest->getPost('mid'));
		$commid = trim($this->mRequest->getPost('commid'));
		$msg = trim($this->mRequest->getPost('msg'));
		
		if (!Lamb_Utils::isInt($mid, true)) {
			$this->showResults(-2, null, '评论的影片不存在');
		}
		
		if (!Lamb_Utils::isInt($commid, true)) {
			$commid = 0;
		}
		
		if ($msg == '') {
			$this->showResults(-3, null, '评论不能为空');
		}
		
		if (Ttk_Utils::strLen($msg) > 2000 ) {
			$this->showResults(-4, null, '评论长度在2000个字以内');
		}
		
		$smt = $this->getDb('movie')->prepare('exec :ret=addComment :uid,:username,:mid,:commid,:msg,:time,:ip');
		$smt->bindValue(':uid', $uid, PDO::PARAM_INT);
		$smt->bindValue(':username', $username, PDO::PARAM_STR, 200);
		$smt->bindValue(':mid', $mid, PDO::PARAM_INT);
		$smt->bindValue(':commid', $commid, PDO::PARAM_INT);
		$smt->bindValue(':msg', $msg, PDO::PARAM_STR, 2000);
		$smt->bindValue(':time', time(), PDO::PARAM_INT);
		$smt->bindValue(':ip', $this->mRequest->getClientIp(), PDO::PARAM_STR, 20);
		$smt->bindParam(':ret', $ret, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 2);
		$smt->execute();
		
		if ($ret == -1) {
			$this->showResults(-2, null, '评论的影片不存在');
		}
		
		$this->showResults(1);
		
	}

}