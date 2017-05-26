<?php
class Ttk_Model_YingshidaquanList
{
	public function __construct()
	{

	}
		
	public function getUrl($page = 1)
	{
		return "http://www.yingshidaquan.cc/vod-show-id-1-year-2015-p-{$page}.html";
	}

	public function collect($url)
	{
		$ret = array();
		
		if ( !($html = Lamb_Utils::fetchContentByUrl($url))) {
			return $ret;
		}
		
		if (!preg_match('/class="mlist">(.*?)<\/ul>/is', $html, $html)) {
			return $ret;
		}
		$html = $html[1];
		
		if (!preg_match_all('/<li><a href="(.*?)".*?<img src="(.*?)" alt="(.*?)".*?<em>(.*?)</is', $html, $result, PREG_SET_ORDER)) {
			return $ret;
		}		
		
		foreach ($result as $item) {
			$ret[] = array(
				'url' => 'http://www.yingshidaquan.cc' . trim($item[1]), 
				'pic' => trim($item[2]),
				'name' => trim($item[3]),
				'mark' => preg_replace('/\d|韩语|国语|英语|双语|版/', '', $item[4])
			);
		}
		return $ret;
	}
	
	public function d($str)
	{
		Lamb_Debuger::debug($str);
	}
}
