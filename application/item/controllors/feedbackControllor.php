<?php
class feedbackControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'feedback';
	}
	
	
	/**
	 * @author jude
	 * @method POST
	 * 添加意见反馈
	 * 
	 * req_data : 
	 * 		msg : string 内容
	 * res_data:
	 * 		s : 
	 *			1-成功
	 *			-1-未登录
	 *			-3-反馈意见长度10到500个字符以内
	 *
	 * 		d : null
	 */
	public function addAction()
	{	
		$uid = $this->isLogin();
	
		$msg = trim($this->mRequest->getPost('msg'));

		if ($msg == '' || Ttk_Utils::strLen($msg) < 10 || Ttk_Utils::strLen($msg) > 500) {
			$this->showResults(-3, null, '反馈意见长度10到500个字符以内');
		}
		
		$smt = $this->getDb('movie')->prepare('exec addSuggest :uid,:msg,:time');
		$smt->bindValue(':uid', $uid, PDO::PARAM_INT);
		$smt->bindValue(':msg', $msg, PDO::PARAM_STR, 500);
		$smt->bindValue(':time', time(), PDO::PARAM_INT);
		if(!$smt->execute()) {
			$this->showResults(0);
		}
		
		$this->showResults(1);
	}
	
}