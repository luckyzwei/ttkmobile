<?php
/**
 * Lamb Framework
 * @package Lamb
 * @author 灏忕緤
 */
class Lamb_Exception extends Exception
{
	/**
	 * @var null|Exception
	 */
	private $_mPrevious = null;
	
	/**
	 * Construct the exception
	 *
	 * @param string $strMsg
	 * @param int $nCode
	 * @param Exception $previous
	 * @return void
	 */
	public function __construct($strMsg = '', $nCode = 0, Exception $previous = null)
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<')) { //if PHP_VERSION < 5.3.0
			parent::__construct($strMsg, (int)$nCode);
		} else {
			parent::__construct($strMsg, (int)$nCode, $previous);
		}
		$this->_mPrevious = $previous;
	}
	
	/**
	 * String of exception
	 * @return string
	 */
	public function __toString()
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			if (null != $this->_mPrevious) {
				return $this->_mPrevious->__toString()
						. "\n\nNext "
						. parent::__toString();
			}
		}
		return parent::__toString();
	}
	
	/**
	 * returns previous exception
	 * @return Exception|null
	 */
	protected function _getPrevious()
	{
		return $this->_mPrevious;
	}
}