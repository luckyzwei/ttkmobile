<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Db
 */
class Lamb_Db_Table implements Serializable
{
	const UPDATE_MODE = 1;
	
	const INSERT_MODE = 2;
	
	const UPDATE_PREPARE_MODE = 4;
	
	const INSERT_PREPARE_MODE = 8;
	
	/**
	 * @var boolean
	 */
	protected $_mIsToStringEscape = true;
	
	/**
	 * @var string $_table 鏁版嵁搴撹〃鍚?
	 */
	protected $_mTable;
	
	/**
	 * @var int 妯″纺 update or insert
	 */
	protected $_mMode;
	
	/**
	 * @var Lamb_Db_Abstract
	 */
	protected $_mDb = null;
	
	/**
	 * @var array $_mFields 瀛桦偍寰呮搷浣滃垪镄勯敭链煎板嗗悎
	 */
	protected $_mFields = array();
	
	/**
	 * @var string $_mWhere 淇濆瓨鏉′欢璇彞锛屼粎鍦?mode涓簎pdate镞舵湁鏁?
	 */
	protected $_mWhere = '';
	
	/**
	 * @param string $table
	 * @param int $mode
	 */
	public function __construct($table, $mode = self::UPDATE_MODE)
	{
		$this->setOrGetTable($table);
		$this->setOrGetMode($mode);
	}
	
	/**
	 * @param string $table
	 * @return string | Lamb_Db_Table
	 */
	public function setOrGetTable($table = null)
	{
		if (null === $table) {
			return $this->_mTable;
		}
		$this->_mTable = (string)$table;
		return $this;
	}
	
	/**
	 * @param int $mode
	 * @return int | Lamb_Db_Table
	 */
	public function setOrGetMode($mode = null)
	{
		if (null === $mode) {
			return $this->_mMode;
		}
		$this->_mMode = (int)$mode;
		return $this;
	}
	
	/**
	 * @param Lamb_Db_Abstract $db
	 * @return Lamb_Db_Abstract | Lamb_Db_Table
	 */
	public function setOrGetDb(Lamb_Db_Abstract $db = null)
	{	
		if (null === $db) {
			if (null === $this->_mDb) {
				$this->setOrGetDb(Lamb_App::getGlobalApp()->getDb());
			}
			return $this->_mDb;
		}
		$this->_mDb = $db;
		return $this;
	}
	
	/**
	 * @param string $where
	 * @return string | Lamb_Db_Table
	 */
	public function setOrGetWhere($where = null)
	{
		if (null === $where) {
			return $this->_mWhere;
		}
		$this->_mWhere = (string)$where;
		return $this;
	}

	/**
	 * @param string $where
	 * @return boolean | Lamb_Db_Table
	 */	
	public function setOrGetToStringEscape($escape = null)
	{
		if (null === $escape) {
			return $this->_mIsToStringEscape;
		}
		$this->_mIsToStringEscape = (boolean)$escape;
		return $this;
	}
	
	/**
	 * 璁剧疆_mFields链硷紝濡傛灉$key瀛桦湪骞朵笖$val涓簄ull鍒椤垹闄よ阌€?
	 *
	 * @param string $key
	 * @param string | int $val
	 * @return void
	 */
	public function __set($key, $val)
	{
		if ($val === null && isset($this->_mFields[$key])) {
			unset($this->_mFields[$key]);
		} else {
			$this->_mFields[$key] = $val;
		}
	}
	
	/**
	 * 銮峰彇_mFields涓寚瀹氱殑$key镄勫€?
	 * 
	 * @param string $key
	 * @return string | int
	 */
	public function __get($key)
	{
		return isset($this->_mFields[$key]) ? $this->_mFields[$key] : null;
	}
	
	/**
	 * 镓归噺璁剧疆瀛楁
	 *
	 * @param array $fields
	 * @return Lamb_Db_Table
	 */
	public function set(array $fields)
	{
		foreach ($fields as $key => $val) {
			$this->__set($key, $val);
		}
		
		return $this;
	}
	
	/**
	 * 銮峰彇_mFields板嗗悎锛屽鏋滃弬鏁?key涓簄ull鍒栾繑锲炴暣涓?
	 * 
	 * @param string $key
	 * @return string | array
	 */
	public function get($key = null)
	{
		if (null === $key) {
			return $this->_mFields;
		}
		return $this->__get($key);
	}
	
	/**
	 * @return Lamb_Db_Table
	 */
	public function flush()
	{
		$this->_mFields = array();
		$this->_mWhere = '';
		return $this;
	}
	
	/**
	 * 銮峰彇update镙煎纺镄凷QL璇彞锛屽叾涓殑鍒楃殑淇敼鏄抵鎹?_mFields涓殑璁剧疆
	 *
	 * @param boolean $escape
	 * @return string
	 */
	public function getUpdateSql($escape = true, $where = '')
	{
		if (empty($where)) {
			$where = $this->setOrGetWhere();
		}
		if($this->_mTable && count($this->_mFields)){
			reset($this->_mFields);
			$sqlHelper = Lamb_App::getGlobalApp()->getSqlHelper();
			$sql = 'update [' . $this->_mTable . '] set ';
			$key = key($this->_mFields);
			$val =	$escape ? $sqlHelper->escape($this->_mFields[$key]) : $this->_mFields[$key];
			$sql .=	$sqlHelper->escapeField($key) . "='" . $val . "'";
			$fields = array_slice($this->_mFields, 1);
			
			foreach ($fields as $key => $val) {
				$sql .= ',' . $sqlHelper->escapeField($key) . "='";
				$sql .= $escape ?$sqlHelper->escape($val) : $val;
				$sql .= "'";
			}

			if ($where){
				$sql .=	' where ' . $where;
			}
			return $sql;
		}
		return '';		
	}
	
	/**
	 * 銮峰彇update棰勫鐞嗘牸寮忕殑SQL璇彞锛屽叾涓殑鍒楃殑淇敼鏄抵鎹?_mFields涓殑璁剧疆
	 *
	 * @param boolean $escape
	 * @return string	 
	 */
	public function getUpdatePrepareSql($escape = true, $where = '')
	{
		if (empty($where)) {
			$where = $this->setOrGetWhere();
		}
		if($this->_mTable && count($this->_mFields)) {
			reset($this->_mFields);
			$sqlHelper = Lamb_App::getGlobalApp()->getSqlHelper();
			$sql = 'update [' . $this->_mTable . '] set ';
			$key = key($this->_mFields);
			$val = $escape ? $sqlHelper->escape($this->_mFields[$key]) : $this->_mFields[$key];
			$sql .=	$sqlHelper->escapeField($key) . ' = ' . ($this->_mFields[$key] == '?' ? '?' :"'{$val}'");
			$fields	= array_slice($this->_mFields, 1);
			
			foreach ($fields as $key => $val) {
				$val = $escape ? $sqlHelper->escape($val) : $val;
				$sql .= ',' . $sqlHelper->escapeField($key) . '= ';
				$sql .= $fields[$key] == '?' ? '?' : "'{$val}'";
			}

			if ($where){
				$sql .=	' where ' . $where;
			}
			return $sql;
		}
		return '';
	}
	
	/**
	 * 銮峰彇insert镙煎纺镄凷QL璇彞锛屽叾涓殑鍒楃殑淇敼鏄抵鎹?_mFields涓殑璁剧疆
	 *
	 * @param boolean $escape
	 * @return string
	 */	
	public function getInsertSql($escape = true)
	{	
		if ($this->_mTable && count($this->_mFields)) {
			reset($this->_mFields);
			$sqlHelper = Lamb_App::getGlobalApp()->getSqlHelper();
			$sql = 'insert into [' . $this->_mTable . ']( ';
			$key = key($this->_mFields);
			$sql .=	$sqlHelper->escapeField($key);
			$sql2 = " values ( '" . ($escape ? $sqlHelper->escape($this->_mFields[$key]) : $this->_mFields[$key]) . "'";
			$fields	= array_slice($this->_mFields ,1);
			
			foreach ($fields as $key => $val) {
				$sql .= ',' . $sqlHelper->escapeField($key);
				$sql2 .= ",'" . ($escape ? $sqlHelper->escape($val) : $val) . "'";
			}

			return $sql . ')' . $sql2 . ')';
		}
		return '';
	}	
	
	/**
	 * 銮峰彇insert棰勫鐞嗘牸寮忕殑SQL璇彞锛屽叾涓殑鍒楃殑淇敼鏄抵鎹?_mFields涓殑璁剧疆
	 *
	 * @param boolean $escape
	 * @return string
	 */
	public function getInsertPrepareSql($escape = true)
	{		
		if ($this->_mTable && count($this->_mFields)) {
			reset($this->_mFields);
			$sqlHelper = Lamb_App::getGlobalApp()->getSqlHelper();
			$sql = 'insert into [' . $this->_mTable . ']( ';
			$key = key($this->_mFields);
			$sql .= $sqlHelper->escapeField($key);
			$val = $escape ? $sqlHelper->escape($this->_mFields[$key]) : $this->_mFields[$key];
			$sql2 =	' values ( ' . ($this->_mFields[$key] == '?' ? '?' :"'{$val}'");
			$fields	= array_slice($this->_mFields, 1);
			
			foreach ($fields as $key => $val) {
				$sql .= ',' . $sqlHelper->escapeField($key);
				$sql2 .= ',' . ($val == '?' ? '?' : ($escape ? $sqlHelper->escape($val) : $val));
			}
			return $sql . ')' . $sql2 . ')';
		}
		return '';
	}
	
	/**
	 * Get the sql statment
	 *
	 * @return string
	 */
	public function __toString()
	{
		$sql = '';
		$escape = $this->setOrGetToStringEscape();
		switch($this->setOrGetMode()) {
			case self::UPDATE_MODE:
				return $this->getUpdateSql($escape);
			case self::INSERT_MODE:
				return $this->getInsertSql($escape);
			case self::UPDATE_PREPARE_MODE:
				return $this->getUpdatePrepareSql($escape);
			case self::INSERT_PREPARE_MODE:
				return $this->getInsertPrepareSql($escape);
			
		}
		return $sql;
	}
	
	/**
	 * @param array $aPrepareSource
	 * @return boolean is success
	 */
	public function execute(array $aPrepareSource = null)
	{
		$ret = false;
		$sql = $this->__toString();
		if ($sql) {
			$db = $this->setOrGetDb();
			$mode = $this->setOrGetMode();
			if (($mode == self::UPDATE_PREPARE_MODE || $mode == self::INSERT_PREPARE_MODE)) {
				$objRecord = $db->prepare($sql);
				if ($aPrepareSource) {
					Lamb_Db_Abstract::batchBindValue($objRecord, $aPrepareSource);
				}
				if ($objRecord) {
					return $objRecord->execute();
				}
			} else {
				 return $db->exec($sql);
			}
		}
		return $ret;
	}
	
	/**
	 * the Serializable implemention
	 */
	public function serialize()
	{
		$data = array(
			'table' => $this->setOrGetTable(),
			'fields' => $this->get(),
			'where' => $this->setOrGetWhere(),
			'toStringEscape' => $this->setOrGetToStringEscape(),
			'mode' => $this->setOrGetMode()
		);
		return serialize($data);
	}

	/**
	 * the Serializable implemention
	 */	
	public function unserialize($source)
	{
		$data = unserialize($source);
		if ($data && is_array($data)) {
			$this->setOrGetTable($data['tables'])
				 ->setOrGetWhere($data['where'])
				 ->setOrGetToStringEscape($data['toStringEscape'])
				 ->setOrGetMode($data['mode']);
			foreach ($data['fields'] as $key => $val) {
				$this->__set($key, $val);
			}
		}
	}		
}