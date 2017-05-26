<?php
/**
 * Lamb Framework
 * @package Lamb_Queue
 * @author 灏忕緤
 */
class Lamb_Queue_Const
{
	/**
	 * @var int
	 */
	protected $mLength;
	
	/**
	 * @var array
	 */
	protected $mData = array();
	
	/**
	 * @param int $len
	 */
	public function __construct($len)
	{
		$this->setOrGetLength($len);
	}
	
	/**
	 * @param int $len
	 * @return int | Lamb_Stack_Const
	 */
	public function setOrGetLength($len = null)
	{
		if (null === $len) {
			return $this->mLength;
		}
		$this->mLength = (int)$len;
		return $this;
	}
	
	/**
	 * @param array $data
	 * @return array | Lamb_Stack_Const
	 */
	public function setOrGetData(array $data = null)
	{
		if (null === $data) {
			return $this->mData;
		}
		$this->mData = $data;
		return $this;
	}
	
	/**
	 * @param mixed $data
	 * @return Lamb_Queue_Const
	 */
	public function push($data)
	{
		$len = $this->setOrGetLength();
		if ($this->getDataLen() >= $len) {
			unset($this->mData[$len - 1]);
		}
		array_unshift($this->mData, $data);
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function pop()
	{
		return array_shift($this->mData);
	}
	
	/**
	 * @return int
	 */
	public function getDataLen()
	{
		return count($this->mData);
	}
}