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
	
	/**
	 * 影片详情
	 * req_data:
	 * 		$id int 影片ID
	 *		$fileds string默认字段:id,type,name,pic,directors,actors,tag,point,point_num,description,is_end,mark
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
	 *				description string 影片描述
	 *				
	 *				is_end int 是否完结。一般针对电视剧或动漫。1-完结，0-未完结
	 *				mark string 当type=1，存放的是资源清晰度以及电影时长
	 *							当type=2，当is_end=0，当前更新的集数
	 *									  当is_end=1，当前影片的总集数
	 * res_data:
	 * 	's' => 	0  系统错误
	 * 		   	1  成功
	 * 		   	-1 影片ID错误
	 * 		   	-2 fields非法
	 * 			
	 * 	'd' => array()
	 */
	public function infoAction()
	{
		$isExpire = 0;
		$uid = $this->isLogin($isExpire ,false);
		
		$id = trim($this->mRequest->id);
		$fields = trim($this->mRequest->fields);
		
		if (!Lamb_Utils::isInt($id, true)) {
			$this->showResults(-4, null, '影片ID错误');
		}
		
		static $allowFields = array(
			array(
				'id' => 1, 'type' => 1, 'name' => 1, 'pic' => 1, 'directors' => 1, 'actors' => 1, 'tag' => 1, 'point' => 1, 
				'description' => 1,'is_end' => 1,'mark' => 1, 'year' => 1, 'is_point' => 1, 'is_favorite' => 1, 'comm_num' => 1,
				'source_name' => 1
			)
		);
		
		$defaultFields = 'id,type,name,pic,directors,actors,tag,point,description,is_end,mark,year,is_point,is_favorite,comm_num,source_name';
		$fieldObj = new Ttk_Fields_CheckForList($fields, $allowFields, $defaultFields);
		$isGetPoint = $fieldObj->findAndReplace('is_point');
		$isGetFavorite = $fieldObj->findAndReplace('is_favorite');
		$isGetCommNum = $fieldObj->findAndReplace('comm_num');
		$isGetSourceName = $fieldObj->findAndReplace('source_name');
		
		if (!($fields = $fieldObj->toArray())) {
			$this->showResults(-3, null, 'feilds非法');
		}
		
		$movie = new Ttk_Cache_Movie;
		$videoInfo = $movie->get("{$id}", $fields[0]);		
		$videoInfo = $videoInfo[0];
		
		if (!$videoInfo) {
			$this->showResults(-4, null, '影片ID错误');
		}
		
		$db = $this->getDb('movie');
		
		$week = (int)date('w');
		$month = (int)date('d');
		
		$writeStatus = (int)Lamb_IO_File::getContents($this->mSiteCfg['week_lock_path']);
		
		if ($writeStatus > 3 || $writeStatus < 0) {
			$writeStatus = 0;
		}
		
		if ($week == 1 && !($writeStatus & 1) ) {//如果周一，并且还没有复位周人气
			$db->quickPrepare('exec viewNumHandler :mid,:reset',array(
				':mid' => array($id, PDO::PARAM_INT),
				':reset' => array(1, PDO::PARAM_INT)
			));
			$writeStatus = $writeStatus & 2 | 1;
			Lamb_IO_File::putContents($this->mSiteCfg['week_lock_path'], $writeStatus & 2 | 1);
		} else if ($week != 1 && ($writeStatus & 1)) { //如果不是周一，则修改成未复位标志
			$writeStatus = $writeStatus & 2;
			Lamb_IO_File::putContents($this->mSiteCfg['week_lock_path'], $writeStatus & 2);
		}
		
		if ($month == 1 && !($writeStatus & 2) ) {
			$db->quickPrepare('exec viewNumHandler :mid,:reset',array(
				':mid' => array($id, PDO::PARAM_INT),
				':reset' => array(2, PDO::PARAM_INT)
			));
			Lamb_IO_File::putContents($this->mSiteCfg['week_lock_path'], $writeStatus & 1 | 2);
		} else if ($month != 1 && ($writeStatus & 2)) {
			Lamb_IO_File::putContents($this->mSiteCfg['week_lock_path'], $writeStatus & 1);
		}
		
		$db->quickPrepare('exec viewNumHandler :mid,:reset',array(
				':mid' => array($id, PDO::PARAM_INT),
				':reset' => array(0, PDO::PARAM_INT)
			));
		
		/*
		 * '搜狐' => 1, 'PPTV' => 2, '乐视' => 3, '爱奇艺' => 4, '优酷' => 5, '华数' => 6(放弃)
		 * '芒果tv' => 7, '看看' => 8, 'M1905' => 9, '暴风' => 10, 'CNTV' => 11, '土豆' => 12, 
		 * '腾讯' => 13, '凤凰' => 14 , '影视大全' => 15, '16' => Funshion
		*/
		static $maps = array(
			'1' => '搜狐', '2' => 'PPTV', '3' => '乐视', '4' => '爱奇艺', '5' => '优酷', '7' => '芒果',
			'8' => '看看', '9' => '1905', '10'=>'暴风', '11' => 'CNTV', '12' => '土豆', '13' => '腾讯', '14' => '凤凰',
			'15' => '影视大全', '16' => '风行', '17' => 'Bilibili', '18' => '56', '19' => '吐槽', '20' => '阡陌', '21' => 'Dyued', 
			'22' => 'BdDisk', '23' => 'Acfun', '24' => '碟调', '25' => 'LeDisk'
		);
		
		if ($isGetSourceName) {
			$sourceName = $db->quickPrepare('exec getSourceName :mid',array(
				':mid' => array($id, PDO::PARAM_INT)
			))->toArray();
			$sourceName = $sourceName[0]['source'];
			if (!isset($maps[$sourceName])) {
				$sourceName = '其他';
			}
			$videoInfo['source_name'] = $maps[$sourceName];
		}
		
		if ($isGetPoint || $isGetFavorite || $isGetCommNum) {
			$ret = $db->quickPrepare('exec getMovieFields :uid,:mid,:igp,:igf,:igc', array(
				':uid' => array($uid, PDO::PARAM_INT),
				':mid' => array($id, PDO::PARAM_INT),
				':igp' => array($isGetPoint, PDO::PARAM_INT),
				':igf' => array($isGetFavorite, PDO::PARAM_INT),
				':igc' => array($isGetCommNum, PDO::PARAM_INT)
			))->toArray();
			$ret = $ret[0];
			
			if ($isGetPoint) {
				$videoInfo['is_point'] = $ret['is_point'];
			}
			if ($isGetFavorite) {
				$videoInfo['is_favorite'] = $ret['is_favorite'];
			}
			if ($isGetCommNum) {
				$videoInfo['comm_num'] = $ret['comm_num'];
			}
		}
						
		Ttk_Cache_Movie::clear($id);
		$this->showResults(1, $videoInfo);
	}
	
	/**
	 * 获取播放列表
	 * req_data:
	 * 		$id int 影片ID
	 * 		$order int 1-升序 2-倒序
	 *			
	 * res_data:
	 * 	's' => 	0  系统错误
	 * 		   	1  成功
	 * 		   	-1 影片ID错误
	 * 		   	-2 fields非法
	 * 			
	 * 	'd' => array()
	 */
	public function getPlayDataAction()
	{
		$id = trim($this->mRequest->id);
		$order = trim($this->mRequest->order);
		
		if (!Lamb_Utils::isInt($id, true)) {
			$this->showResults(-1, null, 'MOVIE_ID_ERR');
		}
		
		if ($order != 2) {
			$order = 1;
		}
		
		$play_data = $this->getDb('movie')->quickPrepare('exec getPlayData :mid,:order',array(
			':mid' => array($id, PDO::PARAM_INT),
			':order' => array($order, PDO::PARAM_INT),
		))->toArray();
		
		$ret['play_data'] = array(
			array(
				'name' => '',
				'logo' => '',
				'play_data' => $play_data
			)
		);
		$this->showResults(1, $ret);
	}
	
	/**
	 *反馈
	 *	req_data:
	 *		$mid int 影片id
	 * res_data:
	 * 	's' => 	0  系统错误
	 * 		   	1  成功
	 * 			
	 */
	public function feedbackAction()
	{
		$mid = trim($this->mRequest->mid);
		
		if (!Lamb_Utils::isInt($mid, true)) {
			$this->showResults(1);
		}
		
		$pre = $this->getDb('movie')->prepare('exec userFeedback :mid, :time');
		$pre->bindValue(':mid', $mid, PDO::PARAM_INT);
		$pre->bindValue(':time', time(), PDO::PARAM_INT);
		$pre->execute();
		$this->showResults(1);	
	}
	
}