<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb
 */
class Lamb_Registry extends ArrayObject
{
	/**
	 * @var Lamb_Registry singleton instance
	 */
	private static $sInstance = null;
	
	/**
	 * Get the singleton instance of Lamb_Registry
	 *
	 * @return Lamb_Registry
	 */
	public static function getInstance()
	{
		if (null === self::$sInstance) {
			self::$sInstance = new self();
		}
		return self::$sInstance;
	}
	
	/**
	 * Retset the singleton instance
	 */
	public static function resetInstance()
	{
		self::$sInstance = null;
	}
	
	/**
	 * @param mixed $index
	 * @return mixed
	 * @throws Lamb_Exception
	 */
	public static function get($index)
	{
		$self = self::getInstance();
		if (!$self->offsetExists($index)) {
			throw new Lamb_Exception("No entry is registered for key '$index'");
		}
		return $self->offsetGet($index);
	}
	
	/**
	 * @param mixed $index
	 * @param mixed $val
	 * @return void
	 */
	public static function set($index, $val)
	{
		self::getInstance()->offsetSet($index, $val);
	}
	
	/**
	 * @param mixed $index
	 * @return boolean
	 */
	public static function isRegistred($index)
	{
		return self::getInstance()->offsetExists($index);
	}
	
	/**
	 * Contruct the Lamb_Registry
	 *
	 * @param array $array
	 * @param int $flag
	 */
	public function __construct($array = array(), $flag = parent::ARRAY_AS_PROPS)
	{
		parent::__construct($array, $flag);
	}
	
	/**
	 * Workaround for http://bugs.php.net/bug.php?id=40442 (ZF-960).
	 */
	public function offsetExists($index)
	{
		return array_key_exists($index, $this);
	}
}