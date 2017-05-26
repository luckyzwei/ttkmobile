<?php
interface Ttk_Collect_Interface
{
	const S_OK = 1;
	
	const E_NET_FAIL = -1;
	
	const E_RULE_NOT_MATCH = -2;
	
	/**
	 * @param string $url
	 * @param array $externals
	 * @param int $error
	 * @param array
	 */
	public function collect($url, $type = 1);
} 