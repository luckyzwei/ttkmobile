<?php
class taskControllor extends Ttk_Controllor_Manager
{
	private $vedio = null;
	private $list = null;
	private $diedianshiju = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->vedio = new Ttk_Model_Vedio();
		$this->list = new Ttk_Model_Diediaolist();
		$this->diedianshiju = new Ttk_Model_Diedianshiju();
	}
	
	public function indexAction()
	{
		echo Ttk_Parser_Diediao::parse_byurl('http://www.diediao.com/Japan/rangwojiaoniyuefu/player-1-4.html');
	}
	
	public function collectAction()
	{
		$page = trim($this->mRequest->p);
		$page = Lamb_Utils::isInt($page,true) ? $page : 1;
		
		$db = Ttk_Db::get('movie');
		
		$pagesize = 30;
		$offset = $pagesize * ($page - 1);
		$sql = "select * from important_movies where is_end = 0 order by time desc offset $offset row fetch next $pagesize rows only";
		
		$ret = $db->query($sql)->toArray();
		if (empty($ret)) {
			$this->_exit();
		}
		
		foreach ($ret as $item) {
			if ($item['channel'] == 24) { //碟调网
				$data = $this->diedianshiju->collectItem($item['url'], $mark);
			}
			
			if (empty($data)) {
				continue;
			}
			
			if ($data['is_end']) {
				$db->query("update important_movies set is_end=1 where id={$item['id']}");
			}
			
			$result = $this->vedio->get($data['name'], Ttk_Model_Vedio::T_VIDEO_NAME, $item['type'], true);

			if ($mark == $result['mark']) {
				echo "影片[<font color='red'>{$data['name']}</font>]资源暂无更新，跳过采集<br/>";
				continue;
			}
			
			$time = time();
			if($this->vedio->update($item['mid'], Ttk_Model_Vedio::T_VID, $item['type'],  array('mark' => $mark, 'update_time' => $time, 'is_end' => $data['is_end'], 'play_data' => $data['play_data']))) {
				$db->query("update important_movies set update_time={$time} where id={$item['id']}");
				echo "影片[<font color='green'>{$data['name']}</font>]更新成功<br/>";
			}
		}
		
		$page = $page + 1;
		$this->redirect("?s=task/collect/p/$page");
	}
	
	
	public function getControllorName()
	{
		return 'task';
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
	
}