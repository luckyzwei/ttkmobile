<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_View_Tag
 */
interface Lamb_View_Tag_Interface
{
	/**
	 * @param string $content 镙囩涓庢爣绛剧粨鏉熺涔嬮棿镄勬暟鎹?
	 * @param string $property 镙囩镄勫睘镐?
	 * @return string
	 */
	public function parse($content, $property);
}