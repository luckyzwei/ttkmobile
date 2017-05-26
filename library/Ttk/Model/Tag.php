<?php
class Ttk_Model_Tag
{
	const T_TAG_NAME = 1;
	
	const T_TAG_ID = 2;
	
	private $db = null;
	public function __construct()
	{
		$this->db = Ttk_Db::get('movie');
	}
	
	/**
	 * @param string $tagstr
	 * @return array
	 */
	public function parse($tagstr)
	{
		return explode(" " ,  trim($tagstr));	
	}
	
	/** 
	 * @param mixed $val
	 * @param int $type T_TAG_NAME | T_TAG_ID
	 * @param boolean $isGetData
	 * @return null | int | array
	 */
	public function getTag($val, $type = self::T_TAG_NAME, $isGetData = false)
	{
		$type = (int)$type;
		$aPrepareSource = array();
		$sql = 'select ' . ($isGetData ? '*' : 'tagid') . ' from tag where ';
		if ($type & self::T_TAG_NAME) {
			$sql .= 'tagname=?';
			$aPrepareSource[1] = array($val, PDO::PARAM_STR);
		} else if ($type & self::T_TAG_ID) {
			$sql .= 'tagid=?';
			$aPrepareSource[1] = array($val, PDO::PARAM_INT);
		} else {
			return null;
		}
		
		$ret = $this->db->getNumDataPrepare($sql, $aPrepareSource, true);
		
		if ($ret['num'] != 1) {
			return null;
		}
		return $isGetData ?  $ret['data'] : $ret['data']['tagid'];
	}
	
	/**
	 * @param string | int $tag
	 * @param int $vid
	 * @return int > 0 found <= 0 not found
	 */
	public function getTagRela($tag, $vid, $type = self::T_TAG_ID)
	{
		if ($type == self::T_TAG_NAME && !($tag = $this->getTag($tag))) {
			return 0;
		}
		$sql = 'select tagid from tagrelation where tagid=? and mid=?';
		$aPrepareSource = array(1 => array($tag, PDO::PARAM_INT), 2 => array($vid, PDO::PARAM_INT));
		$ret = $this->db->getNumDataPrepare($sql, $aPrepareSource, true);
		return $ret['num'] == 1 ? $ret['data']['tagid'] : 0;
	}
	
	/**
	 * @param string | array  $tags
	 * @param int $vid
	 * @return
	 */
	public function handle ($tags, $vid)
	{
		if (!is_array($tags)) {
			$tags = $this->parse($tags);
		}
		if (!is_array($tags) || count($tags) < 1) {
			return false;
		}
		$sqltag = 'insert into tag (tagname) values (?)';
		$sqltagrel = 'insert into tagrelation (tagid, mid) values (?, ?)';
		$aPrepareSourceTag =  $aPrepareSourceRela = array();
	
		
		foreach ($tags as $tag) {
			if (strlen($tag) <= 0) {
				continue;
			}
			$this->db->begin();
			if (!$tagid = $this->getTag($tag)) {
				$aPrepareSourceTag[1] = array($tag, PDO::PARAM_STR);
				$this->db->quickPrepare($sqltag, $aPrepareSourceTag, true);
				$tagid = $this->db->lastInsertId();
			}

			if (!$this->getTagRela($tagid, $vid)) {
				$aPrepareSourceRela[1] = array($tagid, PDO::PARAM_INT);
				$aPrepareSourceRela[2] = array($vid, PDO::PARAM_INT);
				$ret = $this->db->quickPrepare($sqltagrel, $aPrepareSourceRela, true);
			}
			$this->db->end();
		}
		return true;
	}	
	
	/**
	 * @param int $tagid
	 * @param int $vid
	 * @return int
	 */
	public function delete($tagid, $vid, $isName = true) 
	{
		if ($isName && !($tagid = $this->getTag($tagid))) {
			return 0;
		}
		return $this->db->quickPrepare('delete from tagrelation where tagid=? and mid=?',
			array( 1 => array($tagid, PDO::PARAM_INT), 2 => array($vid, PDO::PARAM_INT)), true);
	}
	
	public function compareTag($newtag, $oldtag, $vid)
	{
		$aNewTag = $aOldTag = array();
		$aNewTag = array_keys(array_count_values($this->parse($newtag))); #È¥ÖØ¸´
		$aOldTag = array_keys(array_count_values($this->parse($oldtag)));
		foreach (array_diff($aOldTag,$aNewTag) as $tag) {
			$this->delete($tag, $vid);
		}
		
		foreach (array_diff($aNewTag,$aOldTag) as $tag) {
			$this->handle($tag, $vid);
		}
	}
}