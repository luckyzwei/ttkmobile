<?php
class indexControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'index';
	}
	
	public function launchAction()
	{
		$this->showResults(1, $this->mSiteCfg['launch']);
	}
	
	/**
	 * @author: wz
	 * 天天看首页
	 * req_data:
	 *	$id  int 视频id
	 *	$type int 类型 1-电影 2-电视剧 3-动漫
	 *  $name int 视频名称
	 *  $mark string 视频文本类型
	 *  $pic string 封面图片
	 * 
	 * res_data:
	 * 	's' =>  0  系统错误
	 * 		    1  成功
	 * 
	 * 	'd' => array('hot' => array, 'anime' => array, 'movie' => array, 'teleplay' => array);
	 */
	public function indexAction()
	{
		$db = $this->getDb('movie');
		
		$hot = $db->query('select top 6 id from movie order by sort_id desc')->toArray();
		$anime = $db->query('select top 6 id from movie where type = 3 and status =1 order by week_num desc')->toArray();
		$movie = $db->query('select top 6 id from movie where type = 1 and status =1 order by week_num desc')->toArray();
		$teleplay =	$db->query('select top 6 id from movie where type = 2 and status =1 order by week_num desc')->toArray();
		$variety = $db->query('select top 6 id from movie where type = 4 and status =1 order by week_num desc')->toArray();
		
		$data = array('hot' => $hot, 'anime' => $anime, 'movie' => $movie, 'teleplay' => $teleplay, 'variety' => $variety);
		
		$movie = new Ttk_Cache_Movie;
		$datas = array();
		
		foreach ($data as $key => $value) {
			foreach ($value as $k=> $v) {
				$datas[$key][$k] = $movie->get($v['id'], 'id,name,pic,mark,type,is_end');
				$datas[$key][$k] = $datas[$key][$k][0];
				$datas[$key][$k]['id'] = $v['id'];
			}
		}
		
		$datas['top'] = $this->mSiteCfg['top_recommend'];
		$this->showResults(1, $datas);
	}
	
	/**
	 * @author: wz
	 * 列表页
	 * 	req_data:
	 *		$type int 类型 0-全部 1-电影 2-电视剧 3-动漫 4-综艺
	 *		$tag string 视频标签
	 *		$area string 地区
	 *		$year int 年份 0-'其他' ， 1-'全部'
	 *		$pinyin string 影片拼音首字母
	 * 		$order int 排序 0-最新 1-周人气 2-月人气 3-总人气 4-月好评 5-总好评
	 *  	$page int 
	 *  	$pagesize int 
	 * 		$ct int 时间戳
	 *		$sign string 签名(通过$page,$ct,'e2fh5a9ej18a2dfbi'，每个字符串中间加'|'拼接,然后md5生成)
	 *  fields string默认字段:id,name,pic,mark,point
	 *		支持字段：
	 *			id int 影片ID
	 *			type int 1-电影 2-电视剧 3-动漫
	 *			name string 影片名
	 *			pic string 封面
	 *			directors string 导演，多个以空格隔开
	 *			actors string 演员，多个以空格隔开
	 *			tag string 标签，多个以空格隔开
	 *			point float 评分
	 *			point_num int 评分人数
	 *			description string 影片描述
	 *			update_time	int	更新时间
	 *			pinyin	string 片名拼音
	 *			is_end int 是否完结。一般针对电视剧或动漫。1-完结，0-未完结
	 *			mark string 当type=1，存放的是资源清晰度以及电影时长
	 *						当type=2，当is_end=0，当前更新的集数
	 *								  当is_end=1，当前影片的总集数
	 * 	res_data:
	 * 	's' =>  0  系统错误
	 * 		    1  成功
	 *			-1 字段有误	
	 * 			-2 签名错误
	
	 * 	'd' => array(
			 '0' => array, 
			 '1' => array, 
			......
	 );
	 */
	public function listAction()
	{	
		$ct = trim($this->mRequest->ct);
		$sign = trim($this->mRequest->sign);
		$type = trim($this->mRequest->type);
		$tag = trim($this->mRequest->tag);
		$area  = trim($this->mRequest->area);
		$year  = trim($this->mRequest->year);
		$pinyin  = trim($this->mRequest->pinyin);
		$order = trim($this->mRequest->order);
		$page = trim($this->mRequest->page);
		$pagesize = trim($this->mRequest->pagesize);
		$fields = trim($this->mRequest->fields);
		
		if (!Lamb_Utils::isInt($page, true)) {
			$page = 1;
		}
		
		if (!$sign || md5($page . '|' . $ct . '|' . self::SALT) != $sign) {
			$this->showResults(-2, null, 'SIGN_ERR');
		}
		
		if (!Lamb_Utils::isInt($type, true) || $type > 4 || $type < 1 ) {
			$type = 0;
		}
		
		if (!Lamb_Utils::isInt($year, true)) {
			$year = 1;
		}
		
		if (!Lamb_Utils::isInt($order, true)) {
			$order = 0;
		}
		
		if (!Lamb_Utils::isInt($pagesize, true)) {
			$pagesize = 30;
		}
		$pagesize = min(max($pagesize, 1), self::MAX_PAGESIZE);	
	
		static $allowFields = array(
			array(
				'id' => 1, 'type' => 1, 'name' => 1, 'pic' => 1, 'directors' => 1, 'actors' => 1, 'tag' => 1, 'point' => 1, 
				'point_num' => 1, 'description' => 1, 'is_end' => 1, 'mark' => 1, 'update_time' => 1
			)
		);

		$defaultFields = 'id,type,name,pic,mark,point,is_end,description';
		$fieldObj = new Ttk_Fields_CheckForList($fields, $allowFields, $defaultFields);
		//检索标签集合
		$search_index = $this->mSiteCfg['search_index'];
		$areaString = implode(',', array_slice($search_index['areas'], 0, count($search_index['areas']) - 1));
		$yearString = implode(',', array_slice($search_index['years'], 0, count($search_index['years']) - 1));
		
		if (!($fields = $fieldObj->toArray())) {
			$this->showResults(-3, null, 'FIELDS_ERR');
		}
		
		$mids = $this->getDb('movie')->quickPrepare('exec getList :type,:tag,:area,:year,:pinyin,:areaString,:yearString,:order,:page,:pagesize',array(
				':type' => array($type, PDO::PARAM_INT),
				':tag' => array($tag, PDO::PARAM_STR, 10),
				':area' => array($area, PDO::PARAM_STR, 10),
				':year' => array($year, PDO::PARAM_INT),
				':pinyin' => array($pinyin, PDO::PARAM_STR, 10),
				':areaString' => array($areaString, PDO::PARAM_STR, 200),
				':yearString' => array($yearString, PDO::PARAM_STR, 200),
				':order' => array($order, PDO::PARAM_INT),
				':page' => array($page, PDO::PARAM_INT),
				':pagesize' => array($pagesize, PDO::PARAM_INT)
			))->toArray();
		
		
		
		if (!$mids) {
			$this->showResults(1, array('data' => null));
		}
		
		foreach ($mids as $k => $v) {
			$mids[$k] = $v['id'];
		}
		
		$movie = new Ttk_Cache_Movie;
		$ret = $movie->get($mids, $fields[0]);
		
		$this->showResults(1, array('data' => $ret));
	}
	
	
}