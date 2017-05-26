<?php
class itemControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'item';
	}
	
	
	/**
	 * @author jude
	 * @method get 
	 * 影片评分
	 * 
	 * req_data : 
	 * 		msg : string 反馈内容
	 * res_data:
	 * 		s : 
	 *			-1-未登录
	 *			-3-影片不存在
	 *			-4-评分有误
	 *			-5-您已经评过分了
	 *
	 * 		d : null
	 */
	public function pointAction()
	{
		$uid = $this->isLogin();
		
		$mid = trim($this->mRequest->mid);
		$point = trim($this->mRequest->point);
		
		if (!Lamb_Utils::isInt($mid, true)) {
			$this->showResults(-3, null, '影片不存在');
		}
		
		if (!Lamb_Utils::isInt($point, true) || $point < 0 || $point > 10) {
			$this->showResults(-4, null, '评分有误');
		}
		
		$movie = new Ttk_Cache_Movie();
		$info = $movie->get($mid, 'point_all,point_num');
		if (empty($info)){
			$this->showResults(-3, null, '影片不存在');
		}
		
		$pointnum = $info[0]['point_num'];
		$pointall = $info[0]['point_all'];
		$pointall += $point;
		$pointnum ++;
		
		$newpoint = sprintf('%0.1f', $pointall/$pointnum);

		$smt = $this->getDb('movie')->prepare('exec :ret=addPoint :uid,:mid,:point,:pointnum,:pointall,:time');
		$smt->bindValue(':uid', $uid, PDO::PARAM_INT);
		$smt->bindValue(':mid', $mid, PDO::PARAM_INT);
		$smt->bindValue(':point', $newpoint, PDO::PARAM_STR);
		$smt->bindValue(':pointnum', $pointnum, PDO::PARAM_INT);
		$smt->bindValue(':pointall', $pointall, PDO::PARAM_INT);
		$smt->bindValue(':time', time(), PDO::PARAM_INT);
		$smt->bindParam(':ret', $ret, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 2);
		if(!$smt->execute()) {
			$this->showResults(0);
		}
		
		if ($ret == -1) {
			$this->showResults(-5, null, '您已经评过分了');
		}
		
		Ttk_Cache_Movie::clear($mid);
		$this->showResults(1);		
	}
	
}