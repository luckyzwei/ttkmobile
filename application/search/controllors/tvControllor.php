<?php
class tvControllor extends Lamb_Controllor_Abstract
{	
	public function __construct()
	{
		parent::__construct();
	}
		
	
	public function getControllorName()
	{
		return 'tv';
	}	
	
	/**
	 * 带错误信息的输出
	 *
	 * @param int $code 错误码
	 * @param array $data 输出的内容
	 * @param string $errorString 错误信息，如果为空，当$code=0,-1,-2则会输出固定的错误信息，如果不为空，则会先从配置文件error_strings找出对应的映射，
	 * 如果找不到映射，则直接将该值输出
	 */
	public function showResults($code, array $data = null, $errorString = '')
	{
		static $fixedErrorStr = array(
			'0' => '服务器繁忙，请稍后再试',
			'-1' => '您还没有登录',
			'-2' => '登录过期，请重新登录'
		);
		
		$ret = array('s' => $code);
		
		if ($data) {
			$ret['d'] = $data;
		}
		
		if ($errorString && isset($this->mSiteCfg['error_strings']) && isset($this->mSiteCfg['error_strings'][$errorString])) {
			$errorString = $this->mSiteCfg['error_strings'][$errorString];
		}
		
		if (!$errorString && isset($fixedErrorStr[$code])) {
			$errorString = $fixedErrorStr[$code];
		}
		
		$ret['err_str'] = $errorString;
		
		$ret = json_encode($ret);
		$this->mResponse->eecho($ret);	
	}	
	
	public function getHunanTvUrlAction()
	{	
		$time = time() * 1000;
		$content = Lamb_Utils::fetchContentByUrlH("http://www.lovev.com/play.msp?contentId=611444877&nodeId=70000014&ran=0.5401319344528019&netspeed=3&jsoncallback=jQuery1111027233104407787323_{$time}&_={$time}");
		//Lamb_Debuger::debug($content);
		if (!preg_match('/"url":"(.*?)"/is', $content, $result)) {
			$this->showResults(-1, null, '暂不能播放');
		} else {
			//stripslashes($content)
			$this->showResults(1, array('url' => stripslashes($result[1])));
		}
	}
}
