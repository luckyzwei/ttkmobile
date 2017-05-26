<?php
class testControllor extends Ttk_Controllor_Manager
{
	private $model = null;
	private $dianshiju  = null;
	private $list = null;
	private $diedianshiju = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->dianshiju  = new Ttk_Model_Dianshiju();
		$this->model = new Ttk_Model_Vedio();
		$this->list = new Ttk_Model_Diediaolist();
		$this->diedianshiju = new Ttk_Model_Diedianshiju();
	}
	
	public function collectAction()
	{	
		$data = $this->diedianshiju->collectItem('http://www.diediao.com/SoutheastAsia/tadezuitaiju/', $mark);
	
		Lamb_Debuger::debug($data);
	
		$ret = $this->list->collect($this->list->getUrl(1,1));
		
		foreach ($ret as $item) {
			
			$data = $this->diedianshiju->collectItem('http://www.diediao.com/Domestic/fangunbaxiaoming/', $mark);
			Lamb_Debuger::debug($data);
		}
		
		print_r($ret);
		
	}
	
	public function getControllorName()
	{
		return 'test';
	}
	
	public function deleteAction()
	{
		set_time_limit(0) ;
		header("Content-type: text/html; charset=utf-8"); 
		
		$sql = 'SELECT top 1000 * FROM movie where id>72938 ';
		$db = Ttk_Db::get('movie');
		$ret = $db->query($sql)->toArray();
		
		foreach($ret as $item) {
			$db->query("delete from movie  where id={$item['id']}");
			$db->query("delete from movie_source where mid={$item['id']}");
			$db->query("delete from tagrelation where mid={$item['id']}");
		}
		
		echo 'OK';
		
	}
	
	public function ysdqAction()
	{
		$url = 'http://www.yingshidaquan.cc/html/DQ220787.html';
		$itemModel = new Ttk_Model_Yingshidaquan;
		$ysdqModel = new Ttk_Parser_Ysdq;
		
		$data = $itemModel->collectItem($url);
		$sourceUrl = $data['play_data'];
		$paserUrl = $ysdqModel->parse_byurl($sourceUrl);
		
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
		
		Lamb_Debuger::debug($data);
	}
	
	
	public function tempAction()
	{	
		set_time_limit(0) ;
		$type = 2;
		header("Content-type: text/html; charset=utf-8"); 
		
		$name = '他来了，请闭眼';
		$url  = 'http://www.soku.com/detail/show/XMTE5NjYwNA==';
		
		$result = $this->model->get($name, Ttk_Model_Vedio::T_VIDEO_NAME, $type, true);
		
		$source = '';
		if (!empty($result)) {
			$result = $this->model->getSource($result['id']);
					
			if (empty($result) || !isset($result[0]['source']) || $result[0]['source'] == '' || $result[0]['source'] <= 0) {
				exit("影片{$name}资源不合法，跳过采集");
			}
			
			$source = $result[0]['source'];
		}
		
		$data = $this->dianshiju->collectItem($url, $source);
		$mark = 16;
		$data['mark'] = $mark;
		
		if ($source) {
			if($this->model->update($data['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $type,  array('mark' => $mark,  'is_end' => 0, 'update_time' => time(), 'play_data' => $data['play_data']))) {
				echo "影片[<font color='green'>{$name}</font>]修改成功<br/>";
			} 
		} 		
		
		echo 'OK';
		
	}
	
	public function redirect($url)
	{
		 echo "<script>
				 function redirect() 
				 {
					 window.location.replace('$url');
				 }
				 window.setTimeout('redirect();', 5000);
			 </script>";
	}
	
	public function l($str)
	{
		$path = "log_msg.txt";
		$str = date('Y-m-d H:i:s') . " 信息：{$str}";
		file_put_contents($path, $str . "\r\n", FILE_APPEND);
	}
}