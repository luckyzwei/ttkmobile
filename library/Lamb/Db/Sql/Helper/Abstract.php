<?php
/**
 * Lamb Framework
 * Lamb_Db_Sql_Helper_Abstract鏄疭QL澶勭悊宸ュ叿鎶借薄绫伙紝涓昏鐢ㄤ簬
 * 銮峰彇鍒嗛〉SQL锛岀紪镰丼QL璇彞锛岀敱浜庢疮涓猄QL寮曟搸澶勭悊镄勬柟寮忎笉涓€镙?
 * 锲犳灏嗗叾鎶借薄鍑烘潵锛屽叿浣揿紩鎿庨兘瑕佺户镓挎鎶借薄绫?
 *
 * @author 灏忕緤
 * @package Lamb_Db_Sql_Helper
 */
abstract class Lamb_Db_Sql_Helper_Abstract
{

	/**
	 * 鐢熸垚鍒嗛〉镄凷QL锛屼紶鍏ョ殑SQL璇彞涓岖敤鍖呮嫭鍒嗛〉镄勮鍙?
	 *
	 * @param string $strSql
	 * @param int $nPageSize
	 * @param int $nPage
	 * @param boolean $bIncludeUnion
	 * @return string
	 */
	public function getPageSql($strSql, $nPageSize, $nPage = 1, $bIncludeUnion=false)
	{
		return $this->getLimitSql($strSql, $nPageSize, ($nPage-1) * $nPageSize, $bIncludeUnion);
	}
	
	/**
	 * 銮峰彇SQL璇彞涓殑镓€链夊垪鍚?
	 *
	 * @param string $sql
	 * @return string
	 */
	public function getSqlField($sql)
	{
		$aMatchs	=	array();
		$strFields	=	'';
		if(preg_match('/^select(.+?)from(.+?)/is', $sql, $aMatchs)){
			$strFields	=	$aMatchs[1];
		}
		return $strFields;
	}
	
	/**
	 * 鍒ゆ柇SQL璇彞涓槸鍚﹀惈链塙NION鍏抽敭瀛?
	 * 娉细姝ゆ柟娉曚笉澶彲闱?
	 *
	 * @param string $sql
	 * @return boolean 
	 */
	public function hasUnionKey($sql)
	{
		return strpos(strtolower($sql), ' union ') ? true : false;
	}	
	
	/**
	 * 鐢熸垚銮峰彇鎸囧畾offset浠ュ强锲哄畾闀垮害璁板綍镄凷QL璇彞 
	 *
	 * @param string $sql
	 * @param int $nLimit
	 * @param int $nOffset
	 * @param boolean $bIncludeUnion
	 * @return string
	 */
	abstract public function getLimitSql($sql, $nLimit, $nOffset = 0, $bIncludeUnion=false);
	
	/**
	 * 杞箟SQL璇彞涓殑闱炴硶瀛楃
	 *
	 * @param string $sql
	 * @return string
	 */
	abstract public function escape($sql);
	
	/**
	 * 鍙浆涔夋ā绯婃悳绱㈢殑闱炴硶瀛楃锛屼笉浼氲皟鐢╡scape杞箟
	 *
	 * @param string $sql
	 * @return string
	 */
	abstract public function escapeBlur($sql);
	
	/**
	 * 杞箟妯＄硦鎼灭储镄勯潪娉曞瓧绗︼紝骞惰皟鐢╡scape杞箟
	 *
	 * @param string $sql
	 * @return string
	 */
	abstract public function escapeBlurEncoded($sql);
	
	/**
	 * 鐢熸垚銮峰彇鎸囧畾offset浠ュ强锲哄畾闀垮害璁板綍镄勯澶勭悊SQL璇彞 
	 * 娉细SQL棰勫鐞呜鍙ヤ腑浣跨敤:g_limit锅氢负璁剧疆锲哄畾璁板綍闀垮害鍙傛暟鍚?
	 * :g_offset浣滀负璁剧疆锅忕Щ浣岖疆鍙傛暟鍚?
	 *
	 * @param string $sql
	 * @param boolean $bIncludeUnion
	 */
	abstract public function getPrePareLimitSql($sql, $bIncludeUnion = false);

	/** 
	 * 瀵瑰垪鍚嶈繘琛岃浆涔夛紝阒叉鐗规畩鍏抽敭瀛楀悓鍒楀悕鍚屽悕
	 *
	 * @param string $field
	 * @return string 
	 */	
	abstract public function escapeField($field);
}