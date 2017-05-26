<?php
class updateControllor extends Ttk_Controllor_Manager
{
	private $model  = null;
	private $dianshiju  = null;
	private $zongyi = null;
	private $newlist = null;
	private $dianying = null;
	private $dongman = null;
	private $totalPage = 5;
	public function __construct()
	{
		parent::__construct();
		$this->model = new Ttk_Model_Vedio();
		$this->dianshiju  = new Ttk_Model_Dianshiju();
		$this->zongyi = new Ttk_Model_Zongyi();
		$this->newlist = new Ttk_Model_Newlist();
		$this->dianying = new Ttk_Model_Dianying();
		$this->dongman = new Ttk_Model_Dongman();
	}
	
	public function getControllorName()
	{
		return 'update';
	}
	
	public function dianshijuAction()
	{			
		set_time_limit(0) ;
		header("Content-type: text/html; charset=utf-8"); 
		$type = 2;
		$flag = trim($this->mRequest->flag);
		$page = trim($this->mRequest->p);
		$page = Lamb_Utils::isInt($page,true) ? $page : 1;
		
		if ($page > $this->totalPage) {
			echo '采集完毕';
			$this->_exit();
		}
		
		if ($flag) {
			$url = 'http://www.soku.com/channel/teleplaylist_0_0_0_3_' . $page . '.html'; 
		} else {
			$url = $this->newlist->getUrl($type, $page);
		}
		$ret = $this->newlist->collect($url, $type);
		//Lamb_Debuger::debug($ret);
		
		foreach($ret as $key => $it) {
			if (!isset($it['name']) || $it['name'] == '') {
				continue;
			}
			
			$result = $this->model->get($it['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $type, true);
			
			$source = '';
			if (!empty($result)) {
				if ($it['mark'] == $result['mark']) {
					echo "影片[<font color='red'>{$it['name']}</font>]资源暂无更新，跳过采集<br/>";
					continue;
				}
				
				$result = $this->model->getSource($result['id']);
				
				if (empty($result) || !isset($result[0]['source']) || $result[0]['source'] == '' || $result[0]['source'] <= 0) {
					echo "影片[<font color='red'>{$it['name']}</font>]资源不合法，跳过采集<br/>";
					$this->l("影片[:{$it['url']}]资源不合法");
					continue;
				}
				
				$source = $result[0]['source'];
			}
			
			$data = $this->dianshiju->collectItem($it['url'], $source);
			
			if (!$data['play_data']) {
				echo "影片[<font color='red'>{$it['name']}</font>]资源不存在，跳过采集<br/>";
				$this->l("影片资源不存在！地址:{$it['url']}");
				continue;
			}
			
			$data['is_end'] = $it['is_end'];
			$data['mark'] = $it['mark'];

			if ($source) {
				if($this->model->update($data['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $type,  array('mark' => $it['mark'],  'is_end' => $it['is_end'], 'update_time' => time(), 'play_data' => $data['play_data']))) {
					echo "影片[<font color='green'>{$data['name']}</font>]修改成功<br/>";
				} 
			} else {
				$data['pic'] = $this->download($data['pic']);
				echo "影片[<font color='green'>{$data['name']}</font>]" . ($this->model->add($data, $type) ? "<font color='pink'>插入成功</font><br/>" : "<font color='red'>插入失败</font><br/>");		
			}	
		}
		
		$url = $this->mRouter->urlEx($this->C, $this->A, array('p' => ++$page, 'flag' => $flag));
		$this->redirect($url);		
	}

	public function zongyiAction()
	{
		set_time_limit(0) ;
		header("Content-type: text/html; charset=utf-8"); 
		$type = 4;
		$page = trim($this->mRequest->p);
		$page = Lamb_Utils::isInt($page,true) ? $page : 1;
		
		if ($page > $this->totalPage) { 
			echo '采集完毕';
			$this->_exit();
		}
		
		$ret = $this->newlist->collect($this->newlist->getUrl($type, $page), $type);
		if (empty($ret)) {
			echo '采集完毕';
			$this->_exit();
		}
		
		foreach($ret as $it) {
			if (!isset($it['name']) || $it['name'] == '') {
				continue;
			}
			
			$result = $this->model->get($it['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $type, true);
			
			$source = '';
			if (!empty($result)) {
				if ($it['mark'] == $result['mark']) {
					echo "影片[<font color='red'>{$it['name']}</font>]资源暂无更新，跳过采集<br/>";
					continue;
				}
					
				$result = $this->model->getSource($result['id']);
				if (empty($result) || !isset($result[0]['source']) || $result[0]['source'] == '' || $result[0]['source'] <= 0) {
					echo "影片[<font color='red'>{$it['name']}</font>]资源不合法，跳过采集<br/>";
					$this->l("影片[:{$it['url']}]资源不合法");
					continue;
				}
				
				$source = $result[0]['source'];
			}
		
			$data = $this->zongyi->collectItem($it['url'], $it['mark'], $source);
			if (!$data['play_data']) {
				echo "影片[<font color='red'>{$it['name']}</font>]资源不存在，跳过采集<br/>";
				$this->l("影片资源不存在！地址:{$it['url']}");
				continue;
			}
			
			if ($source) {
				if($this->model->update($data['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $type, array('mark' => $it['mark'], 'update_time' => time(), 'play_data' => $data['play_data']))) {
					echo "影片[<font color='green'>{$data['name']}</font>]修改成功<br/>";
				}
			} else {
				/*
				if ( $it['name'] == '绝对中国•端午韵 2015' ) {
					continue;
				}*/
				$data['pic'] = $this->download($data['pic']);
				echo "影片[<font color='green'>{$data['name']}</font>]" . ($this->model->add($data, $type) ? "<font color='pink'>插入成功</font><br/>" : "<font color='red'>插入失败</font><br/>");		
			}	
		}
		
		$url = $this->mRouter->urlEx($this->C, $this->A, array('p' => ++$page));
		$this->redirect($url);
	}
	
	public function dianyingAction()
	{
		set_time_limit(0) ;
		header("Content-type: text/html; charset=utf-8"); 
		$type = 1;
		$page = trim($this->mRequest->p);
		$page = Lamb_Utils::isInt($page,true) ? $page : 1;
		
		if ($page > 2) {
			echo '采集完毕';
			$this->_exit();
		}
		
		$ret = $this->newlist->collect($this->newlist->getUrl($type, $page), $type);
		
		foreach($ret as $it) {
			if (!isset($it['name']) || $it['name'] == '') {
				continue;
			}
			
			$result = $this->model->get($it['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $type, true);
			if (!empty($result)) {
				echo "影片[<font color='red'>{$it['name']}</font>]资源存在跳过采集<br/>";
				continue;
			}
			
			$data = $this->dianying->collectItem($it['url']);
			if (!$data['play_data']) {
				echo "影片[<font color='red'>{$it['name']}</font>]资源不存在，跳过采集<br/>";
				$this->l("影片资源不存在！地址:{$it['url']}");
				continue;
			}
			
			$data['pic'] = $this->download($data['pic']);
			echo "影片[<font color='green'>{$data['name']}</font>]" . ($this->model->add($data, $type) ? "<font color='pink'>插入成功</font><br/>" : "<font color='red'>插入失败</font><br/>");	
		}
		
		
		$url = $this->mRouter->urlEx($this->C, $this->A, array('p' => ++$page));
		$this->redirect($url);
		
	}
	
	
	public function dongmanAction()
	{
		set_time_limit(0) ;
		header("Content-type: text/html; charset=utf-8"); 
		$type = 3;
		$page = trim($this->mRequest->p);
		$page = Lamb_Utils::isInt($page,true) ? $page : 1;
		
		if ($page > $this->totalPage) {
			echo '采集完毕';
			$this->_exit();
		}
		
		$ret = $this->newlist->collect($this->newlist->getUrl($type, $page), $type);
		
		foreach($ret as $it) {
			if (!isset($it['name']) || $it['name'] == '') {
				continue;
			}
			
			
			$result = $this->model->get($it['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $type, true);
			
			$source = '';
			if (!empty($result)) {
				if ($it['mark'] == $result['mark']) {
					echo "影片[<font color='red'>{$it['name']}</font>]资源暂无更新，跳过采集<br/>";
					continue;
				}
				
				$result = $this->model->getSource($result['id']);
				if (empty($result) || !isset($result[0]['source']) || $result[0]['source'] == '' || $result[0]['source'] <= 0) {
					echo "影片[<font color='red'>{$it['name']}</font>]资源不合法，跳过采集<br/>";
					$this->l("影片[:{$it['url']}]资源不合法");
					continue;
				}
				
				$source = $result[0]['source'];
			}
		
			$data = $this->dongman->collectItem($it['url'], $source);
			if (!$data['play_data']) {
				echo "影片[<font color='red'>{$it['name']}</font>]资源不存在，跳过采集<br/>";
				$this->l("影片资源不存在！地址:{$it['url']}");
				continue;
			}
			
			$data['is_end'] = $it['is_end'];
			$data['mark'] = $it['mark'];
		
			if ($source != '') {
				if($this->model->update($data['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $type, array('mark' => $it['mark'],  'is_end' => $it['is_end'], 'update_time' => time(), 'play_data' => $data['play_data']))) {
					echo "影片[<font color='green'>{$data['name']}</font>]修改成功<br/>";
				} 
			} else {
				$data['pic'] = $this->download($data['pic']);
				echo "影片[<font color='green'>{$data['name']}</font>]" . ($this->model->add($data, $type) ? "<font color='pink'>插入成功</font><br/>" : "<font color='red'>插入失败</font><br/>");		
			}	
		}
		
		$url = $this->mRouter->urlEx($this->C, $this->A, array('p' => ++$page));
		$this->redirect($url);		
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