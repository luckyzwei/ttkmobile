<?php
/**
 * Lamb Framework
 * @package Lamb_Queue
 * @author 灏忕緤
 */
class Lamb_Queue_UniqueKey extends Lamb_Queue_Const
{
	/**
	 * @var PHP Standard Callback @return false - not found int - index (@param mixed @param array)
	 */
	protected $mSearchUnqiueKeyCallback = null;
	
	
	/**
	 * @param PHP Standard Callback $callback
	 * @return callback | Lamb_Queue_UniqueKey
	 */
	public function setOrGetSearchUniqueKeyCallback($callback = null)
	{
		if (null === $callback) {
			return $this->mSearchUniqueKeyCallback;
		}
		if (is_callable($callback)) {
			$this->mSearchUniqueKeyCallback = $callback;
		}
		return $this;
	}
	
	/**
	 * @override
	 */
	public function push($data)
	{
		$index = false;
		if ($callback = $this->setOrGetSearchUniqueKeyCallback()) {
			$index = call_user_func($callback, $data, $this->setOrGetData());
		}
		if ($index !== false) {
			unset($this->mData[$index]);
		}
		parent::push($data);
		return $this;
	}
}