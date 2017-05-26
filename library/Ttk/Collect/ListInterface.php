<?php
/**
 * $array = collect($url)
 * $array = array(
 *		'url' => string,
 *		'datetime' => int,
 *		'externls' => mixed 
 *	)
 */
interface Ttk_Collect_ListInterface extends Ttk_Collect_Interface
{
	/**
	 * @param int $page
	 * @return string
	 */
	public function getUrl($page = 1);
}