<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Db_RecordSet
 */
class Lamb_Db_RecordSet_Array implements Lamb_Db_RecordSet_CustomInterface
{
	/**
	 * @var array the data source
	 */
	protected $_mSource = null;
	
	/**
	 * @var int 
	 */
	protected $_mCurrentPos = 0;
	
	/**
	 * @param array $source
	 */
	public function __construct(array $source)
	{
		$this->_mSource = $source;
	}
	
	/**
	 * Lamb_Db_RecordSet_CustomInterface implemention
	 */
	public function toArray()
	{
		return $this->_mSource;
	}
	
	/**
	 *  Lamb_Db_RecordSet_CustomInterface implemention
	 */
	public function getRowCount()
	{
		return $this->count();
	}

	/**
	 *  Lamb_Db_RecordSet_CustomInterface implemention
	 */	
	public function getColumnCount()
	{
		return isset($this->_mSource[0]) ? count($this->_mSource[0]) : 0;
	}

	/**
	 * Iterator implemention
	 */
	public function current()
	{
		return $this->_mSource[$this->_mCurrentPos];
	}

	/**
	 * Iterator implemention
	 */	
	public function key()
	{
		return $this->_mCurrentPos;
	}
	
	/**
	 * Iterator implemention
	 */
	public function rewind()
	{
		$this->_mCurrentPos = 0;
	}
	
	/**
	 * Iterator implemention
	 */	
	public function next()
	{
		$this->_mCurrentPos ++ ;
	}
	
	/**
	 * Iterator implemention
	 */
	public function valid()
	{
		return $this->_mCurrentPos < $this->count();
	}	
	
	/**
	 * Countable implemention
	 */
	public function count()
	{
		return count($this->_mSource);
	}	
}