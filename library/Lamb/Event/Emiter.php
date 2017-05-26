<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Event
 */
class Lamb_Event_Emiter implements Lamb_Event_Emiter_Interface
{
	/**
	 * @var array = array( event => 
	 *		array(
	 *			'listeners' => array($listener, ...),
	 *			'exts' => array(array($params, $isRunonce), ...)
	 *		)
	 *	)
	 */
	protected $mListeners = array();
	
	public function __construct()
	{
	
	}
	
	/**
	 * Lamb_Event_Emiter_Interface implemention
	 */
	public function addEventListener($event, $listener, $params = null)
	{
		$this->initListenerItem($event);
		array_push($this->mListeners[$event]['listeners'], $listener);
		array_push($this->mListeners[$event]['exts'], array($params, false));
		return $this;
	}

	/**
	 * Lamb_Event_Emiter_Interface implemention
	 */	
	public function removeEventListener($event, $listener)
	{
		if (($index = array_search($listener, $this->mListeners[$event]['listeners'])) !== false) {
			unset($this->mListeners[$event]['listeners'][$index]);
			unset($this->mListeners[$event]['exts'][$index]);
		}
		return $this;
	}
	
	/**
	 * @return Lamb_Event_Emiter_Interface
	 */		
	public function removeAllListeners($event = null)
	{
		if (null === $event) {
			$this->mListeners = array();
		} else if (isset($this->mListeners[$event])) {
			$this->mListeners[$event] = array();
		}
		return $this;
	}

	/**
	 * @return Lamb_Event_Emiter_Interface
	 */	
	public function runOnceListener($event, $listener, $params = null)
	{
		$this->initListenerItem($event);
		array_push($this->mListeners[$event]['listeners'], $listener);
		array_push($this->mListeners[$event]['exts'], array($params, true));
		return $this;	
	}

	/**
	 * @return Lamb_Event_Emiter_Interface
	 */		
	public function emit($event, $param = null)
	{
		if (isset($this->mListeners[$event])) {
			$listeners = $this->mListeners[$event];
			foreach ($listeners['listeners'] as $key => $item) {
				$exts = $listeners['exts'][$key];
				if ($exts[1]) {
					unset($this->mListeners[$event]['listeners'][$key], $this->mListeners[$event]['exts'][$key]);
				}
				if (call_user_func($item, $exts[0], $param)) {
					break;
				}
			}
		}
		return $this;
	}
	
	/**
	 * @param int | string
	 * @return Lamb_Event_Emiter
	 */
	protected function initListenerItem($event)
	{
		if (!isset($this->mListeners[$event]) || !is_array($this->mListeners[$event])) {
			$this->mListeners[$event] = array('listeners' => array(), 'exts' => array());
		}
		return $this;	
	}
}