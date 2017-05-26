<?php
/**
 * Lamb Framework
 * @auth lamb
 * @time 2015-09-20 11:57
 * redis缂揿瓨
 */
class Lamb_Cache_Redis extends Lamb_Cache_Abstract
{
	protected $_redis = null;
	
	protected $_is_connected = false;
	
	/**
	 * 鏋勯€犲嚱鏁?
	 *
	 * @param array $conn_opt 杩炴帴阃夐」 = array(
	 *		'host' => 涓绘満澶达紝榛樿涓簂ocalhost,
	 *		'port' => 绔彛鍙凤紝蹇呭～
	 *		timeout => 瓒呮椂镞堕棿锛岄粯璁や负0锛屼笉闄愬埗
	 *		auth => 鐧诲綍瀵嗙爜锛屽鏋滈渶瑕佺殑璇濓紝濉啓璇ュ€?
	 *		is_pconnect => 鏄惁涓烘案涔呴摼鎺ワ紝榛樿涓篓rue
	 * )
	 *
	 *
	 * @param int $cache_time 缂揿瓨镞堕棿
	 * @param int $identity 缂揿瓨鍚?
	 */
	public function __construct($conn_opt = null, $cache_time = null, $identity = null)
	{
		parent::__construct($cache_time, $identity);
		$this->_redis = new Redis;
		
		if ($conn_opt !== null) {
			$this->connect($conn_opt);
		}
	}
	
	/**
	 * 鏋勯€犲嚱鏁?
	 *
	 * @param array $conn_opt 杩炴帴阃夐」 = array(
	 *		'host' => 涓绘満澶达紝榛樿涓簂ocalhost,
	 *		'port' => 绔彛鍙凤紝蹇呭～
	 *		timeout => 瓒呮椂镞堕棿锛岄粯璁や负0锛屼笉闄愬埗
	 *		auth => 鐧诲綍瀵嗙爜锛屽鏋滈渶瑕佺殑璇濓紝濉啓璇ュ€? 
	 *		is_pconnect => 鏄惁涓烘案涔呴摼鎺ワ紝榛樿涓篓rue
	 * )
	 *
	 * @return boolean
	 */
	public function connect($conn_opt)
	{
		$opt = array('timeout' => 15, 'host' => '127.0.0.1', 'port'=> 6379, 'auth' => '', 'is_pconnect' => true);
		Lamb_Utils::setOptions($opt, $conn_opt);
		$funcname = 'pconnect';
		
		if (!$opt['is_pconnect']) {
			$funcname = 'connect';
		}
		
		if ($this->_redis->$funcname($opt['host'], $opt['port'], $opt['timeout'])) {
			if ($opt['auth'] && $this->_redis->auth($opt['auth'])) {
				$this->_is_connected = true;
			} 
			
			if (!$opt['auth']) {
				$this->_is_connected = true;
			}
		}
		
		return $this->_is_connected;
	}
	
	/**
	 * 鏂紑褰揿墠杩炴帴
	 *
	 * @return boolean
	 */
	public function close()
	{
		if ($this->isConnected()) {
			$this->_redis->quit();
			return true;
		}
		
		return false;
	}
	
	/**
	 * 娓呯┖缂揿瓨
	 */
	public function flush()
	{
		if ($this->isConnected()) {
			return $this->_redis->del($this->getIdentity());
		}
		return false;
	}
	
	/**
	 * 娓呯┖镓€链夌紦瀛?
	 */
	public function flushAll()
	{
		if ($this->isConnected()) {
			return $this->_redis->flushAll();
		}
		return false;	
	}
	
	/**
	 * 杩斿洖缂揿瓨鏄惁鍦ㄦ湁鏁堟湡鍐?
	 *
	 * @return boolean
	 */
	public function isCached()
	{
		if ($this->isConnected() && $this->getCacheTime()>0 && $this->_redis->ttl($this->getIdentity()) > 0 && $this->_redis->get($this->getIdentity()) !== false) {
			return true;
		}
		
		return false;
	}
	

	/**
	 * Lamb_Cache_Interface implemention
	 */	
	public function write($data)
	{
		if ($this->isConnected()) {	
			if (is_string($data)) {
				$data = ':$1$:' . $data;
			} else {
				$data = ':$2$:' . json_encode($data);
			}
			
			return $this->_redis->setex($this->getIdentity(), $this->getCacheTime(), $data);
		}
		
		return false;
	}
	
	
	/**
	 * Lamb_Cache_Interface implemention
	 */	
	public function read()
	{
		if ($this->isConnected()) {
			$ret = $this->_redis->get($this->getIdentity());
			
			if ($ret === false) {
				return null;
			}
			$header = substr($ret, 0, 5);
			$ret = substr($ret, 5);
			
			//濡傛灉鏄痡son瀛楃涓?
			if ($header == ':$2$:') {
				$ret = json_decode($ret, true);
			}
			return $ret;
		}
		return null;
	}
	
	/**
	 * 銮峰彇铡熺敓镄剅edis瀵硅薄
	 */
	public function getRawRedis()
	{
		return $this->_redis;
	}
	
	/**
	 * 杩斿洖褰揿墠鏄惁杩炴帴
	 */
	public function isConnected()
	{
		return $this->_is_connected;
	}
}