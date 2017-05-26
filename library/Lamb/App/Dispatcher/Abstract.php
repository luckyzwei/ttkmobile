<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_App_Dispatcher
 */
abstract class Lamb_App_Dispatcher_Abstract implements Lamb_App_Dispatcher_Interface
{
	/**
	 * @var array the controllor and action's alias
	 */
	protected $_mAlias = array();
	
	/**
	 * @var string the default controllor's name
	 */
	protected $_mDefaultControllor = 'index';
	
	/**
	 * @var string the default action's name
	 */
	protected $_mDefaultAction = 'index';
	
	/**
	 * @var string if is null,then use default include path
	 */
	protected $_mControllorPath = null;
	
	/**
	 * @var string
	 */
	protected $_mControllor = ''; 
	
	/**
	 * @var string
	 */
	protected $_mAction = '';
	
	/**
	 * Lamb_App_Dispatcher_Interface implemention
	 */
	public function setAlias(array $alias)
	{
		$this->_mAlias = $alias;
		return $this;
	}
	
	/**
	 * Lamb_App_Dispatcher_Interface implemention
	 */
	public function getRealControllorAction(&$controllor, &$action)
	{
		if (count($this->_mAlias) && array_key_exists($controllor, $this->_mAlias)) {
			$temp = $this->_mAlias[$controllor];
			if (is_array($temp) && array_key_exists($action, $temp)) {
				$action = $temp[$action];
			} else {
				$controllor = $temp;
			}
		}
		unset($controllor, $action);
		return $this;
	}
	
	/**
	 * Lamb_App_Dispatcher_Interface implemention
	 */
	public function setOrGetDefaultControllor($controllor = null)
	{
		if (null === $controllor) {
			return $this->_mDefaultControllor;
		}
		$controllor = (string)$controllor;
		$this->_mDefaultControllor = $controllor;
		return $this;
	}
	
	/**
	 * Lamb_App_Dispatcher_Interface implemention
	 */
	public function setOrGetDefaultAction($action = null)
	{
		if (null === $action) {
			return $this->_mDefaultAction;
		}
		$action = (string)$action;
		$this->_mDefaultAction = $action;
		return $this;	
	}
	
	/**
	 * Lamb_App_Dispatcher_Interface implemention
	 */
	public function setControllorPath($path = null)
	{
		$this->_mControllorPath = $path;
		return $this;
	}
	
	/**
	 * Lamb_App_Dispatcher_Interface implemention
	 */	
	public function setOrGetControllor($controllor = null)	
	{
		if (null === $controllor) {
			return $this->_mControllor;
		}
		$controllor = (string)$controllor;
		$this->_mControllor = $controllor;
		return $this;
	}
	
	/**
	 * Lamb_App_Dispatcher_Interface implemention
	 */	
	public function setOrGetAction($action = null)	
	{
		if (null === $action) {
			return $this->_mAction;
		}
		$action = (string)$action;
		$this->_mAction = $action;
		return $this;
	}	
	
	/**
	 * Retrieve the controllor path 
	 *
	 * @return string
	 */
	public function getControllorPath()
	{
		return $this->_mControllorPath;
	}
	
	/**
	 * @param string $controllorClass
	 * @return void | Controllors
	 */
	public function loadControllor($controllorClass, $instance = false)
	{
		Lamb_Loader::loadClass($controllorClass, $this->getControllorPath());
		if ($instance) {
			return new $controllorClass;
		}
	}
}