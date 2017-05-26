<?php
class Ttk_Model_Vedio
{
	const T_VID = 1;
	
	const T_VIDEO_NAME = 2;
	
	const T_IS_LOCKCOLL = 4;
	
	protected $mListeners = array();

	private $db = null;
	private $pinyin = null;
	public function __construct()
	{
		$this->db = Ttk_Db::get('movie');
		$this->pinyin = new Ttk_Model_Pinyin();
	}
	
		
	/**
	 * @param int $event Ttk_Model_VideoListenerInterface::ON_BEFORE_UPDATE ...
	 * @param Ttk_Model_VideoListenerInterface
	 * @return Ttk_Model_Video
	 */
	public function addEventListener($event, Ttk_Model_VideoListenerInterface $listener)
	{
		$this->mListeners[$event][] = $listener;
		return $this;
	}
	
	/**
	 * @param int $event
	 * @param Ttk_Model_VideoListenerInterface $listener
	 * @return Ttk_Model_Video
	 */
	public function removeListener($event, Ttk_Model_VideoListenerInterface $listener)
	{
		if (isset($this->mListeners[$event]) && ($listeners = $this->mListeners[$event]) && ($index = array_search($listener, $listeners)) !== false) {
			unset($this->mListeners[$event][$index]);
		}
		return $this;
	}
	
	/**
	 * @param int $event
	 * @param array $videoInfo
	 * @return Ttk_Model_Video
	 */
	public function fireEvent($event, array $videoInfo)
	{
		if (isset($this->mListeners[$event])) {
			foreach ($this->mListeners[$event] as $listener) {
				$listener->on($event, $videoInfo);
			}
		}
		return $this;
	}
	
	public function get($val, $type = self::T_VID, $media_type = 2, $isGetData = false, $includeVid = 0)
	{
		$sql = 'select ' . ($isGetData ? '*' : 'id') . ' from movie where type=' . $media_type;
		if ($type & self::T_VID) {
			$sql .= ' and id=?';
			$aPrepareSource[1] = array($val, PDO::PARAM_INT);
		} else if ($type & self::T_VIDEO_NAME) {
			$sql .= ' and name=?';
			$aPrepareSource[1] = array($val, PDO::PARAM_STR, 50);
		} else {
			return null;
		}
		
		if ($type & self::T_IS_LOCKCOLL) {
			$sql .= ' and status=?';
			$aPrepareSource[2] = array(1, PDO::PARAM_STR);
		}
		if ($type & self::T_VID && $includeVid > 0) {
			$sql .= ' and id != :niid';
			$aPrepareSource[':niid'] = array($includeVid, PDO::PARAM_INT);
		}
		
		$ret = $this->db->getNumDataPrepare($sql, $aPrepareSource, true);
		if ($ret['num'] != 1) {
			return null;
		}
		
		return $isGetData ? $ret['data'] : $ret['data']['id'];
	}
	
	
	public function getSource($mid, $fields = 'play_data,source,num,extra,description')
	{
		$sql = "select {$fields} from movie_source where mid=:mid";
		
		$aPrepareSource = array(
			':mid' => array($mid, PDO::PARAM_INT)
		);
		$ret = $this->db->quickPrepare($sql, $aPrepareSource)->toArray();
		
		return $ret;
	}
	
	public function getSourceData($id, $fields = 'play_data,source,extra,num')
	{
		$sql = "select {$fields} from movie_source where id=:id";
		
		$aPrepareSource = array(
			':id' => array($id, PDO::PARAM_INT)
		);
		$ret = $this->db->quickPrepare($sql, $aPrepareSource)->toArray();
		
		return $ret;
	}
	
	public function add(array $videoInfo, $media_type)
	{
		unset($videoInfo['id']);
		if ($this->get($videoInfo['name'], self::T_VIDEO_NAME, $media_type)) {
			return -1;
		}

		$this->fireEvent(Ttk_Model_VideoListenerInterface::ON_BEFORE_INSERT, $videoInfo);
		$videoInfo['search_code'] = Ttk_Utils::encodeFullSearchStr($videoInfo['name']);
		$videoInfo['pinyin'] = $this->pinyin->transformWithoutTone($videoInfo['name']);
		$videoDataInfo['play_data'] = $videoInfo['play_data'];
		unset($videoInfo['play_data']);
		
		$videoTable = new Lamb_Db_Table('movie', Lamb_Db_Table::INSERT_MODE);		
		$videoDataTable = new Lamb_Db_Table('movie_source', Lamb_Db_Table::INSERT_MODE);
		$tagmodel = new Ttk_Model_Tag();	
				
		if (!isset($videoDataInfo['play_data']) || empty($videoDataInfo['play_data'])) {
			return -1;
		}
		
		$this->db->begin();
		$videoTable->set($videoInfo)
				   ->setOrGetDb($this->db)
				   ->execute();
		$vid = $this->db->lastInsertId();
		
		foreach ($videoDataInfo['play_data'] as $item) {
			$item['mid'] = $vid;
			$videoDataTable->set($item)
						   ->setOrGetDb($this->db)
						   ->execute();		   
		}			   
					   
		if ($this->db->end()) {
			$temp = $videoInfo + $videoDataInfo;
			$this->fireEvent(Ttk_Model_VideoListenerInterface::ON_AFTER_INSERT, $temp);
			$tagmodel->handle($videoInfo['directors'], $vid);
			$tagmodel->handle($videoInfo['actors'], $vid);
			$tagmodel->handle($videoInfo['tag'], $vid);				
			return $vid;
		}
		
		return 0;
	}
	
	
	/**
	 * @param mixed $val
	 * @param int $type T_VID | T_VIDEO_NAME
	 * @param array $videoinfo
	 * @return int 1 - succ 0 - not found -1 videoname exits
	 */
	public function update($val, $type, $media_type, array $videoInfo)
	{
		$type = (int)$type;
		if (!($ret = $this->get($val, $type, $media_type, true))) {
			return 0;
		}
		unset($videoInfo['id']);
		if (isset($videoInfo['name']) && $ret['name'] != $videoInfo['name']) {
			$videoInfo['pinyin'] = $this->pinyin->transformWithoutTone($videoInfo['name']);
			if ($this->get($videoInfo['name'], self::T_VIDEO_NAME, false, $ret['name'])) {
				return -1;
			}
		}
		
		if (isset($videoInfo['mark']) && $ret['mark'] == $videoInfo['mark']) {
			return 1;
		}
		
		$this->fireEvent(Ttk_Model_VideoListenerInterface::ON_BEFORE_UPDATE, $videoInfo);
		$videoDataInfo = array();
		if (isset($videoInfo['play_data'])) {
			$play_data = $this->getSource($ret['id']);
			
			//Lamb_Debuger::debug($play_data);
		
			if ($media_type == 4) {
				$play_data = $this->arrayRecursiveDiffZongyi($videoInfo['play_data'], $play_data);
			} else {
				$play_data = $this->arrayRecursiveDiff($videoInfo['play_data'], $play_data);
			}
				
			$videoDataInfo['play_data'] = $play_data;
			unset($videoInfo['play_data']);
		}
		
		$videoTable = new Lamb_Db_Table('movie');
		$videoDataTable = new Lamb_Db_Table('movie_source', Lamb_Db_Table::INSERT_MODE);
	
		$tagmodle = new Ttk_Model_Tag();
		if (isset($videoInfo['directors']) && $videoInfo['directors'] != $ret['directors']) {
			$tagmodle->compareTag($videoInfo['directors'], $ret['directors'], $ret['id']);
		}
		if (isset($videoInfo['actors']) && $videoInfo['actors'] != $ret['actors'])	{
			$tagmodle->compareTag($videoInfo['actors'], $ret['actors'], $ret['id']);
		}
		if (isset($videoInfo['tag']) && $videoInfo['tag'] != $ret['tag'])	{
			$tagmodle->compareTag($videoInfo['tag'], $ret['tag'], $ret['id']);
		}
		$this->db->begin();
		if (count($videoDataInfo) > 0) {
			foreach ($videoDataInfo['play_data'] as $item) {
				if (!isset($item['play_data'])){
					continue;
				}
				$item['mid'] = $ret['id'];
				$videoDataTable->set($item)
							   ->setOrGetDb($this->db)
							   ->execute();
					  
			}
		}

		$videoTable->setOrGetWhere('id=' . $ret['id'])
		           ->setOrGetDb($this->db)
				   ->set($videoInfo)
			       ->execute();	

				   
		if ( $media_type > 1 ) {
			
			$uids = $this->db->quickPrepare('select uid from favorite where mid=:mid', array(
						':mid' => array($ret['id'], PDO::PARAM_INT)
					))->toArray();
			
			if (!empty($uids)) {
				$temp = array();
				foreach ($uids as $val) {
					$temp[] = $val['uid'];
				}
				
				$uids_str = implode(',', $temp);
				$utils = new Ttk_Push_Utils();
				$utils->favorite($uids_str, '您收藏的影片更新了！');
			}	
			
		}
			
			
		if($this->db->end()) {
			$temp = $videoInfo + $videoDataInfo;
			$this->fireEvent(Ttk_Model_VideoListenerInterface::ON_AFTER_UPDATE, $temp);
			$id = $ret['id'];
			Ttk_Cache_Movie::clear($id);
			//Ttk_Utils::flushCDN(array("http://item.m.ttkvod.com/?c=index&a=info&id={$id}", "http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}", "http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&order=1", "http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&order=2", "http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&order=1&pagesize=50", "http://item.m.ttkvod.com/?c=index&a=getPlayData&id={$id}&order=2&pagesize=50")); 
			return 1;
		} 
		return 0;
	}
	
	public function updateSource($val, array $videoInfo)
	{
		$videoDataTable = new Lamb_Db_Table('movie_source');
		$videoDataTable->setOrGetWhere('id=' . $val)
		           ->setOrGetDb($this->db)
				   ->set($videoInfo)
			       ->execute();		
	}
	
	
	// 否则获取$arr_new中与$arr_old不同的
	/*
	public function arrayRecursiveDiff($arr_new, $arr_old) 
	{
		$aReturn = array(); 
		foreach ($arr_new as $mKey => $mValue) { 
			if (array_key_exists($mKey, $arr_old)) { 
				if (is_array($mValue)) { 
					$aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $arr_old[$mKey]); 
					if (count($aRecursiveDiff)) { 
						$aReturn[$mKey] = $aRecursiveDiff;
					} 
				} else { 
					if ($mValue != $arr_old[$mKey]) { 
						$aReturn[$mKey] = $mValue; 
					} 
				} 
			} else { 
				$aReturn[$mKey] = $mValue; 
			} 
		}
		
		//Lamb_Debuger::debug($aReturn);
		return $aReturn; 
	}*/
	
	public function arrayRecursiveDiff($arr_new, $arr_old) 
	{
		//array_multisort($arr_old, SORT_ASC);
		$old_mark = $arr_old[count($arr_old)-1]['num'];
			
		$temp = array();	
		foreach ($arr_new as $item) {
			if ($item['num'] > $old_mark) {
				$temp[] = $item;
			}
		}
		
		return $temp;	
	}
	
	public function arrayRecursiveDiffZongyi($arr_new, $arr_old) 
	{
		$ret = array();
		$new_length = count($arr_new);
		$old_length = count($arr_old);
		$length = $new_length - $old_length;
		
		$ret = array_slice($arr_new, 0, $length);
		return $ret;
	}	
	
	
	
}
