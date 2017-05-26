<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Cache
 */
interface Lamb_Cache_Interface
{
	/** 
	 * 璁剧疆缂揿瓨镞堕棿 
	 *
	 * @param int $second
	 * @return Lamb_Cache_Interface
	 */
	public function setCacheTime($second);
	
	/**
	 * @return int
	 */
	public function getCacheTime();
	
	/**
	 * 璁剧疆缂揿瓨镄勬爣璇?
	 * 濡傦细鏂囦欢缂揿瓨镄勬爣璇嗘槸璺缎
	 * 鍐呭瓨缂揿瓨鏄敭链?
	 *
	 * @param string | int $identity
	 * @return Lamb_Cache_Interface
	 */
	public function setIdentity($identity);
	
	/** 
	 * Get the cache's identity
	 *
	 * @return string | int
	 */
	public function getIdentity();
	
	/**
	 * Read data from cache
	 *
	 * @return mixed 濡傛灉涓簄ull鍒欎负缂揿瓨杩樻湭鍒涘缓娲诲凡缁忚绷链?
	 */
	public function read();
	
	/**
	 * Write data to cache
	 *
	 * @return boolean
	 */
	public function write($data);
	
	/**
	 * Flush the cache
	 *
	 * @reutnr boolean is success?
	 */
	public function flush();
	
	/** 
	 * Retrieve the data whether in cached
	 *
	 * @return boolean
	 */
	public function isCached();
}