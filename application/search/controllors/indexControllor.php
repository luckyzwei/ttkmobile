<?php
class indexControllor extends Ttk_Controllor_Abstract
{	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * @var int 
	 */
	protected $mDefaultPagesize = 8;	
	
	public function getControllorName()
	{
		return 'index';
	}	
	
	/**
	 * 影片搜索
	 * req_data:
	 * 		$q string 搜索关键字
	 *		$fileds string默认字段:id,type,name,pic,directors,actors,tag,point,mark
	 *			支持字段：
	 *				id int 影片ID
	 *				type int 1-电影 2-电视剧 3-动漫
	 *				name string 影片名
	 *				pic string 封面
	 *				directors string 导演，多个以空格隔开
	 *				actors string 演员，多个以空格隔开
	 *				tag string 标签，多个以空格隔开
	 *				point float 评分
	 *				mark string
	 *				
	 * res_data:
	 * 	's' => 	0  系统错误
	 * 		   	1  成功
	 * 		   	-1 fields非法
	 * 			
	 * 	'd' => array()
	 */
	public function indexAction()
	{
		$q = trim($this->mRequest->q);
		$fields = trim($this->mRequest->fields);
		
		$resultLimitNum = 20;
		if (empty($q)) {
			$this->showResults(1, array('data' => null));
		}
		
		static $allowFields = array(
			array(
				'id' => 1, 'type' => 1, 'name' => 1, 'pic' => 1, 'directors' => 1, 'actors' => 1, 'tag' => 1, 'point' => 1, 'mark' => 1, 'status' => 1
			)
		);
		
		$defaultFields = 'id,type,name,pic,directors,actors,tag,point,mark,status';
		$fieldObj = new Ttk_Fields_CheckForList($fields, $allowFields, $defaultFields);
		if (!($fields = $fieldObj->toArray())) {
			$this->showResults(-3, null, 'FIELDS_ERR');
		}

		$sql = "select id from movie where name like '" . Lamb_App::getGlobalApp()->getSqlHelper()->escapeBlur($q) . "%' and status=1 order by week_num desc";
		$ret = $this->getDb('movie')->query($sql)->toArray(); 
		
		if (count($ret) < 10) {
			$fulltext = Ttk_Utils::encodeFullSearchStr($q);
			$sql = "select id from (select id,week_num from movie a, freetexttable(movie,search_code,'{$fulltext}',$resultLimitNum) b where a.id=b.[KEY] and status = 1) a order by week_num desc";	
			$ret2 = $this->getDb('movie')->query($sql)->toArray(); 		
			$ret = array_merge($ret, $ret2);
		}
		
		$mids = array();
		foreach ($ret as $it) {
			if (!in_array($it['id'], $mids)) {
				$mids[] = $it['id'];
			}
		}
		
		if (!$mids) {
			$this->showResults(1, array('data' => null));
		}
	
		$movie = new Ttk_Cache_Movie;
		$movieInfos = $movie->get($mids, $fields[0]);	
		$this->showResults(1, array('data' => $movieInfos));
	}
	
	public function index2Action()
	{
		$q = trim($this->mRequest->q);
		$fulltext = Ttk_Utils::encodeFullSearchStr($q);
		$this->d($fulltext); 
	}
	
	/**
	 * 联想搜索（like搜索）
	 * req_data:
	 * 		$q string 搜索关键字	
	 * res_data:
	 * 	's' => 	0  系统错误
	 * 		   	1  成功
	 * 		   	-1 fields非法
	 * 			
	 * 	'd' => array(
	 *		
	 *	)
	 */
	public function associationAction()
	{
		$q = trim($this->mRequest->q);
		$fields = trim($this->mRequest->fields);
		
		if (empty($q)) {
			$this->showResults(1, array('data' => null));
		}
		
		static $allowFields = array(
			array(
				'id' => 1, 'type' => 1, 'name' => 1, 'pic' => 1, 'directors' => 1, 'actors' => 1, 'tag' => 1, 'point' => 1, 'mark' => 1, 'status' => 1
			)
		);
		
		$defaultFields = 'id,type,name,pic,directors,actors,tag,point,mark,status';
		$fieldObj = new Ttk_Fields_CheckForList($fields, $allowFields, $defaultFields);
		if (!($fields = $fieldObj->toArray())) {
			$this->showResults(-1, null, 'FIELDS_ERR');
		}
		$sql = "select top 8 id from movie where status=1 and name like '" . Lamb_App::getGlobalApp()->getSqlHelper()->escapeBlur($q) . "%' order by week_num desc";
		//$this->d($sql);
		$ret = $this->getDb('movie')->query($sql)->toArray();
		
		$mids = array();
		foreach ($ret as $it) {
			$mids[] = $it['id'];
		}
		
		if (!$mids) {
			$this->showResults(1, array('data' => null));
		}
		
		$movie = new Ttk_Cache_Movie;
		$movieInfos = $movie->get($mids, $fields[0]);	
		$this->showResults(1, array('data' => $movieInfos));
	}
	
	
	/**
	 * 获取热搜
	 * req_data:
	 * 				
	 * res_data:
	 * 	's' => 	0  系统错误
	 * 		   	1  成功
	 * 			
	 * 	'd' => array(
	 *		'movie' => array(
	 *			array(
	 *				'id' =>,
	 *				'name' =>,
	 *				'pic' =>
	 *			)
	 *		)
	 *		'teleplay' => array(
	 *			array(
	 *				'id' =>,
	 *				'name' =>,
	 *				'pic' =>
	 *			)
	 *		)
	 *		'anime' => array(
	 *			array(
	 *				'id' =>,
	 *				'name' =>,
	 *				'pic' =>
	 *			)
	 *		)
	 *	)
	 */
	public function getHotAction()
	{
		
		if (version_compare($this->mClientVersion, '1.1', '<')) {
			$data = $this->mSiteCfg['search_hot'];
		} else {
			$data = $this->mSiteCfg['search_hot_2'];
		}
		
		$this->showResults(1, $data);
	}
	
	public function getHunanTvUrlAction()
	{	
		$time = time() * 1000;
		$content = Lamb_Utils::fetchContentByUrlH("http://www.lovev.com/play.msp?contentId=611444877&nodeId=70000014&ran=0.5401319344528019&netspeed=3&jsoncallback=jQuery1111027233104407787323_{$time}&_={$time}");
		//$this->d($content);
		if (!preg_match('/"url":"(.*?)"/is', $content, $result)) {
			$this->showResults(-1, null, 'ID_ERR');
		} else {
			//stripslashes($content)
			$this->showResults(1, array('url' => stripslashes($result[1])));
		}
	}
}
