<?php
class taskControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'task';
	}
	
	
	public function indexAction()
	{
		$ret = $this->getDb('movie')->query('exec sevendayNotice')->toArray();
		
		if (count($ret) > 0) {
			$uids = array();
			foreach($ret as $val) {
				$uids[] = $val['uid'];
			}
		
			$uids_str = implode(',' , $uids);
			$utils = new Ttk_Push_Utils();
			$utils->favorite($uids_str, '小天们，好久没来光顾了，为您精心准备了好多影片呢，赶紧来看看吧！');
		}
		
		$this->mResponse->eecho("<script>window.open('','_self');window.opener=null;window.close();</script>");
	}
	
	
	
	
}