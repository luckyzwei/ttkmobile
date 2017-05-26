<?php
class Ttk_Collect_Soku_Abstract
{
	/**
	 * @var array
	 */
	protected static $sTypeKeyMap = array(
				1 => 'movie',
				2 => 'tv',
				3 => 'comic',
				4 => 'show'
			);
	
	protected static $sChhanelSites = array(
		1 => 'site6', 
		2 => 'site31', 
		3 => 'site17', 
		4 => 'site19', 
		5 => 'site14', 
		7 => 'site24', 
		8 => 'site28', 
		11 => 'site15', 
		12 => 'site1', 
		13 => 'site27', 
		14 => 'site8', 
		16 => 'site130',
		17 => 'site134'
	);
	/*
	protected static $sSourceKeyMap = array(
		'site6' => 'sohu', 
		'site31' => 'pptv', 
		'site17' => 'letv', 
		'site19' => 'qiyi', 
		'site14' => 'youku', 
		'site24' => 'imgo', 
		'site28' => 'kankan', 
		'site15' => 'cntv', 
		'site1'  => 'tudou', 
		'site27' => 'qq', 
		'site8'  => 'ifeng', 
		'site130' => 'fengxing',
		'site134' => 'bilibili'
	
	);
	*/
			
	protected static $defaultMedia = array(
		'搜狐' => 1, '芒果TV' => 7, 'PPTV' => 2, '风行' => 16,  '乐视' => 3, 'bilibili' => 17, '爱奇艺' => 4, '优酷' => 5, 'CNTV' => 11,  '土豆' => 12, '响巢' => 8,'凤凰' => 14, '腾讯' => 13, 'yinshidaquan' => 15
	);
			
	/**
	 * @var int
	 */
	protected $mTypeid;
	
	public function __construct()
	{
	
	}
	
	/**
	 * @param int $typeid
	 * @return Tmovie_Collect_Le123_Abstract
	 */
	public function setTypeid($typeid)
	{
		if ( (Lamb_Utils::isInt($typeid, true) && array_key_exists($typeid, self::$sTypeKeyMap)) ||
				($typeid = array_search($typeid, self::$sTypeKeyMap)) !== false ) {
			$this->mTypeid = (int)$typeid;
		}
		return $this;
	}
			
	/**
	 * @return string
	 * @throws Lamb_Exception
	 */
	protected function getTypeKey()
	{
		if (!isset($this->mTypeid) || !isset(self::$sTypeKeyMap[$this->mTypeid])) {
			throw new Lamb_Exception("Can't found type key ");
			return '';
		}
		return self::$sTypeKeyMap[$this->mTypeid];
	}
	
	public function l($str)
	{
		$path = "log.txt";
		$str = date('Y-m-d H:i:s') . " 信息：{$str}";
		file_put_contents($path, $str . "\r\n", FILE_APPEND);
	}
	
	public function intersect(array $default, array $target)
	{
		$arr = array();
		foreach ($default as $key => $item) {
			foreach ($target as $it) {
				if (trim($key) == trim($it)) {
					$arr[] = trim($it);
				} 
			}
		}
		
		return $arr;
	}
	
	public function filterHtmlTag($content, $replaceMent = '')
	{
		return preg_replace('/(<(\/)?[^>]*>)/is', $replaceMent, $content);	
	}
	
}