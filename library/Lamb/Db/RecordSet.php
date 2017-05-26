<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Db
 */
class Lamb_Db_RecordSet extends PDOStatement implements Lamb_Db_RecordSet_Interface
{
	/**
	 * @var Lamb_Db_Abstract 鏁版嵁搴揿璞?
	 */
	protected $db = null;
	
	/**
	 * @var $bindParams 淇濆瓨缁戝畾镄勫弬鏁?
	 */
	protected $bindParams = array();
	
	/**
	 * @var $bindValues 淇濆瓨缁戝畾镄勫€?
	 */
	protected $bindValues = array();
	
	/** 
	 * $queryString涓槸鍚﹀惈链塽nion
	 * null - 榛樿浣跨敤Lamb_Db_Sql_Helper::hasUnion銮峰彇
	 * false, true
	 *
	 * @var boolean 
	 */
	protected $hasUnion = null;
	
	/**
	 * Construct the Lamb_Db_RecordSet
	 * 鍙敤浜嶱OD::setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Lamb_Db_RecordSet', array(PDO)))
	 *
	 * @param Lamb_Db_Abstract $db
	 */
	protected function __construct(Lamb_Db_Abstract $db = null)
	{
		if (null !== $db) {
			$this->setOrGetDb($db);
		}
	}
	
	/**
	 * @param Lamb_Db_Abstract $db
	 * @return Lamb_Db_Abstract | Lamb_Db_RecordSet
	 */
	public function setOrGetDb(Lamb_Db_Abstract $db = null)
	{
		if (null === $db) {
			return $this->db;
		}
		$this->db = $db;
		return $this;
	}
	
	/**
	 * @param boolean $hasUnion or null
	 * @return Lamb_Db_Select
	 */
	public function setHasUnion($hasUnion)
	{
		$this->hasUnion = $hasUnion === null ? null : (boolean)$hasUnion;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function getHasUnion()
	{
		return $this->hasUnion;
	}	
	
	/**
	 * 鏀堕泦镓€链夌粦瀹氱殑鍙傛暟锛屼缭瀛桦湪bindParams鏁扮粍涓紝
	 * 浠ヤ究璋幂敤getAllCountCount鐢?
	 *
	 * @override
	 *
	public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
	{
		$param = array(&$variable, $data_type);
		if (null === $length) {
			$ret = parent::bindParam($parameter, $data_type);
		} else if (null == $driver_options) {
			$param[] = $length;
			$ret = parent::bindParam($parameter, $data_type, $length);
		} else {
			$param[] = $length;
			$param[] = $driver_options;
			$ret = parent::bindParam($parameter, $data_type, $length);
		}
		unset($variable);
		$this->bindParams[$parameter] = $param;
		return $ret;
	}/
	
	/**
	 * 鏀堕泦镓€链夌粦瀹氱殑鍙傛暟锛屼缭瀛桦湪bindParams鏁扮粍涓紝
	 * 浠ヤ究璋幂敤getAllCountCount鐢?
	 *	
	 * @override
	 */
	public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
	{
		$this->bindValues[$parameter] = array($value, $data_type);
		return parent::bindValue($parameter, $value, $data_type);
	}
	
	/**
	 * $value涓簄ull镞跺€欙紝褰?key涓哄瓧绗︿覆鍒欐槸鍒犻櫎姝ら敭链?
	 * 褰?key涓篴rray镞讹紝鍒欐槸镓归噺淇敼
	 *
	 * @param string|int $key
	 * @param array | null $value
	 * @return Lamb_Db_RecordSet
	 */
	public function setBindParams($key, $value = null)
	{
		if (null === $value) {
			if (is_string($key)) {
				unset($this->bindParams[$key]);
			}
			if (is_array($key)) {
				foreach ($key as $k => $v) {
					$this->setBindParams($k, $v);
				}
			}
		} else if (is_array($value)){
			$this->bindParams[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * @param string | int $key 濡傛灉涓簄ull鍒栾繑锲炴暣涓暟缁勶紝鍚﹀垯鍒栾繑锲为敭链煎搴旗殑链?
	 * @return array
	 */
	public function getBindParams($key = null)
	{
		if (null === $key) {
			return $this->bindParams;
		}
		return isset($this->bindParams[$key]) ? $this->bindParams[$key] : null;
	}

	/**
	 * $value涓簄ull镞跺€欙紝褰?key涓哄瓧绗︿覆鍒欐槸鍒犻櫎姝ら敭链?
	 * 褰?key涓篴rray镞讹紝鍒欐槸镓归噺淇敼
	 *
	 * @param string|int $key
	 * @param array | null $value
	 * @return Lamb_Db_RecordSet
	 */	
	public function setBindValues($key, $value = null)
	{
		if (null === $value) {
			if (is_string($key)) {
				unset($this->bindValues[$key]);
			}
			if (is_array($key)) {
				foreach ($key as $k => $v) {
					$this->setBindValues($k, $v);
				}
			}
		} else if (is_array($value)) {
			$this->bindValues[$key] = $value;
		}
		return $this;
	}
	
	/**
	* @param string | int $key 濡傛灉涓簄ull鍒栾繑锲炴暣涓暟缁勶紝鍚﹀垯鍒栾繑锲为敭链煎搴旗殑链?
	 * @return array
	 */
	public function getBindValues($key = null)
	{
		if (null === $key) {
			return $this->bindValues;
		}
		return isset($this->bindValues[$key]) ? $this->bindValues[$key] : null;
	}
	
	/**
	 * 姝ゆ柟娉曚笉阃傚悎璋幂敤bindParam璁块棶涓殑PARAM_OUTPUT鍙傛暟
	 *
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function getRowCount()
	{
		$nRowCount	=	parent::rowCount();
		if ($nRowCount < 0) {
			$hasUnion = $this->getHasUnion();
			if (null === $hasUnion) {
				$hasUnion = Lamb_App::getGlobalApp()->getSqlHelper()->hasUnionKey($this->queryString);
			}
			if (count($this->bindParams) <= 0 && count($this->bindValues) <= 0) { //娌℃湁浣跨敤棰勫鐞嗘煡璇?
				$nRowCount = $this->db->getRowCountEx($this->queryString, $hasUnion);
			} else {
				foreach ($this->bindParams as $key => $val) {
					switch(count($val)) {
						case 2:
							parent::bindParam($key, $val[0], $val[1]);
							break;
						case 3:
							parent::bindParam($key, $val[0], $val[1], $val[2]);
							break;
						case 4:
							parent::bindParam($key, $val[0], $val[1], $val[2], $val[3]);
							break;
					}
				}
				$nRowCount = $this->db->getPrepareRowCount($this->queryString, $this->bindValues, $hasUnion);
			}
		}
		return $nRowCount;	
	}
	
	/**
	 * Countable implemention
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->getRowCount();
	}
	
	/**
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function toArray()
	{
		return $this->fetchAll();
	}
	
	/**
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function getColumnCount()
	{
		return $this->columnCount();
	}
}