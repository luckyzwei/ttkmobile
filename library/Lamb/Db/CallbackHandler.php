<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Db
 */
class Lamb_Db_CallbackHandler
{
	/**
	 * @var PHP callback
	 */
	protected $mDbCallback = null;
	
	/**
	 * @param PHP callback $callback
	 */
	public function __construct($callback = null)
	{
		if (null !== $callback) {
			$this->setDbCallback($callback);
		}
	}
	
	 /**
	  * Set the value of '_mDbCallback' 
	  *
	  * @param PHP callback | Lamb_Db_Callback_Interface $callback
	  * @return Lamb_Db_CallbackHandler
	  * @thorws Lamb_Db_Exception
	  */		
	public function setDbCallback($callback)
	{
		if (null === $callback) {
			$this->mDbCallback = null;
		} else if ($callback instanceof Lamb_Db_Callback_Interface || is_callable($callback)) {
			$this->mDbCallback = $callback;
		} else {
			throw new Lamb_Exception("Invaild $callback argument,$callback must be a Lamb_Db_Callback_Interface implemetion or PHP standard callback ");
		}
		return $this;
	}
	
	/**
	 * @return Lamb_Db_Abstract
	 */
	public function getDb()
	{
		if (null === $this->mDbCallback) {
			$db = Lamb_App::getGlobalApp()->getDb();
		} else if ($this->mDbCallback instanceof Lamb_Db_Callback_Interface){
			$db = $this->mDbCallback->getDb();
		} else {
			$db = call_user_func($this->mDbCallback);
		}
		return $db;
	}
	
	/**
	 * @return PHP callback
	 */
	public function getDbCallback()
	{
		return $this->mDbCallback;
	}	
}