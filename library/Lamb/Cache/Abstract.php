<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Cache
 */
abstract class Lamb_Cache_Abstract implements Lamb_Cache_Interface
{
	protected $_mCacheTime;
	
	protected $_mIdentity;
	
	/**
	 * @param int $cacheTime
	 * @param string | int $identity
	 */
	public function __construct($cacheTime = null, $identity = null)
	{
		$this->setCacheTime($cacheTime)
			 ->setIdentity($identity);
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function setCacheTime($second)
	{
		if (Lamb_Utils::isInt($second)) {
			$this->_mCacheTime = (int)$second;
		}
		return $this;
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function getCacheTime()
	{
		return $this->_mCacheTime;
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */	
	public function setIdentity($identity)
	{
		$this->_mIdentity = $identity;
		return $this;
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */	
	public function	getIdentity()
	{
		return $this->_mIdentity;
	}
	
	/** 
	 * 灏嗗敮涓€镙囱瘑CRC32缂栫爜
	 * 
	 * @param string $str
	 * @return string
	 */
	public static function crc32EncodeIdentity($str)
	{
		return Lamb_Utils::crc32FormatHex($str);
	}
}