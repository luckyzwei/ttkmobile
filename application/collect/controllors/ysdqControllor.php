<?php
class ysdqControllor extends Ttk_Controllor_Manager
{
	private $listModel = null;
	private $itemModel = null;
	private $ysdqModel = null;
	private $model  = null;
	private $totalPage = 2;
	public function __construct()
	{
		$this->listModel = new Ttk_Model_YingshidaquanList;
		$this->itemModel = new Ttk_Model_Yingshidaquan;
		$this->ysdqModel = new Ttk_Parser_Ysdq;
		$this->model = new Ttk_Model_Vedio();
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'ysdq';
	}
	
	public function indexAction()
	{
		set_time_limit(0) ;
		header("Content-type: text/html; charset=utf-8"); 
		$page = trim($this->mRequest->p);
		$page = Lamb_Utils::isInt($page,true) ? $page : 1;
		
		$url = $this->listModel->getUrl($page);
		$movieList = $this->listModel->collect($url);
	
		if ($page > $this->totalPage) {
			echo '采集完毕';
			$this->_exit();
		}
		
		foreach ($movieList as $it) {
			$url = $it['url'];
			$data = $this->itemModel->collectItem($url);
			
			if (!$data) {
				continue ;
			}
			
			if ($data['play_data'] == '') {
				continue ;
			}
			
			$data['pic'] = $it['pic'];
			$data['name'] = $it['name'];
			$data['mark'] = $it['mark'];
			
			$result = $this->model->get($it['name'], Ttk_Model_Vedio::T_VIDEO_NAME, 1, true);
			if (!empty($result)) {
				echo "影片[<font color='red'>{$it['name']}</font>]资源存在跳过采集<br/>";
				continue;
			}
			
			$sourceUrl = $data['play_data'];
			$paserUrl = $this->ysdqModel->parse_byurl($sourceUrl);
			
			$_paserUrl = explode('|', $paserUrl);
			if ($_paserUrl[1] == 'superm3u8') {
				$sourceUrl .= '|superm3u8';
			} else {
				$sourceUrl = $paserUrl;
			}
			
			$data['play_data'] = array(
				array(
					'play_data' => $sourceUrl,
					'num' => 1,
					'extra' => 1,
					'source' => 15,
					'time' => time()
				)
			);
			
			//Lamb_Debuger::debug($data);
			
			if (!$data['play_data']) {
				echo "影片[<font color='red'>{$it['name']}</font>]资源不存在，跳过采集<br/>";
				$this->l("影片资源不存在！地址:{$it['url']}");
				continue;
			}
			
			$data['pic'] = $this->download($data['pic']);
			echo "影片[<font color='green'>{$data['name']}</font>]" . ($this->model->add($data, 1) ? "<font color='pink'>插入成功</font><br/>" : "<font color='red'>插入失败</font><br/>");
		}
		
		$url = $this->mRouter->urlEx($this->C, $this->A, array('p' => ++$page));
		$this->redirect($url);
		
	}
	
	public function testAction()
	{
		$data = $this->itemModel->collectItem('http://www.yingshidaquan.cc/html/DQ22062.html');
		if (!$data) {
			Lamb_Debuger::debug('ddd');
		}
		
		Lamb_Debuger::debug($data);
		
		$data['pic'] = 'http://static.yingshidaquan.cc/Uploads/vod/11/5236.jpg';
		$data['name'] = '阿凡达';
		$data['mark'] = '高清';
		
		$sourceUrl = $data['play_data'];
		$sourceUrl = 'http://www.yingshidaquan.cc/html/DQ5236.html';
		$paserUrl = $this->ysdqModel->parse_byurl($sourceUrl);
		
		$_paserUrl = explode('|', $paserUrl);
		if ($_paserUrl[1] == 'superm3u8') {
			$sourceUrl .= '|superm3u8';
		} else {
			$sourceUrl = $paserUrl;
		}
		$sourceUrl = 'http://www.yingshidaquan.cc/html/DQ5236.html';
		$data['play_data'] = array(
			array(
				'play_data' => $sourceUrl,
				'num' => 1,
				'extra' => 1,
				'source' => 15,
				'time' => time()
			)
		);
		
		//Lamb_Debuger::debug($data);
		echo "影片[<font color='green'>{$data['name']}</font>]" . ($this->model->add($data) ? "<font color='pink'>插入成功</font><br/>" : "<font color='red'>插入失败</font><br/>");
	
	}
	
	
	public function redirect($url)
	{
		 echo "<script>
				 function redirect() 
				 {
					 window.location.replace('$url');
				 }
				 window.setTimeout('redirect();', 2000);
			 </script>";
	}
	
	public function _exit()
	{
		$this->mResponse->eecho("<script>window.open('','_self');window.opener=null;window.close();</script>");
	}
	
	public function l($str, $isExit = false)
	{
		$path = "log_msg.txt";
		$str = date('Y-m-d H:i:s') . " 信息：{$str}";
		file_put_contents($path, $str . "\r\n", FILE_APPEND);
		
		if ($isExit) {
			exit;
		}
	}
}