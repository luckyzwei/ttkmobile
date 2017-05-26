<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Looper_Count
 */
class Lamb_Looper_Count implements Lamb_Looper_Count_Interface
{
	/**
	 * @var int
	 */
	protected $mCount;
	
	/**
	 * @var mixed
	 */
	protected $mHandlerExternalParams = null;
	
	/**
	 * @var Lamb_Looper_Count_HandlerInterface | PHP callback
	 */
	protected $mHandler = null;
	
	/**
	 * @param int $count
	 * @param Lamb_Looper_Count_HandlerInterface $handler | PHP callback
	 */
	public function __construct($count = null, $handler = null)
	{
		if (null !== $count) {
			$this->setCount($count);
		}
		
		if (null !== $handler) {
			$this->setHandler($handler);
		}
	}
	
	/**
	 * @return int
	 */
	public function getCount()
	{	
		return $this->mCount;
	}
	
	/**
	 * @return Lamb_Looper_Count_HandlerInterface | PHP callback
	 */
	public function getHandler()
	{
		return $this->mHandler;
	}
	
	/**
	 * @param mixed $externalParam
	 * @return Lamb_Looper_Count
	 */
	public function setHandlerExternalParam($externalParam)
	{
		$this->mHandlerExternalParams = $externalParam;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getHandlerExternalParam()
	{
		return $this->mHandlerExternalParams;
	}
	
	/**
	 * Lamb_Looper_Count_Interface implemention
	 */
	public function setCount($count)
	{
		$this->mCount = max((int)$count, 1);
		return $this;
	}
	
	/**
	 * Lamb_Looper_Count_Interface implemention
	 */
	public function setHandler($handler)	
	{
		if ($handler instanceof Lamb_Looper_Count_HandlerInterface || is_callable($handler)) {
			$this->mHandler = $handler;
		}
		return $this;
	}
	
	/**
	 * Lamb_Looper_Interface implemention
	 */
	public function run()
	{
		if ($handler = $this->getHandler()) {		
			for ($i = 1, $j = $this->getCount(); $i <= $j; $i ++) {
				if ($handler instanceof Lamb_Looper_Count_HandlerInterface) {
					if (!$handler->handle($i, $this->getHandlerExternalParam())) {
						return false;
					}
				} else if (!call_user_func($handler, $i, $this->getHandlerExternalParam())){
					return false;
				}
			}
		}
		return true;
	}
}
