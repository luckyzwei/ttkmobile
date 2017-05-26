<?php
class testControllor extends Ttk_Controllor_Abstract2
{	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'test';
	}	
	
	public function testAction()
	{	
		//$model = new Ttk_Cache_Movie2;
		//Ttk_Cache_Movie2::clear(10);
		//Ttk_Cache_Movie2::clear(2);
		//Ttk_Cache_Movie2::clear(55);
		//$r = $model->get('10,2,55,0', 'name,week_num,id,point_all');
		
		$this->d(123123);
	}
	
	
	public function getSourceAction()
	{
		$url = trim($this->mRequest->url);
		
		preg_match("/\.(.*?)\./i", $url, $r);
		
		if (!$r) {
			$this->showResults(0, null, 'ERR');
		}
		$r = ucfirst($r[1]);
		if ($r == '1905') {
			$r = 'M1905';
		}
		
		static $maps = array(
			'Sohu','Pptv','Letv', 'Iqiyi', 'Youku', 
			'Kankan','M1905','Tudou','Qq','Hunantv','Cntv','Baofeng'
		);
		
		if (!in_array($r, $maps)) {
			$this->showResults(0, null, 'URL_ERR');
		}
		
		$r = 'Ttk_Parser_' . $r;
		$parser = new $r;
		
		$play_source = $parser->parse($url);
		$this->d($play_source);
		if (!$play_source) {
			$this->showResults(10, null, 'ERR');
		}
		$this->showResults(1, array('play_source' => $play_source));
	}
}
