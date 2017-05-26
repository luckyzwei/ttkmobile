<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Cache
 */
class Lamb_Cache_Memcached extends Lamb_Cache_Abstract
{
	const T_NORMAL = 1;
	
	const T_PCONNECT = 2;
	
	/**
	 * @var Memcached 
	 */
	protected $_mMemcached = null;
	
	/**
	 * @var boolean
	 */
	protected $_mIsConnected = false;
	
	/**
	 * @param array $connectOptions = array('host'=>, 'port'=>, 'timeout'=>(default:15), 'type'=>[T_NOMAL|T_PCONNECT])
	 * @param int $cacheTime
	 * @param string | int $identity
	 * @throws Lamb_Cache_Exception
	 */
	public function __construct(array $connectOptions = null, $cacheTime = null, $identity = null)
	{
		parent::__construct($cacheTime, $identity);
		
		try {
			$this->_mMemcached = new Memcache();
			if ($connectOptions) {
				$this->connect($connectOptions);
			}
		} catch (Exception $e) {
			throw new Lamb_Cache_Exception($e->getMessage());
		}
	}
	
	/**
	 * @param array $options = array('host'=>, 'port'=>, 'timeout'=>(default:15), 'type'=>[T_NOMAL|T_PCONNECT])
	 * @return Lamb_Cache_Memcached
	 */
	public function connect(array $options)
	{

		$default = array('timeout' => 15, 'type' => self::T_PCONNECT);
		Lamb_Utils::setOptions($default, $options);
		if ($this->isEverythingOk() && isset($default['host']) && isset($default['port'])) {
			$this->_mIsConnected = $default['type'] == self::T_NORMAL ? 
								   $this->_mMemcached->connect($default['host'], $default['port'], $default['timeout'])
								   : $this->_mMemcached->pconnect($default['host'], $default['port'], $default['timeout']);
		}
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function close()
	{
		$this->isEverythingOk();
		$ret = $this->_mMemcached->close();
		if ($ret) {
			$this->_mIsConnected = false;
		}
		return $ret;
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function flush()
	{
		$this->isEverythingOk();
		return $this->_mMemcached->delete($this->getIdentity());
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function isCached()
	{
		$this->isEverythingOk();
		return $this->getCacheTime() > 0 && $this->_mMemcached->get($this->getIdentity()) !== false;
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function read()
	{
		$this->isEverythingOk();
		$ret = $this->_mMemcached->get($this->getIdentity());
		
		if ($ret === false) {
			return null;
		}
		
		if (is_string($ret)) {
			$head = substr($ret, 0, 5);
			
			if ($head == ':$2$:') {
				$ret = json_decode(substr($ret, 5), true);
			} else if ($head == ':$1$:') {
				$ret = substr($ret, 5);
			}
		}
		return $ret;
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function write($data)
	{
		$this->isEverythingOk();
		if (is_string($data)) {
			$data = ':$1$:' . $data;
		} else {
			$data = ':$2$:' . json_encode($data);
		}		
		return $this->_mMemcached->set($this->getIdentity(), $data, 0, $this->getCacheTime());
	}
	
	/**
	 * @return Memecache
	 */
	public function getRawMemcached()
	{
		return $this->_mMemcached;
	}
	
	/**
	 * The wrapper of Memcached::flush() function
	 *
	 * @return boolean
	 */
	public function flushAll()
	{
		$this->isEverythingOk();
		return $this->_mMemcached->flush();
	}
	
	/**
	 * @param boolean $trhows
	 * @return boolean
	 * @throws Lamb_Cache_Excepton
	 */
	protected function isEverythingOk($throws = true)
	{
		$bRet = null !== $this->_mMemcached;
		if (!$bRet && $throws) {
			throw new Lamb_Cache_Exception("Memcached cache is initlize failed, please check out.");
		}
		return $bRet;
	}
}