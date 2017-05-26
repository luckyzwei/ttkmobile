<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Cache
 */
class Lamb_Cache_File extends Lamb_Cache_Abstract
{	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function read()
	{
		$ret = null;
		$path = $this->getIdentity();
		if ($this->isCached()) {
			$ret = Lamb_IO_File::getContents($path);
		}
		return $ret;
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function write($data)
	{
		if ($this->getCacheTime() > 0) {
			Lamb_IO_File::putContents($this->getIdentity(), $data);
		}
		return true;
	}
	
	/** 
	 * Lamb_Cache_Interface implemention
	 */
	public function flush()
	{
		$ret = false;
		if (Lamb_IO_File::exists($this->getIdentity())) {
			$ret = Lamb_IO_File::delete($this->getIdentity());
		}
		return $ret;
	}
	
	/**
	 * Lamb_Cache_Interface implemention
	 */
	public function isCached()
	{
		$path = $this->getIdentity();
		return Lamb_IO_File::exists($path) && (time() - Lamb_IO_File::getLastModifytime($path) <= $this->getCacheTime());
	}
}