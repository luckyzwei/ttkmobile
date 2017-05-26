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
	 * 		s : 
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
		
		$db = $this->getDb('movie');
		$smt = $db->prepare('exec getMovieComment :mid,:commid,:pagesize');
		$smt->bindValue(':mid', $mid, PDO::PARAM_INT);
		$smt->bindValue(':commid', $id, PDO::PARAM_INT);
		$smt->bindValue(':pagesize', $pagesize, PDO::PARAM_INT);
		if ( !$smt->execute() || !($aData = $smt->fetchAll())){
			$this->showResults(1, array() );
		}
		
		$smt->nextRowset();	
		$bData = $smt->toArray();
		
		$hotData = array();
		if (!$id) {
			$smt->nextRowset();	
			$cData = $smt->toArray(); // 获取最热评论 3条
			
			if (count($cData)) {
				$smt->nextRowset();	
				$dData = $smt->toArray();				
				
				$hotData = $this->combind($cData, $dData);		
			}
		}

		$smt = null;
		if (!count($bData)) { //没有出现评论盖楼
			$aData = empty($hotData) ?  array('last' => $aData) : array('hot' => $hotData, 'last' => $aData);
			$this->showResults(1, $aData );
		}
		
		$aData = $this->combind($aData, $bData);
		
		$aData = empty($hotData) ? array('last' => $aData) : array('hot' => $hotData, 'last' => $aData); 
		$this->showResults(1, $aData );
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
	 *			-1-未登录
	 *			-3-评论的影片不存在 
	 *			-4-评论不能为空
	 *			-5-评论长度在2000个字以内
	 *			-6-评论盖楼已达上限	
	 * 			
	 * 		d : null
	 */
	public function addAction()
	{
		$uid = $this->isLogin();
		
		if (!$this->mRequest->isPost()) {
			$this->showResults(0);
		}
		
		$mid = trim($this->mRequest->getPost('mid'));
		$commid = trim($this->mRequest->getPost('commid'));
		$msg = trim($this->mRequest->getPost('msg'));
		
		if (!Lamb_Utils::isInt($mid, true)) {
			$this->showResults(-3, null, '评论的影片不存在');
		}
		
		if (!Lamb_Utils::isInt($commid, true)) {
			$commid = 0;
		}
		
		if ($msg == '') {
			$this->showResults(-4, null, '评论不能为空');
		}
		
		if (Ttk_Utils::strLen($msg) > 2000 ) {
			$this->showResults(-5, null, '评论长度在2000个字以内');
		}
		
		if ($this->accessCommentContent($msg)){
			$this->showResults(-7, null, '评论中含有非法词汇');
		}
		
		$member = new Ttk_Cache_Member();
		$data = $member->get("$uid", 'nickname');
		$data = $data[0];
		if (!count($data)) {
			$this->showResults(0);
		}
		
		$nickname = $data['nickname'];
		$smt = $this->getDb('movie')->prepare('exec :ret=addComment :uid,:nickname,:mid,:commid,:msg,:time,:ip');
		$smt->bindValue(':uid', $uid, PDO::PARAM_INT);
		$smt->bindValue(':nickname', $nickname, PDO::PARAM_STR, 200);
		$smt->bindValue(':mid', $mid, PDO::PARAM_INT);
		$smt->bindValue(':commid', $commid, PDO::PARAM_INT);
		$smt->bindValue(':msg', $msg, PDO::PARAM_STR, 2000);
		$smt->bindValue(':time', time(), PDO::PARAM_INT);
		$smt->bindValue(':ip', $this->mRequest->getClientIp(), PDO::PARAM_STR, 20);
		$smt->bindParam(':ret', $ret, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 2);
		
		if (!$smt->execute()) {
			$this->showResults(0);
		}
		
		if ($ret == -1) {
			$this->showResults(-3, null, '评论的影片不存在');
		} else if ($ret == -2) {
			$this->showResults(-6, null, '评论盖楼已达上限');
		}
		
		$res = $smt->toArray(); 
		$touid  = $res[0]['touid']; 
		$commid = $res[0]['commid'];
		
		if ($touid && $uid !=  $touid) {
			$push = new Ttk_Push_Handle_Comment;
			$pushData = array(
				'client_data' => array(
					'commid' => $commid
				)
			);
			
			$push->notify($touid, $pushData);
		}
				
		$this->showResults(1, $res[0]);
		
	}
	
	public function testAction()
	{
		$temp = array('nickname' => 'Jude');	
			echo json_encode($temp);exit;
			
		/*
		$uids = $this->getDb('movie')->quickPrepare('select uid from favorite where mid=:mid', array(
					':mid' => array(85268, PDO::PARAM_INT)
				))->toArray();
		
		if (!empty($uids)) {
			$temp = array();
			foreach ($uids as $val) {
				$temp[] = $val['uid'];
			}
			
			$uids_str = implode(',', $temp);
			$utils = new Ttk_Push_Utils();
			$utils->favorite($uids_str, '您收藏的影片更新了！');
		}	
	
		*/
	
		/*
		$push = new Ttk_Push_Handle_Comment;
		$pushData = array(
			'client_data' => array(
				'commid' => 10
			)
		);
		
		$push->notify(131308, $pushData);
		*/
		$c = TopClient::getInstance();
		/*
		$req = new CloudpushNoticeAndroidRequest;
		$req->setSummary("this is summary");
		$req->setTarget("device");
		$req->setTargetValue("2323439cbd4f4326b32024fa6b611e3f");
		$req->setTitle("this is title");
		$resp = $c->execute($req);
		*/
		$req = new CloudpushNoticeIosRequest;
		$req->setSummary("this is summary");
		$req->setTarget("device");
		$req->setTargetValue("e044f53289cb497ea662a721809a73e6");
		//$req->setEnv("DEV");

		$resp = $c->execute($req);
		
	}
	
	/**
	 * @author jude
	 * @method get 
	 * 评论点赞
	 * 
	 * req_data : 
	 * 		
	 *		id : int 评论id
	 * res_data :
	 * 		s : 
	 *			 1-成功
	 *			-1-未登录
	 *			-3-评论不存在 
	 * 			
	 * 		d : null
	 */
	public function praiseAction()
	{
		$uid = $this->isLogin();
		
		$id = trim($this->mRequest->id);
		
		if (!Lamb_Utils::isInt($id, true)) {
			$this->showResults(-3, null, '评论不存在');
		}
		
		$this->getDb('movie')->quickPrepare('exec addPraise :uid, :id',array(
			':uid' => array($uid, PDO::PARAM_INT),
			':id' => array($id, PDO::PARAM_INT)
		), true);
		
		$this->showResults(1);
	}
	
	
	/**
	 * @author jude
	 * @method get 
	 * 我发送的评论列表
	 * 
	 * req_data : 
	 * 		
	 *		id : int 评论id
	 *		pagesize : int 页数
	 *		
	 * res_data :
	 * 		s : 
	 *			 1-成功
	 *			-1-未登录
	 *			 
	 *		
	 * 			
	 * 		d :  {
	 *			'id' : 
	 *			...
	 *			floor_msg : [
	 *				{
	 *					
	 *				}
	 *				...
	 *			]
	 *		}
	 */
	public function sendListAction()
	{
		$this->core(); 
	}	
	
	/**
	 * @author jude
	 * @method get 
	 * 回复我的评论列表
	 * 
	 * req_data : 
	 *
	 *		id : int 评论id
	 *		pagesize : int 页数
	 *
	 * res_data :
	 * 		s : 
	 *			 1-成功
	 *			-1-未登录
	 *			 
	 * 			
	 * 		d : null
	 */
	public function replyListAction()
	{
		$this->core('getReplyComments'); 
	}
	
	/**
     * 我发送的评论列表 | 回复我的评论列表		
	 */
	public function core($func = 'getSendComments')
	{
		$uid = $this->isLogin();
		
		$id = trim($this->mRequest->id);
		$pagesize = trim($this->mRequest->pagesize);
		
		if (!Lamb_Utils::isInt($id, true)) {
			$id = 0;
		}
		
		if (!Lamb_Utils::isInt($pagesize,true)) {
			$pagesize = 10;
		}	
		$pagesize = min(max($pagesize, 1), self::MAX_PAGESIZE);	
		
		$db = $this->getDb('movie');
		$smt = $db->prepare("exec {$func} :uid,:commid,:pagesize");
		$smt->bindValue(':uid', $uid, PDO::PARAM_INT);
		$smt->bindValue(':commid', $id, PDO::PARAM_INT);
		$smt->bindValue(':pagesize', $pagesize, PDO::PARAM_INT);
		if ( !$smt->execute() || !($aData = $smt->fetchAll())) {
			$this->showResults(1, array('data' => array() ));
		}
		
		$smt->nextRowset();	
		$bData = $smt->toArray();
		
		if (empty($bData)) {
			$this->showResults(1, array('data' => $aData));
		}
		
		$aData = $this->combind($aData, $bData);

		$this->showResults(1, array('data' => $aData));
	}
	
	public function combind(array $aData, array $bData)
	{
		$aNewData = array();
		$id = 0;
		foreach($bData as $key => $val) {
			$id = $val['comm_id'];
			unset($val['comm_id']);
			$aNewData[$id][] =  $val;
		}
		
		for ($i=0, $j = count($aData); $i < $j; $i++) {
			$id = $aData[$i]['commid'];
			if (array_key_exists($id, $aNewData)) {
				$aData[$i]['floor_msg'] = $aNewData[$id];		
			} else {
				$aData[$i]['floor_msg'] = array();
			}
		}

		return $aData;
	}

}