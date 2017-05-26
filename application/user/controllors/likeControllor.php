<?php
class likeControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'like';
	}
	
	
	/**
	 * @author jude
	 * @method get
	 * 获取猜你喜欢的影片
	 * 
	 * req_data : 
	 * 		page : int 分页起始下标 默认 0 
	 *		pagesize : int 页数 默认 10
	 *		fields : string 默认字段:id,type,name,pic,directors,actors,tag,point,point_num,description,is_end,mark
	 *			支持字段：
	 *				id int 影片ID
	 *				type int 1-电影 2-电视剧 3-动漫
	 *				name string 影片名
	 *				pic string 封面
	 *				directors string 导演，多个以空格隔开
	 *				actors string 演员，多个以空格隔开
	 *				tag string 标签，多个以空格隔开
	 *				point float 评分
	 *				point_num int 评分人数
	 *				update_time int 更新日期
	 *				description string 影片描述
	 *				is_end int 是否完结。一般针对电视剧或动漫。1-完结，0-未完结
	 *				mark string 当type=1，存放的是资源清晰度以及电影时长
	 *							当type=2，当is_end=0，当前更新的集数
	 *									  当is_end=1，当前影片的总集数
	 *				pinyin string 影片拼音
	 *
	 * res_data:
	 * 		s : 
	 *			 1-成功
	 *			-1-未登录
	 *			-3-fields错误
	 *
	 * 		d : null
	 */
	public function listAction()
	{	
		
		$uid = $this->isLogin();
		
		$page = trim($this->mRequest->page);
		$pagesize = trim($this->mRequest->pagesize);
		$fields = trim($this->mRequest->fields);
		
		static $allowFields = array(
			array(
				'id' => 1, 'type' => 1, 'name' => 1, 'pic' => 1, 'directors' => 1, 'actors' => 1, 'tag' => 1, 'point' => 1, 
				'point_num' => 1, 'update_time' => 1, 'description' => 1,'is_end' => 1,'mark' => 1, 'pinyin' => 1
			)
		);
		
		if (!Lamb_Utils::isInt($page, true)) {
			$page = 1;
		}
		
		if (!Lamb_Utils::isInt($pagesize,true)) {
			$pagesize = 10;
		}	
		$pagesize = min(max($pagesize, 1), self::MAX_PAGESIZE);	
		
		$defaultFields = 'id,name,pic,actors,mark,point,description';
						
		$fieldObj = new Ttk_Fields_CheckForList($fields, $allowFields, $defaultFields);
		
		if (!($fields = $fieldObj->toArray())) {
			$this->showResults(-3, null, 'fields错误');
		}
		
		$offset = $pagesize * ($page - 1);
		$ret = $this->getDb('movie')->quickPrepare('exec getLikes :uid,:offset,:pagesize',array(
			':uid' => array($uid, PDO::PARAM_INT),
			':offset' => array($offset, PDO::PARAM_INT),
			':pagesize' => array($pagesize, PDO::PARAM_INT),
		))->toArray();
		
		if (empty($ret)) {
			$this->showResults(-1);			
		}
		
		$mids = array();
		foreach ($ret as $item) {
			$mids[] = $item['id'];
		}
		
		$movie = new Ttk_Cache_Movie();
		$info = $movie->get(implode(',', $mids), $fields[0]);
		
		$this->showResults(1, array('data' => $info ));
	}
	
	
	/**
	 * @author jude
	 * @method get
	 * 统计用户感兴趣的标签 
	 * 
	 * req_data : 
	 * 		id : int 影片ID
	 * res_data:
	 * 		s : 
	 *			1-成功
	 *			-1-未登录
	 *			-3 影片不存在
	 *
	 * 		d : null
	 */
	public function tagAction()
	{
		$uid = $this->isLogin();
		$id = trim($this->mRequest->id);
		
		if (!Lamb_Utils::isInt($id, true)) {
			$this->showResults(-3, null, '影片不存在');	
		}
		
		$this->getDb('movie')->quickPrepare('exec addTags :uid,:id',array(
			':uid' => array($uid, PDO::PARAM_INT),
			':id' => array($id, PDO::PARAM_STR, 200)
		), true);
		
		$this->showResults(1);	
	}
	
}