<?php
interface Ttk_Model_VideoListenerInterface
{
	const ON_BEFORE_UPDATE = 1;
	
	const ON_AFTER_UPDATE = 2;
	
	const ON_BEFORE_INSERT = 4;
	
	const ON_AFTER_INSERT = 8;
	
	const ON_BEFORE_DELETE = 16;
	
	const ON_AFTER_DELETE = 36;
	
	/** 
	 * @param int $event
	 * @param mixed & $videoInfo
	 * @return boolean
	 */
	public function on($event, &$videoInfo);
}