<?php
class Ttk_Model_ItemAbstract
{
	public $media = array(
		'搜狐' 	 => 'tv.sohu.com',
		'PPTV'   => 'www.pptv.com',
		'乐视' 	 => 'www.letv.com',
		'爱奇艺' => 'www.iqiyi.com',
		'优酷'   => 'www.youku.com',
		'芒果tv' => 'www.hunantv.com',
		'CNTV'   => 'www.cntv.com',
		'土豆'   => 'www.tudou.com',
		'看看'   => 'www.kankan.com',
		'凤凰'   => 'v.ifeng.com',
		'腾讯'   => 'www.qq.com',
		'电影网' => 'www.1905.com'
		
		//'暴风'   => 'www.baofeng.com',
		//'华数'   => 'www.wasu.com',
	);
	
	public $defaultMedia = array(
		'搜狐' => 1, '风行' => 16,  '乐视' => 3, 'bilibili' => 17, '爱奇艺' => 4, '优酷' => 5, 'CNTV' => 11,  '土豆' => 12, '响巢' => 8,'凤凰' => 14, '腾讯' => 13, '芒果TV' => 7,  'PPTV' => 2,  'yinshidaquan' => 15, 'acfun' => 23, '碟调网' => 24
	);
	
	public $diediao_media = array(
		'acfun.gif'  => 23,
		'flv.gif'    => 24,
		'yun.gif' 	 => 24,
		'youku.gif'  => 5 ,
		'letv.gif'   => 3 ,
		'sohu.gif' 	 => 24,
		'bibibi.gif' => 17,
		'qq.gif'	 => 24,
		'tudou.gif'  => 12		
	);
	
	public $chanel_sites = array(
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
	
	public function getRealyUrl($url) 
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		// 不需要页面内容
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		// 不直接输出
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 返回最后的Location
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_exec($ch);
		$info = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
		
		curl_close($ch);
		
		if (strpos($info, 'http://kan.sogou.com')) {
			return -1;
		}
		
		return $info;
	}
	
	public function log($str)
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
