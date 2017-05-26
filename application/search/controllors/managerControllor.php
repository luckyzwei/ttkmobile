<?php
class managerControllor extends Ttk_Controllor_Abstract
{	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'manager';
	}	
	
	public function clearCacheAction()
	{	
		$id = trim($this->mRequest->id);
		if (!Lamb_Utils::isInt($id, true)) {
			$this->showResults(1);
		}
		
		Ttk_Cache_Movie::clear($id);
		/*
		Ttk_Utils::flushCDN(
			array(
				"http://item.m.ttkvod.com/?c=index&a=info&id={$id}", 
				"http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}", 
				"http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&order=1", 
				"http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&order=2",
				"http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&order=1&pagesize=50",
				"http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&order=2&pagesize=50",
				"http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&pagesize=50"
			)
		); 
		*/
		
		$this->showResults(1);
	}
	
	public function parseYsdqAction()
	{	
		$url = trim($this->mRequest->url);
		$model = new Ttk_Parser_Ysdq;
		$r = $model->parse_byurl($url);
		//$r = $model->parse($r);
		$this->showResults(1, array('r' => $r));
	}
	
	public function testAction()
	{
		Ttk_Utils::flushCDN(array('http://cfg.m.ttkvod.com/search.txt'));
	}
	
	
}
