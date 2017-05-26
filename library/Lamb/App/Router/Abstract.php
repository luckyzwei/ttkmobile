<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_App_Router
 */
abstract class Lamb_App_Router_Abstract implements Lamb_App_Router_Interface
{	
	/**
	 * @var string the controllor's name
	 */
	protected $_mControllorName = '';
	
	/**
	 * @var string the action's name
	 */
	protected $_mActionName = '';
	
	/**
	 * @var array the request query string except controllor and action
	 */
	protected $_mParams = array();
	
	/**
	 * @var string Used for url() method
	 */
	protected $_mControllorKey = 'c';
	
	/**
	 * @var string Used for url() method
	 */
	protected $_mActionKey = 'a';
	
	/**
	 * Lamb_App_Router_Interface implement
	 */
	 public function getControllor()
	 {
	 	return $this->_mControllorName;
	 }
	 
	/**
	 * Lamb_App_Router_Interface implement
	 */
	 public function getAction()
	 {
	 	return $this->_mActionName;
	 }

	/**
	 * Lamb_App_Router_Interface implement
	 */
	 public function getParams()
	 {
	 	return $this->_mParams;
	 }
	 
	/**
	 * Lamb_App_Router_Interface implement
	 */
	 public function injectRequest(Lamb_App_Request $request=null)
	 {
	 	if (null === $request) {
			$request = Lamb_App::getGlobalApp()->getRequest();
		}
		$request->setUserParams($this->getParams());
	 	return $this;
	 }
	 
	 /**
	  * Set or retrieve the value of controllor key
	  *
	  * @param string $key
	  * @return Lamb_App_Router_Abstract | string
	  */
	 public function setControllorKey($key = null)
	 {
	 	if (null === $key) {
			return $this->_mControllorKey;
		}
		$key = (string)$key;
		$this->_mControllorKey = $key;
		return $this;
	 }
	 
	 /**
	  * Set or retrieve the value of action key
	  *
	  * @param string $key
	  * @return Lamb_App_Router_Abstract | string
	  */
	 public function setActionKey($key = null)	 	 	 	 
	 {
	 	if (null === $key) {
			return $this->_mActionKey;
		}
		$key = (string)$key;
		$this->_mActionKey = $key;
		return $this;
	 }
}